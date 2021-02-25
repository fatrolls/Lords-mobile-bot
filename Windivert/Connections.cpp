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

DWORD WINAPI InitFilterIncommingConnections_(LPVOID lpParam)
{
	HANDLE handle;
	INT16 priority = 0;
	unsigned char packet[MAXBUF];
	UINT packet_len;
	WINDIVERT_ADDRESS addr;
	PWINDIVERT_IPHDR ip_header;
	PWINDIVERT_TCPHDR tcp_header;
	const char *err_str;
	char Filter[255] = "inbound and !loopback and ip and tcp and tcp.PayloadLength > 0";
	ThreadsRunning++;
	// Divert traffic matching the filter:
	handle = WinDivertOpen(Filter, WINDIVERT_LAYER_NETWORK, priority, 0);
	if (handle == INVALID_HANDLE_VALUE)
	{
		if (GetLastError() == ERROR_INVALID_PARAMETER && !WinDivertHelperCheckFilter(Filter, WINDIVERT_LAYER_NETWORK, &err_str, NULL))
		{
			fprintf(stderr, "error: invalid filter \"%s\"\n", err_str);
			exit(EXIT_FAILURE);
		}
		fprintf(stderr, "error: failed to open the WinDivert device (%d)\n", GetLastError());
		exit(EXIT_FAILURE);
	}

	// Main loop:
	while (KeepThreadsAlive)
	{
		// Read a matching packet.
		if (!WinDivertRecv(handle, packet, sizeof(packet), &addr, &packet_len))
		{
			fprintf(stderr, "warning: failed to read packet (%d)\n", GetLastError());
			continue;
		}

		//check what we received
		PWINDIVERT_IPV6HDR ipv6_header;
		PWINDIVERT_ICMPHDR icmp_header;
		PWINDIVERT_ICMPV6HDR icmpv6_header;
		PWINDIVERT_UDPHDR udp_header;
		void *payload;
		unsigned int payload_len;
		WinDivertHelperParsePacket(packet, packet_len, &ip_header, &ipv6_header, &icmp_header, &icmpv6_header, &tcp_header, &udp_header, &payload, &payload_len);
		if (ip_header != NULL && tcp_header != NULL)
		{
			//filter out IP that we do not wish to see
			if (addr.Direction == WINDIVERT_DIRECTION_INBOUND && CheckServerIpMatch(ip_header->SrcAddr) == 1)
			{
				//in case we wish to see the content on console or maybe file
				ShowPacketInfo(addr, packet, packet_len);

				OnServerToClientPacket((unsigned char *)payload, payload_len);
//if(payload_len==62)
//				PrintDataHexFormat((unsigned char *)payload, payload_len, 0, payload_len);
			}
		}
		//send the packet
		if (!WinDivertSend(handle, packet, packet_len, &addr, 0))
		{
			fprintf(stderr, "warning: failed to put packet back to the send stream (%d)\n", GetLastError());
			continue;
		}
		//only if we filter by IP also
//		else
//			printf("Reinserted packet : %d\n", packet_len);
	}

	//cleanup
	WinDivertClose(handle);
	handle = NULL;
	ThreadsRunning--;
	return 0;
}

DWORD WINAPI InitFilterOutgoingConnections_(LPVOID lpParam)
{
	HANDLE handle;
	INT16 priority = 0;
	unsigned char packet[MAXBUF];
	UINT packet_len;
	WINDIVERT_ADDRESS addr;
	PWINDIVERT_IPHDR ip_header;
	PWINDIVERT_TCPHDR tcp_header;
	const char *err_str;
	char Filter[255] = "outbound and !loopback and ip and tcp and tcp.PayloadLength > 0";
	ThreadsRunning++;
	// Divert traffic matching the filter:
	handle = WinDivertOpen(Filter, WINDIVERT_LAYER_NETWORK, priority, 0);
	if (handle == INVALID_HANDLE_VALUE)
	{
		if (GetLastError() == ERROR_INVALID_PARAMETER && !WinDivertHelperCheckFilter(Filter, WINDIVERT_LAYER_NETWORK, &err_str, NULL))
		{
			fprintf(stderr, "error: invalid filter \"%s\"\n", err_str);
			exit(EXIT_FAILURE);
		}
		fprintf(stderr, "error: failed to open the WinDivert device (%d)\n", GetLastError());
		exit(EXIT_FAILURE);
	}

	// Main loop:
	while (KeepThreadsAlive)
	{
		// Read a matching packet.
		if (!WinDivertRecv(handle, packet, sizeof(packet), &addr, &packet_len))
		{
			fprintf(stderr, "warning: failed to read packet (%d)\n", GetLastError());
			continue;
		}

		//check what we received
		PWINDIVERT_IPV6HDR ipv6_header;
		PWINDIVERT_ICMPHDR icmp_header;
		PWINDIVERT_ICMPV6HDR icmpv6_header;
		PWINDIVERT_UDPHDR udp_header;
		void *payload;
		unsigned int payload_len;
		WinDivertHelperParsePacket(packet, packet_len, &ip_header, &ipv6_header, &icmp_header, &icmpv6_header, &tcp_header, &udp_header, &payload, &payload_len);
		if (ip_header != NULL && tcp_header != NULL)
		{
			//filter out IP that we do not wish to see
			if (addr.Direction == WINDIVERT_DIRECTION_OUTBOUND && CheckServerIpMatch(ip_header->DstAddr) == 1)
			{
				//in case we wish to see the content on console or maybe file
				ShowPacketInfo(addr, packet, packet_len);
				unsigned int PayloadContentChanged = OnClientToServerPacket((unsigned char *)payload, payload_len);
//				if (PayloadContentChanged == 1)
					WinDivertHelperCalcChecksums(packet, packet_len, &addr, 0);
				if (payload_len == 51)
					PrintDataHexFormat((unsigned char *)payload, payload_len, 0, payload_len);
			}
		}
		//send the packet
		if (!WinDivertSend(handle, packet, packet_len, &addr, 0))
		{
			fprintf(stderr, "warning: failed to put packet back to the send stream (%d)\n", GetLastError());
			continue;
		}
		//only if we filter by IP also
//		else
//			printf("Reinserted packet : %d\n", packet_len);
	}

	//cleanup
	WinDivertClose(handle);
	handle = NULL;
	ThreadsRunning--;
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
	HANDLE	FilterIncommingConnectionsThreadHandle = 0;
	HANDLE	FilterOutgoingConnectionsThreadHandle = 0;
	DWORD   ThreadId;
	FilterIncommingConnectionsThreadHandle = CreateThread(NULL, 0, InitFilterIncommingConnections_,	NULL, 0, &ThreadId);		
	FilterOutgoingConnectionsThreadHandle = CreateThread(NULL, 0, InitFilterOutgoingConnections_, NULL, 0, &ThreadId);
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