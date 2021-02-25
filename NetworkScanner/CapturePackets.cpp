#include "CapturePackets.h"
#include <pcap.h>
#include "ParsePackets.h"
#include "LordsMobileControl.h"

FILE *FCONTENT = NULL;
void DumpContent(unsigned char *data, unsigned int size)
{
	if (FCONTENT == NULL)
		errno_t er = fopen_s(&FCONTENT, "p_good", "wb");
	// might need to reassamble segmented packets later. TCP is a bytestream. The beggining of the packet should be a number indicating how much we need to read until the next packet
	if (FCONTENT)
	{
		fwrite(data, 1, size, FCONTENT);
		fflush(FCONTENT);
	}
}

unsigned char *TempPacketStore = NULL;
unsigned int WriteIndex = 0;
unsigned int ReadIndex = 0;
unsigned int ThrowAwayPacketsUntilSmallPackets = 1;
#define MAX_PACKET_SIZE					(10 * 1024 * 1024)
#define WAITING_FOR_X_BYTES				(*(unsigned short*)&TempPacketStore[ReadIndex])
#define MAX_PACKET_SIZE_SERVER_SENDS	15000
int ThrowAwayCount = 0;
void QueuePacketForMore(unsigned char *data, unsigned int size)
{
	//our temp store
	if (TempPacketStore == NULL)
		TempPacketStore = (unsigned char*)malloc(MAX_PACKET_SIZE); //10 MB should suffice i hope. Best i seen was about 10k

	//internal buffer is in a fucked up state !
	if (ThrowAwayPacketsUntilSmallPackets > 0)
	{
		printf("%d)Throwing away packet with size %d\n", ThrowAwayCount++, size);
		return;
	}

	//seems like we can panic. At this point we should try to resync to the next packet start. But how to do that ?
	if (MAX_PACKET_SIZE-WriteIndex <= size
		|| WriteIndex > MAX_PACKET_SIZE_SERVER_SENDS
		|| size > MAX_PACKET_SIZE_SERVER_SENDS
		)
	{
		printf("!!!ERROR:Packet did not fit into our buffer. Write index %d, Size %d, have %d\n", WriteIndex, size, MAX_PACKET_SIZE - WriteIndex);
		WriteIndex = 0;
		ReadIndex = 0;
		ThrowAwayPacketsUntilSmallPackets = 1;
		ThrowAwayCount = 0;
		return;
	}

	//add to our queue buffer. If we have enough data, process those
	memcpy(&TempPacketStore[WriteIndex], data, size);
	WriteIndex += size;

	//can we pop packets ?
	while (WriteIndex - ReadIndex >= WAITING_FOR_X_BYTES && WAITING_FOR_X_BYTES>0 && WriteIndex - ReadIndex != 0)
	{
//		ProcessPacket1(&TempPacketStore[ReadIndex + 2], WAITING_FOR_X_BYTES);
		QueuePacketToProcess(&TempPacketStore[ReadIndex + 2], WAITING_FOR_X_BYTES);
		ReadIndex += WAITING_FOR_X_BYTES;
	}

	//did we pop all packets ?
	if (ReadIndex >= WriteIndex)
	{
		ReadIndex = 0;
		WriteIndex = 0;
	}
}

void Wait1FullPacketThenParse(unsigned char *data, unsigned int size)
{
	if (size == 0)
		return;
	//theoretical size of a full packet. This is GAME specific !
	if (WriteIndex == 0)
	{
		unsigned short FullPacketSize = *(unsigned short*)data;
		if (size == FullPacketSize)
		{
			//ProcessPacket1(data, size);
			QueuePacketToProcess(&data[2], size - 2);
			//this could be a full packet. Consider ourself syncronized
			if (ThrowAwayPacketsUntilSmallPackets > 0)
				ThrowAwayPacketsUntilSmallPackets--;
			return;
		}
		//more than 1 server packet inside a single network packet
		if ( size>FullPacketSize && ThrowAwayPacketsUntilSmallPackets == 0 )
		{
			//ProcessPacket1(&data[2], FullPacketSize - 2);
			QueuePacketToProcess(&data[2], FullPacketSize - 2);
			QueuePacketForMore(&data[FullPacketSize], size - FullPacketSize); // should never happen
			return;
		}
	}
	//if we got here than this is a fragmented packet with first fragment
	QueuePacketForMore(data, size);
}

#define PROTOCOL_TCPIP	6

unsigned int ServerIP[4] = { 192, 243, 0, 0 };
unsigned int ClientBytesSent = 0;
unsigned int ServerBytesSent = 0;

// Callback function invoked by libpcap for every incoming packet 
void packet_handler(u_char *param, const struct pcap_pkthdr *header, const u_char *pkt_data)
{
	ip_hdr *ih;
	u_int ip_len;

	//Unused variable
	(VOID)(param);

	// retireve the position of the ip header
	ih = (ip_hdr *)(pkt_data + 14); //length of ethernet header

	// retireve the position of the udp header 
	ip_len = ih->ip_header_len * 4;

	//capturing all TCP packets from IP
	if (ih->ip_protocol == PROTOCOL_TCPIP)
	{
		// client to server
		// len 11, click on player owned tile : 0000   0b 00 9a 08 75 00 00 00 ff 03 ed
		if ( (ServerIP[0] == 0 || ((unsigned char*)&ih->ip_destaddr)[0] == ServerIP[0])
			&& (ServerIP[1] == 0 || ((unsigned char*)&ih->ip_destaddr)[1] == ServerIP[1])
			&& (ServerIP[2] == 0 || ((unsigned char*)&ih->ip_destaddr)[2] == ServerIP[2])
			&& (ServerIP[3] == 0 || ((unsigned char*)&ih->ip_destaddr)[3] == ServerIP[3]))
		{
			tcp_header *tcph = (tcp_header *)((u_char*)pkt_data + 14 + ip_len);
			int tcp_len = tcph->data_offset * 4;
			unsigned char *DataStart = (u_char*)pkt_data + 14 + ip_len + tcp_len;
			int TotalHeaderSize = (unsigned int)(DataStart - pkt_data);
			int BytesToDump = header->len - TotalHeaderSize;
            ClientBytesSent = htonl(tcph->sequence) + BytesToDump;
            if (BytesToDump > 0)
			{
				OnLordsClientPacketReceived(pkt_data, header->len, DataStart, BytesToDump);
//#define _DUMPPACKET_TO_FILE
#ifdef _DUMPPACKET_TO_FILE
                static FILE *FCONTENT = NULL;
                if (FCONTENT == NULL)
                    errno_t er = fopen_s(&FCONTENT, "client_to_server", "ab");
                // might need to reassamble segmented packets later. TCP is a bytestream. The beggining of the packet should be a number indicating how much we need to read until the next packet
                if (FCONTENT)
                {
                    //                fwrite(&BytesToDump, 1, 4, FCONTENT); //already present in the packet as the first 2 bytes
                    fwrite(DataStart, 1, BytesToDump, FCONTENT);
                    fflush(FCONTENT);
                }
#endif
			}

		}
		// server to client
		if ( (ServerIP[0] == 0 || ((unsigned char*)&ih->ip_srcaddr)[0] == ServerIP[0])
			&& (ServerIP[1] == 0 || ((unsigned char*)&ih->ip_srcaddr)[1] == ServerIP[1])
			&& (ServerIP[2] == 0 || ((unsigned char*)&ih->ip_srcaddr)[2] == ServerIP[2])
			&& (ServerIP[3] == 0 || ((unsigned char*)&ih->ip_srcaddr)[3] == ServerIP[3]))
		{
			tcp_header *tcph = (tcp_header *)((u_char*)pkt_data + 14 + ip_len);
			int tcp_len = tcph->data_offset * 4;
			unsigned char *DataStart = (u_char*)pkt_data + 14 + ip_len + tcp_len;
			int TotalHeaderSize = (unsigned int)(DataStart - pkt_data);
			int BytesToDump = header->len - TotalHeaderSize;
            ServerBytesSent = htonl(tcph->sequence) + BytesToDump;
            if (BytesToDump > 0)
			{
				DumpContent(DataStart, BytesToDump);
				Wait1FullPacketThenParse(DataStart, BytesToDump);
			}
		}
	}
}

pcap_t				* adapterHandle = NULL;
char                 errorBuffer[PCAP_ERRBUF_SIZE];
int StartCapturePackets(int AutoPickAdapter)
{
	pcap_if_t           * allAdapters;
	pcap_if_t           * adapter;

	// retrieve the adapters from the computer
	if (pcap_findalldevs_ex(PCAP_SRC_IF_STRING, NULL, &allAdapters, errorBuffer) == -1)
	{
		fprintf(stderr, "Error in pcap_findalldevs_ex function: %s\n", errorBuffer);
		return -1;
	}

	// if there are no adapters, print an error
	if (allAdapters == NULL)
	{
		printf("\nNo adapters found! Make sure WinPcap is installed.\n");
		return 0;
	}

	// print the list of adapters along with basic information about an adapter
	int crtAdapter = 0;
	for (adapter = allAdapters; adapter != NULL; adapter = adapter->next)
	{
		printf("\n%d.%s ", ++crtAdapter, adapter->name);
		printf("-- %s\n", adapter->description);
	}
	printf("\n");

	int adapterNumber;
	if (AutoPickAdapter == -1)
	{
		printf("Enter the adapter number between 1 and %d:", crtAdapter);
		scanf_s("%d", &adapterNumber);

		if (adapterNumber < 1 || adapterNumber > crtAdapter)
		{
			printf("\nAdapter number out of range.\n");

			// Free the adapter list
			pcap_freealldevs(allAdapters);

			return -1;
		}
	}
	else
		adapterNumber = AutoPickAdapter; //this is my default wireless adapter

	// parse the list until we reach the desired adapter
	adapter = allAdapters;
	for (crtAdapter = 0; crtAdapter < adapterNumber - 1; crtAdapter++)
		adapter = adapter->next;

	// open the adapter
	adapterHandle = pcap_open_live(adapter->name, // name of the adapter
		6000,         // portion of the packet to capture
		// 65536 guarantees that the whole 
		// packet will be captured
		PCAP_OPENFLAG_PROMISCUOUS, // promiscuous mode
		-1,             // read timeout - 1 millisecond
		errorBuffer    // error buffer
		);

	if (adapterHandle == NULL)
	{
		fprintf(stderr, "\nUnable to open the adapter : %s\n", adapter->name);

		// Free the adapter list
		pcap_freealldevs(allAdapters);

		return -1;
	}

	/* Check the link layer. We support only Ethernet for simplicity. */
	if (pcap_datalink(adapterHandle) != DLT_EN10MB)
	{
		fprintf(stderr, "\nThis program works only on Ethernet networks.\n");
		/* Free the device list */
		pcap_freealldevs(allAdapters);
		return -1;
	}
	printf("\nCapture session started on  adapter %s...\n", adapter->name);

	// free the adapter list
	pcap_freealldevs(allAdapters);

	//capture packets using callback
	pcap_loop(adapterHandle, 0, packet_handler, NULL);

	printf("Done creating background thread to monitor network trafic\n");


	FILE *f;
	errno_t opener = fopen_s(&f, "ServerIP.txt", "rt");
	if (f)
	{
		fscanf_s(f, "%d %d %d %d", &ServerIP[0], &ServerIP[1], &ServerIP[2], &ServerIP[3]);
		fclose(f);
	}

	return 0;
}

void StopCapturePackets()
{
	if (adapterHandle == NULL)
		return;
	pcap_breakloop(adapterHandle);
	pcap_close(adapterHandle);
	adapterHandle = NULL;
}

//ip.dst == 192.243.40.53 || ip.src == 192.243.40.53
//ip.dst == 192.243.44.239 || ip.src == 192.243.44.239
void SendPacket(unsigned char *Data, int Len)
{
	if (pcap_sendpacket(adapterHandle, Data, Len) != 0)
	{
		printf("CapturePackets.cpp : Error sending the packet: %s\n", pcap_geterr(adapterHandle));
		return;
	}
}