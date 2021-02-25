#include "CapturePackets.h"
#include <pcap.h>
#include "ParsePackets.h"

#define ETH_ALEN 6
#define	ETHERTYPE_PUP		0x0200      /* Xerox PUP */
#define	ETHERTYPE_IP		0x0800		/* IP */
#define	ETHERTYPE_ARP		0x0806		/* Address resolution */
#define	ETHERTYPE_REVARP	0x8035		/* Reverse ARP */
#define	ETHERTYPE_IPV6		0x86DD		/* IP */
#pragma pack(push, 1)
struct ether_header
{
	u_int8_t  ether_dhost[ETH_ALEN];	/* destination eth addr	*/
	u_int8_t  ether_shost[ETH_ALEN];	/* source ether addr	*/
	u_int16_t ether_type;		        /* packet type ID field	*/
};

typedef struct ip_hdr
{
	unsigned char ip_header_len : 4; // 4-bit header length (in 32-bit words) normally=5 (Means 20 Bytes may be 24 also)
	unsigned char ip_version : 4; // 4-bit IPv4 version
	unsigned char ip_tos; // IP type of service
	unsigned short ip_total_length; // Total length
	unsigned short ip_id; // Unique identifier

	unsigned char ip_frag_offset : 5; // Fragment offset field

	unsigned char ip_more_fragment : 1;
	unsigned char ip_dont_fragment : 1;
	unsigned char ip_reserved_zero : 1;

	unsigned char ip_frag_offset1; //fragment offset

	unsigned char ip_ttl; // Time to live
	unsigned char ip_protocol; // Protocol(TCP,UDP etc)
	unsigned short ip_checksum; // IP checksum
	unsigned int ip_srcaddr; // Source address
	unsigned int ip_destaddr; // Source address
} IPV4_HDR;

// TCP header
typedef struct tcp_header
{
	unsigned short source_port; // source port
	unsigned short dest_port; // destination port
	unsigned int sequence; // sequence number - 32 bits
	unsigned int acknowledge; // acknowledgement number - 32 bits

	unsigned char ns : 1; //Nonce Sum Flag Added in RFC 3540.
	unsigned char reserved_part1 : 3; //according to rfc
	unsigned char data_offset : 4; /*The number of 32-bit words in the TCP header.
								   This indicates where the data begins.
								   The length of the TCP header is always a multiple
								   of 32 bits.*/

	unsigned char fin : 1; //Finish Flag
	unsigned char syn : 1; //Synchronise Flag
	unsigned char rst : 1; //Reset Flag
	unsigned char psh : 1; //Push Flag
	unsigned char ack : 1; //Acknowledgement Flag
	unsigned char urg : 1; //Urgent Flag

	unsigned char ecn : 1; //ECN-Echo Flag
	unsigned char cwr : 1; //Congestion Window Reduced Flag

	////////////////////////////////

	unsigned short window; // window
	unsigned short checksum; // checksum
	unsigned short urgent_pointer; // urgent pointer
} TCP_HDR;
#pragma pack(pop)


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
#define MAX_PACKET_SIZE_SERVER_SENDS	0x3FFF
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
		QueuePacketToProcess(&TempPacketStore[ReadIndex + 2], WAITING_FOR_X_BYTES - 2);
		ReadIndex += WAITING_FOR_X_BYTES;
	}

	//did we pop all packets ?
	if (ReadIndex == WriteIndex)
	{
		ReadIndex = 0;
		WriteIndex = 0;
	}
	else if (WAITING_FOR_X_BYTES == 0)
	{
		printf("a)Packet header->size = 0. This is bad, try to resync reading\n");
		WriteIndex = 0;
		ReadIndex = 0;
		ThrowAwayPacketsUntilSmallPackets = 1;
		ThrowAwayCount = 0;
		return;
	}
	//try to move data to the beginning of the buffer
	if (ReadIndex > 0)
	{
		for (unsigned int i = 0; i <= WriteIndex - ReadIndex; i++)
			TempPacketStore[i] = TempPacketStore[ReadIndex + i];
		WriteIndex -= ReadIndex;
		ReadIndex = 0;
	}
}

void Wait1FullPacketThenParse(unsigned char *data, unsigned int size)
{
	if (size == 0)
		return;
	//theoretical size of a full packet. This is GAME specific !
	if (WriteIndex == 0)
	{
		unsigned short GamePacketSize = *(unsigned short*)data;
		if (size == GamePacketSize)
		{
			//ProcessPacket1(data, size);
			QueuePacketToProcess(&data[2], size - 2);
			//this could be a full packet. Consider ourself syncronized
			if (ThrowAwayPacketsUntilSmallPackets > 0)
				ThrowAwayPacketsUntilSmallPackets--;
			return;
		}
		//more than 1 server packet inside a single network packet
		int BytesRemain = size;
		while (BytesRemain > 0 && 
				BytesRemain >= GamePacketSize && ThrowAwayPacketsUntilSmallPackets == 0 )
		{
			//wow, that is bad
			if (GamePacketSize == 0)
			{
				printf("b)Packet header->size = 0. This is bad, try to resync reading\n");
				WriteIndex = 0;
				ReadIndex = 0;
				ThrowAwayPacketsUntilSmallPackets = 1;
				ThrowAwayCount = 0;
				return;
			}
			//ProcessPacket1(&data[2], GamePacketSize - 2);
			QueuePacketToProcess(&data[2], GamePacketSize - 2);
			//jump to the start of the next packet
			data = &data[GamePacketSize];
			BytesRemain -= (int)GamePacketSize;
			GamePacketSize = *(unsigned short*)data;
		}
		if (BytesRemain > 0)
		{
#ifdef _DEBUG
			printf("1)Adding fragmented packet with size : %d\n", size);
#endif
			QueuePacketForMore(data, BytesRemain); // should never happen
		}
		return;
	}
	//if we got here than this is a fragmented packet with first fragment
#ifdef _DEBUG
	printf("2)Adding fragmented packet with size : %d\n", size);
#endif
	QueuePacketForMore(data, size);
}

#define PROTOCOL_TCPIP	6
#define SIZE_ETHERNET 14

class SniffSessionStore
{
public:
	SniffSessionStore()
	{
		SniffLockedIn = 0;
	}
	int IsTrafficSelected(ip_hdr* ih)
	{
		if (SniffLockedIn == 0)
		{
			if (ih->ip_protocol == PROTOCOL_TCPIP
				&& (((unsigned char*)&ih->ip_destaddr)[0] == 192 && ((unsigned char*)&ih->ip_destaddr)[1] == 243))
			{
				SniffLockedIn = 1;
				SelectedIP = ih->ip_destaddr;
				int ip_len = ih->ip_header_len * 4;
				tcp_header* tcph = (tcp_header*)((u_char*)ih + ip_len);
				SelectedPort = tcph->dest_port;
			}
			if (ih->ip_protocol == PROTOCOL_TCPIP
				&& (((unsigned char*)&ih->ip_srcaddr)[0] == 192 && ((unsigned char*)&ih->ip_srcaddr)[1] == 243))
			{
				SniffLockedIn = 1;
				SelectedIP = ih->ip_srcaddr;
				int ip_len = ih->ip_header_len * 4;
				tcp_header* tcph = (tcp_header*)((u_char*)ih + ip_len);
				SelectedPort = tcph->source_port;
			}
			if (SniffLockedIn == 1)
			{
				printf("Selecting server IP for sniffing : ");
				printf("%d.%d.%d.%d:%d\n", ((unsigned char*)&SelectedIP)[0], ((unsigned char*)&SelectedIP)[1], ((unsigned char*)&SelectedIP)[2], ((unsigned char*)&SelectedIP)[3], htons(SelectedPort));
			}
		}

		if (SniffLockedIn == 0)
			return 0;

		if (ih->ip_protocol == PROTOCOL_TCPIP && ih->ip_destaddr == SelectedIP)
		{
			int ip_len = ih->ip_header_len * 4;
			tcp_header* tcph = (tcp_header*)((u_char*)ih + ip_len);
			if (SelectedPort == tcph->dest_port)
				return 1;
		}
		if (ih->ip_protocol == PROTOCOL_TCPIP && ih->ip_srcaddr == SelectedIP)
		{
			int ip_len = ih->ip_header_len * 4;
			tcp_header* tcph = (tcp_header*)((u_char*)ih + ip_len);
			if (SelectedPort == tcph->source_port)
				return 2;
		}
		return 0;
	}
	int SniffLockedIn;
	unsigned int SelectedIP;
	unsigned short SelectedPort;
};
SniffSessionStore sSession;

// Callback function invoked by libpcap for every incoming packet 
void packet_handler(u_char *param, const struct pcap_pkthdr *header, const u_char *pkt_data)
{
	ip_hdr *ih;
	u_int ip_len;
	u_int length = header->len;

	//Unused variable
	(VOID)(param);

	//something must have went wrong
	if (length < SIZE_ETHERNET)
		return;
	//presumably we parsed ethernet header here
	length -= SIZE_ETHERNET;
	struct ether_header* eptr = (struct ether_header*)pkt_data;
	unsigned short eth_Type = ntohs(eptr->ether_type);
	if (eth_Type != ETHERTYPE_IP)
	{
		if (eth_Type == ETHERTYPE_IPV6)
		{
			static int ReportItOnce = 0;
			if (ReportItOnce == 0)
			{
				printf("!! Right now only support IPV4. Can't parse ethernet IPV6. last warning\n");
				ReportItOnce = 1;
			}
		}
		return;
	}

	//maybe not a full IP header
	if (length < sizeof(ip_hdr))
		return;

	// retireve the position of the ip header
	ih = (ip_hdr *)(pkt_data + SIZE_ETHERNET); //length of ethernet header
	ip_len = ih->ip_header_len * 4;
	if (ip_len < 20) 
	{
		if(ip_len != 0)
			printf("   * Invalid IP header length: %u bytes. IP ver %d\n", ip_len, ih->ip_version);
		return;
	}
	length -= ip_len;

	if (length < sizeof(tcp_header))
		return;

	//this should never trigger
	if (header->len != header->caplen)
		printf("Captured data is not the same ! %d-%d\n", header->len, header->caplen);

	int TrafficSelected = sSession.IsTrafficSelected(ih);
    //dump all incomming packet
#ifdef _DEBUG
    if (TrafficSelected == 1 )
    {
        tcp_header *tcph = (tcp_header *)((u_char*)pkt_data + SIZE_ETHERNET + ip_len);
        int tcp_len = tcph->data_offset * 4;
        unsigned char *DataStart = (u_char*)pkt_data + SIZE_ETHERNET + ip_len + tcp_len;
        int TotalHeaderSize = (unsigned int)(DataStart - pkt_data);
        int BytesToDump = header->len - TotalHeaderSize;
        if (BytesToDump > 0)
        {
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
        }
    }
#endif

	//capturing all TCP packets from IP
	if (TrafficSelected == 2)
	{
		tcp_header *tcph = (tcp_header *)((u_char*)pkt_data + SIZE_ETHERNET + ip_len);
		int tcp_len = tcph->data_offset * 4;
		unsigned char *DataStart = (u_char*)pkt_data + SIZE_ETHERNET + ip_len + tcp_len;
		int TotalHeaderSize = (unsigned int)(DataStart - pkt_data);
		int BytesToDump = header->len - TotalHeaderSize;
		if (BytesToDump > 0)
		{
#ifdef _DEBUG
			DumpContent(DataStart, BytesToDump);
#endif
			Wait1FullPacketThenParse(DataStart, BytesToDump);
		}
	}
}


pcap_t* adapterHandle = NULL;
char                 errorBuffer[PCAP_ERRBUF_SIZE];
int PickAdapter(int AutoPickAdapter)
{
	pcap_if_t* allAdapters;
	pcap_if_t* adapter;

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

	// free the adapter list
	pcap_freealldevs(allAdapters);

	return adapterNumber;
}

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
	int adapterNumber = AutoPickAdapter; //this is my default wireless adapter

	// parse the list until we reach the desired adapter
	adapter = allAdapters;
	for (int crtAdapter = 0; crtAdapter < adapterNumber - 1; crtAdapter++)
		adapter = adapter->next;

	// open the adapter
	adapterHandle = pcap_open_live(adapter->name, // name of the adapter
		BUFSIZ,         // portion of the packet to capture
		// 65536 guarantees that the whole 
		// packet will be captured
		PCAP_OPENFLAG_PROMISCUOUS, // promiscuous mode
		1000,             // read timeout - 1 millisecond
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

	WriteIndex = 0;
	ReadIndex = 0;
	ThrowAwayPacketsUntilSmallPackets = 1;
	ThrowAwayCount = 0;

	//capture packets using callback
	pcap_loop(adapterHandle, 0, packet_handler, NULL);

	// if we got here, chances are network got interrupted
//	exit(1);

	printf("Done creating background thread to monitor network trafic\n");

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