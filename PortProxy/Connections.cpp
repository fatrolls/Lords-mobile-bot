#include <winsock2.h>
#include <windows.h>
#include <stdio.h>
#include <stdlib.h>
#include <string.h>

#include "windivert.h"
#include "PacketContentGenerator.h"
#include "StreamInfo.h"
#include "ParseClientToServer.h"
#include "ParseServerToClient.h"
#include "Tools.h"
#include "PacketAssembler.h"

//https://reqrypt.org/windivert-doc-1.4.html
//https://reqrypt.org/windivert-doc-1.4.html#filter_language
//https://github.com/basil00/Divert/blob/v1.1.8/examples/netfilter/netfilter.c

#define MAXBUF  0xFFFF

unsigned int ServerIP[4] = { 192, 243, 0, 0 };
unsigned int ServerIPMask[4] = { 255, 255, 0, 0 };

int KeepThreadsAlive = 1;
int ThreadsRunning = 0;

int CheckServerIpMatch(unsigned int ip1)
{
	unsigned char *ip = (unsigned char *)&ip1;
	for (int i = 0; i < 4; i++)
		if ((ip[i] & ServerIPMask[i]) != (ServerIP[i] & ServerIPMask[i]))
			return 0;
	return 1;
}

#define MAXBUF          0xFFFF
#define PROXY_PORT      34010
#define ALT_PORT        43010
#define MAX_LINE        65

/*
 * Proxy server configuration.
 */
typedef struct
{
	UINT16 proxy_port;
	UINT16 alt_port;
} PROXY_CONFIG, *PPROXY_CONFIG;

typedef struct
{
	SOCKET s;
	UINT16 alt_port;
	struct in_addr dest;
} PROXY_CONNECTION_CONFIG, *PPROXY_CONNECTION_CONFIG;

typedef struct
{
	BOOL inbound;
	SOCKET s;
	SOCKET t;
	sockaddr_in addr;
} PROXY_TRANSFER_CONFIG, *PPROXY_TRANSFER_CONFIG;

/*
 * Lock to sync output.
 */
static HANDLE lock;

/*
 * Prototypes.
 */
static DWORD proxy(LPVOID arg);
static DWORD proxy_connection_handler(LPVOID arg);
static DWORD proxy_transfer_handler(LPVOID arg);

/*
 * Error handling.
 */
static void message(const char *msg, ...)
{
	va_list args;
	va_start(args, msg);
	WaitForSingleObject(lock, INFINITE);
	vfprintf(stderr, msg, args);
	putc('\n', stderr);
	ReleaseMutex(lock);
	va_end(args);
}
#define error(msg, ...)                         \
    do {                                        \
        message("error: " msg, ## __VA_ARGS__); \
        exit(EXIT_FAILURE);                     \
    } while (FALSE)
#define warning(msg, ...)                       \
    message("warning: " msg, ## __VA_ARGS__)

/*
 * Cleanup completed I/O requests.
 */
static void cleanup(HANDLE ioport, OVERLAPPED *ignore)
{
	OVERLAPPED *overlapped;
	DWORD iolen;
	ULONG_PTR iokey = 0;

	while (GetQueuedCompletionStatus(ioport, &iolen, &iokey, &overlapped, 0))
		if (overlapped != ignore)
			free(overlapped);
}

DWORD WINAPI RedirectTrafficThread(LPVOID lpParam)
{
	HANDLE handle, thread;
	UINT16 port, proxy_port, alt_port;
	int r;
	char filter[256];
	INT16 priority = 123;       // Arbitrary.
	PPROXY_CONFIG config;
	unsigned char packet[MAXBUF];
	UINT packet_len;
	WINDIVERT_ADDRESS addr;
	PWINDIVERT_IPHDR ip_header;
	PWINDIVERT_TCPHDR tcp_header;
	OVERLAPPED *poverlapped;
	OVERLAPPED overlapped;
	HANDLE ioport, event;
	DWORD len;

	// Init.
	port = 5991;
	proxy_port = (port == PROXY_PORT ? PROXY_PORT + 1 : PROXY_PORT);
	alt_port = (port == ALT_PORT ? ALT_PORT + 1 : ALT_PORT);
	lock = CreateMutex(NULL, FALSE, NULL);
	if (lock == NULL)
	{
		fprintf(stderr, "error: failed to create mutex (%d)\n",	GetLastError());
		exit(EXIT_FAILURE);
	}
	ioport = CreateIoCompletionPort(INVALID_HANDLE_VALUE, NULL, 0, 0);
	if (ioport == NULL)
		error("failed to create I/O completion port (%d)", GetLastError());
	event = CreateEvent(NULL, FALSE, FALSE, NULL);
	if (event == NULL)
		error("failed to create event (%d)", GetLastError());

	// Divert all traffic to/from `port', `proxy_port' and `alt_port'.
	r = snprintf(filter, sizeof(filter),
		"tcp and "
		"(tcp.DstPort == %d or tcp.DstPort == %d or tcp.DstPort == %d or "
		"tcp.SrcPort == %d or tcp.SrcPort == %d or tcp.SrcPort == %d)",
		port, proxy_port, alt_port, port, proxy_port, alt_port);
	if (r < 0 || r >= sizeof(filter))
		error("failed to create filter string");
	handle = WinDivertOpen(filter, WINDIVERT_LAYER_NETWORK, priority, 0);
	if (handle == INVALID_HANDLE_VALUE)
		error("failed to open the WinDivert device (%d)", GetLastError());
	if (CreateIoCompletionPort(handle, ioport, 0, 0) == NULL)
		error("failed to associate I/O completion port (%d)", GetLastError());

	// Spawn proxy thread,
	config = (PPROXY_CONFIG)malloc(sizeof(PROXY_CONFIG));
	if (config == NULL)
		error("failed to allocate memory");
	config->proxy_port = proxy_port;
	config->alt_port = alt_port;
	thread = CreateThread(NULL, 1, (LPTHREAD_START_ROUTINE)proxy,(LPVOID)config, 0, NULL);
	if (thread == NULL)
		error("failed to create thread (%d)", GetLastError());
	CloseHandle(thread);

	// Main loop:
	while (TRUE)
	{
		memset(&overlapped, 0, sizeof(overlapped));
		ResetEvent(event);
		overlapped.hEvent = event;
		if (!WinDivertRecvEx(handle, packet, sizeof(packet), 0, &addr, &packet_len, &overlapped))
		{
			if (GetLastError() != ERROR_IO_PENDING)
			{
			read_failed:
				warning("failed to read packet (%d)", GetLastError());
				continue;
			}

			// Timeout = 1s
			while (WaitForSingleObject(event, 1000) == WAIT_TIMEOUT)
				cleanup(ioport, &overlapped);
			if (!GetOverlappedResult(handle, &overlapped, &len, FALSE))
				goto read_failed;
			packet_len = len;
		}
		cleanup(ioport, &overlapped);

		if (!WinDivertHelperParsePacket(packet, packet_len, &ip_header, NULL,NULL, NULL, &tcp_header, NULL, NULL, NULL))
		{
			warning("failed to parse packet (%d)", GetLastError());
			continue;
		}

		//only redirect connection that will lead to the game server
//		if (CheckServerIpMatch(ip_header->SrcAddr) == 1 || CheckServerIpMatch(ip_header->DstAddr) == 1)
		{
			switch (addr.Direction)
			{
			case WINDIVERT_DIRECTION_OUTBOUND:
				if (tcp_header->DstPort == htons(port))
				{
					// Reflect: PORT ---> PROXY
					UINT32 dst_addr = ip_header->DstAddr;
					tcp_header->DstPort = htons(proxy_port);
					ip_header->DstAddr = ip_header->SrcAddr;
					ip_header->SrcAddr = dst_addr;
					addr.Direction = WINDIVERT_DIRECTION_INBOUND;
				}
				else if (tcp_header->SrcPort == htons(proxy_port))
				{
					// Reflect: PROXY ---> PORT
					UINT32 dst_addr = ip_header->DstAddr;
					tcp_header->SrcPort = htons(port);
					ip_header->DstAddr = ip_header->SrcAddr;
					ip_header->SrcAddr = dst_addr;
					addr.Direction = WINDIVERT_DIRECTION_INBOUND;
				}
				else if (tcp_header->DstPort == htons(alt_port))
				{
					// Redirect: ALT ---> PORT
					tcp_header->DstPort = htons(port);
				}
				break;

			case WINDIVERT_DIRECTION_INBOUND:
				if (tcp_header->SrcPort == htons(port))
				{
					// Redirect: PORT ---> ALT
					tcp_header->SrcPort = htons(alt_port);
				}
				break;
			}
		}

		WinDivertHelperCalcChecksums(packet, packet_len, &addr, 0);
		poverlapped = (OVERLAPPED *)malloc(sizeof(OVERLAPPED));
		if (poverlapped == NULL)
		{
			error("failed to allocate memory");
		}
		memset(poverlapped, 0, sizeof(OVERLAPPED));
		if (WinDivertSendEx(handle, packet, packet_len, 0, &addr, NULL,	poverlapped))
		{
			continue;
		}
		if (GetLastError() != ERROR_IO_PENDING)
		{
			warning("failed to send packet (%d)", GetLastError());
			continue;
		}
	}

	return 0;
}

/*
 * Proxy server thread.
 */
static DWORD proxy(LPVOID arg)
{
	PPROXY_CONFIG config = (PPROXY_CONFIG)arg;
	UINT16 proxy_port = config->proxy_port;
	UINT16 alt_port = config->alt_port;
	int on = 1;
	WSADATA wsa_data;
	WORD wsa_version = MAKEWORD(2, 2);
	struct sockaddr_in addr;
	SOCKET s;
	HANDLE thread;

	free(config);

	if (WSAStartup(wsa_version, &wsa_data) != 0)
		error("failed to start WSA (%d)", GetLastError());

	s = socket(AF_INET, SOCK_STREAM, 0);
	if (s == INVALID_SOCKET)
		error("failed to create socket (%d)", WSAGetLastError());

	if (setsockopt(s, SOL_SOCKET, SO_REUSEADDR, (const char*)&on, sizeof(int)) == SOCKET_ERROR)
		error("failed to re-use address (%d)", GetLastError());

	memset(&addr, 0, sizeof(addr));
	addr.sin_family = AF_INET;
	addr.sin_port = htons(proxy_port);
	if (bind(s, (SOCKADDR *)&addr, sizeof(addr)) == SOCKET_ERROR)
		error("failed to bind socket (%d)", WSAGetLastError());

	if (listen(s, 16) == SOCKET_ERROR)
		error("failed to listen socket (%d)", WSAGetLastError());

	while (TRUE)
	{
		// Wait for a new connection.
		PPROXY_CONNECTION_CONFIG config;
		int size = sizeof(addr);
		SOCKET ClientToProxyConnection = accept(s, (SOCKADDR *)&addr, &size);
		if (ClientToProxyConnection == INVALID_SOCKET)
		{
			warning("failed to accept socket (%d)", WSAGetLastError());
			continue;
		}
		printf("Got a new socket connection to IP %d.%d.%d.%d\n", addr.sin_addr.S_un.S_un_b.s_b1, addr.sin_addr.S_un.S_un_b.s_b2, addr.sin_addr.S_un.S_un_b.s_b3, addr.sin_addr.S_un.S_un_b.s_b4);
		// Spawn proxy connection handler thread.
		config = (PPROXY_CONNECTION_CONFIG)	malloc(sizeof(PROXY_CONNECTION_CONFIG));
		if (config == NULL)
			error("failed to allocate memory");
		config->s = ClientToProxyConnection;
		config->alt_port = alt_port;
		config->dest = addr.sin_addr;
		thread = CreateThread(NULL, 1, (LPTHREAD_START_ROUTINE)proxy_connection_handler, (LPVOID)config, 0, NULL);
		if (thread == NULL)
		{
			warning("failed to create thread (%d)", GetLastError());
			closesocket(ClientToProxyConnection);
			free(config);
			continue;
		}
		CloseHandle(thread);
	}
}

/*
 * Proxy connection handler thread.
 */
static DWORD proxy_connection_handler(LPVOID arg)
{
	PPROXY_TRANSFER_CONFIG config1, config2;
	HANDLE thread;
	PPROXY_CONNECTION_CONFIG config = (PPROXY_CONNECTION_CONFIG)arg;
	SOCKET ClientToProxyConnection = config->s, ProxyToServerConnection;
	UINT16 alt_port = config->alt_port;
	struct in_addr dest = config->dest;
	struct sockaddr_in addr;

	free(config);

	ProxyToServerConnection = socket(AF_INET, SOCK_STREAM, 0);
	if (ProxyToServerConnection == INVALID_SOCKET)
	{
		warning("failed to create socket (%d)", WSAGetLastError());
		closesocket(ClientToProxyConnection);
		return 0;
	}

	memset(&addr, 0, sizeof(addr));
	addr.sin_family = AF_INET;
	addr.sin_port = htons(alt_port);
	addr.sin_addr = dest;
	if (connect(ProxyToServerConnection, (SOCKADDR *)&addr, sizeof(addr)) == SOCKET_ERROR)
	{
		warning("failed to connect socket (%d)", WSAGetLastError());
		closesocket(ClientToProxyConnection);
		closesocket(ProxyToServerConnection);
		return 0;
	}

	config1 = (PPROXY_TRANSFER_CONFIG)malloc(sizeof(PROXY_TRANSFER_CONFIG));
	config2 = (PPROXY_TRANSFER_CONFIG)malloc(sizeof(PROXY_TRANSFER_CONFIG));
	if (config1 == NULL || config2 == NULL)
		error("failed to allocate memory");
	config1->inbound = FALSE;
	config1->addr.sin_addr = dest;
	config2->inbound = TRUE;
	config1->addr.sin_addr = dest;
	config2->t = config1->s = ClientToProxyConnection;
	config2->s = config1->t = ProxyToServerConnection;
	thread = CreateThread(NULL, 1, (LPTHREAD_START_ROUTINE)proxy_transfer_handler, (LPVOID)config1, 0, NULL);
	if (thread == NULL)
	{
		warning("failed to create thread (%d)", GetLastError());
		closesocket(ClientToProxyConnection);
		closesocket(ProxyToServerConnection);
		free(config1);
		free(config2);
		return 0;
	}
	proxy_transfer_handler((LPVOID)config2);
	WaitForSingleObject(thread, INFINITE);
	CloseHandle(thread);
	closesocket(ClientToProxyConnection);
	closesocket(ProxyToServerConnection);
	return 0;
}

int SendBufferOverNetwork(SOCKET DestinationSocket, char *buf, int len)
{
	for (int i = 0; i < len; )
	{
		int len2 = send(DestinationSocket, buf + i, len - i, 0);
		if (len2 == SOCKET_ERROR)
			return 1;
		i += len2;
	}
	return 0;
}
/*
 * Handle the transfer of data from one socket to another.
 */
static DWORD proxy_transfer_handler(LPVOID arg)
{
	PPROXY_TRANSFER_CONFIG config = (PPROXY_TRANSFER_CONFIG)arg;
	BOOL inbound = config->inbound;
	SOCKET SourceSocket = config->s, DestinationSocket = config->t;
	char buf[65535];
	free(config);
	PacketAssembler FragmentedPacketStore;

	while (TRUE)
	{
		// Read data from s.
		int len = recv(SourceSocket, buf, sizeof(buf), 0);
		if (len == SOCKET_ERROR)
		{
			warning("failed to recv from socket (%d)", WSAGetLastError());
			shutdown(SourceSocket, SD_BOTH);
			shutdown(DestinationSocket, SD_BOTH);
			return 0;
		}
		if (len == 0)
		{
			shutdown(SourceSocket, SD_RECEIVE);
			shutdown(DestinationSocket, SD_SEND);
			return 0;
		}

		//assemble partial network packets into full game packets
		if (FragmentedPacketStore.AddBuffer(buf, len) == 0)
		{
			char *tbuf = FragmentedPacketStore.FetchPacket(len);
			while (tbuf != NULL)
			{
				int PacketHandledStatus = PPHT_DID_NOT_TOUCH_IT;
				if (inbound == FALSE)
					PacketHandledStatus = OnClientToServerPacket((unsigned char*)tbuf, len);
				else
					PacketHandledStatus = OnServerToClientPacket((unsigned char*)tbuf, len);
#if 0
if (inbound == FALSE)
{
	unsigned char *packet = (unsigned char *)tbuf;
	if (len == 11 && packet[0] == 11 && packet[1] == 0 && packet[2] == 0x9A && packet[3] == 0x08)
	{
		//try to duplicate this packet
		CastleClickSerializer = packet[4];
		//construct a new packet
		unsigned char *Pkt = (unsigned char*)malloc(11);
		memset(Pkt, 0, 11);
		*(unsigned short*)&Pkt[0] = 11;
		//0b 00 9a 08 c8 00 00 00 ff 03 ff
		Pkt[2] = 0x9a;
		Pkt[3] = 0x08;
		Pkt[4] = (CastleClickSerializer);
		Pkt[5] = 0;
		Pkt[6] = 0;
		int x, y;
		GeteneratePosToScan(x, y);
		unsigned int GUID = GenerateIngameGUID(x, y);
		*(unsigned int*)&Pkt[7] = GUID;
		SendBufferOverNetwork(DestinationSocket, (char*)Pkt, 11);
		Sleep(50);
		memset(Pkt, 0, 11);
		*(unsigned short*)&Pkt[0] = 11;
		//0b 00 9a 08 c8 00 00 00 ff 03 ff
		Pkt[2] = 0x9a;
		Pkt[3] = 0x08;
		CastleClickSerializer++;
		Pkt[4] = CastleClickSerializer;
		Pkt[5] = 0;
		Pkt[6] = 0;
		GeteneratePosToScan(x, y);
		GUID = GenerateIngameGUID(x, y);
		*(unsigned int*)&Pkt[7] = GUID;
		SendBufferOverNetwork(DestinationSocket, (char*)Pkt, 11);/**/
		//merge the 2 packets
/*		{
			unsigned char *Pkt2 = (unsigned char*)malloc(11 * 2);
			memcpy(&Pkt2[0], packet, 11);
			memcpy(&Pkt2[11], Pkt, 11);
			tbuf = (char*)Pkt2;
			len = 22;
		}*/
		tbuf = (char*)Pkt;
		len = 0;
	}
}
#endif
				//if we should still send it to the socket ...
				if (len != 0 && PacketHandledStatus != PPHT_SHOULD_DROP && SendBufferOverNetwork(DestinationSocket, tbuf, len) != 0)
				{
					warning("failed to send to socket (%d)", WSAGetLastError());
					shutdown(SourceSocket, SD_BOTH);
					shutdown(DestinationSocket, SD_BOTH);
					return 0;
				}
				free(tbuf);
				tbuf = FragmentedPacketStore.FetchPacket(len);
			}
		}/**/

		// Dump stream information to the screen.
		/*{
			HANDLE console;
			console = GetStdHandle(STD_OUTPUT_HANDLE);
			WaitForSingleObject(lock, INFINITE);
			printf("[%.4d] ", len);
			SetConsoleTextAttribute(console, (inbound ? FOREGROUND_RED : FOREGROUND_GREEN));
			for (i = 0; i < len && i < MAX_LINE; i++)
				putchar((isprint(buf[i]) ? buf[i] : '.'));
			SetConsoleTextAttribute(console, FOREGROUND_RED | FOREGROUND_GREEN | FOREGROUND_BLUE);
			printf("%s\n", (len > MAX_LINE ? "..." : ""));
			ReleaseMutex(lock);
		}/**/

		// Send data to DestinationSocket.
		if(len != 0 && SendBufferOverNetwork(DestinationSocket,buf,len) != 0)
		{
			warning("failed to send to socket (%d)", WSAGetLastError());
			shutdown(SourceSocket, SD_BOTH);
			shutdown(DestinationSocket, SD_BOTH);
			return 0;
		}

		//do we have packets to be injected into the communication stream ?
		if (inbound == FALSE)
		{
			int tlen;
			char *tbuf = InjectQueue.FetchPacket(tlen);
			while (tbuf != NULL)
			{
				Sleep(50);
				if (tlen != 0 && SendBufferOverNetwork(DestinationSocket, tbuf, tlen) != 0)
				{
					warning("failed to send to socket (%d)", WSAGetLastError());
					shutdown(SourceSocket, SD_BOTH);
					shutdown(DestinationSocket, SD_BOTH);
					return 0;
				}
				free(tbuf);
				printf("Injected packet to server : %d\n", tlen);
				tbuf = InjectQueue.FetchPacket(tlen);
			}
		}/**/
	}

	return 0;
}

void InitConnections()
{
	KeepThreadsAlive = 1;
	ThreadsRunning = 0;
	//servers are clustered in this IP group. Not sure if other groups exists, so far mask was not needed
	FILE *f;
	errno_t opener = fopen_s(&f, "ServerIP.txt", "rt");
	if (f)
	{
		fscanf_s(f, "%d.%d.%d.%d", &ServerIP[0], &ServerIP[1], &ServerIP[2], &ServerIP[3]);
		fclose(f);
	}
	//create 2 threads. One to monitor incomming, other to monitor outgoing connections
	HANDLE	RedirectTrafficThreadHandle = 0;
	DWORD   ThreadId;
	RedirectTrafficThreadHandle = CreateThread(NULL, 0, RedirectTrafficThread,	NULL, 0, &ThreadId);		
}

void ShutDownConnections()
{
	KeepThreadsAlive = 0;
	int AntiDeadlock = 3;
	while (ThreadsRunning > 0 && AntiDeadlock > 0)
	{
		Sleep(1000);
		AntiDeadlock--;
	}
}