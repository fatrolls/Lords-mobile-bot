#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "windivert.h"
#include "StreamInfo.h"

double time_passed = -1;
LARGE_INTEGER base, freq;
HANDLE console;

void InitShowPacketInfo(int LogToConsole)
{
	time_passed = 0;
	QueryPerformanceFrequency(&freq);
	QueryPerformanceCounter(&base);
	if (LogToConsole)
		console = GetStdHandle(STD_OUTPUT_HANDLE);
	else
		console = NULL;
}

void ShowPacketInfo(WINDIVERT_ADDRESS addr, unsigned char *packet, unsigned int packet_len)
{
	if (console == NULL)
		return;

	if (time_passed == -1)
	{
		// Set up timing:
		QueryPerformanceFrequency(&freq);
		QueryPerformanceCounter(&base);
	}

	// Print info about the matching packet.
	PWINDIVERT_IPV6HDR ipv6_header;
	PWINDIVERT_ICMPHDR icmp_header;
	PWINDIVERT_ICMPV6HDR icmpv6_header;
	PWINDIVERT_UDPHDR udp_header;
	PWINDIVERT_IPHDR ip_header;
	PWINDIVERT_TCPHDR tcp_header;
	WinDivertHelperParsePacket(packet, packet_len, &ip_header, &ipv6_header, &icmp_header, &icmpv6_header, &tcp_header, &udp_header, NULL, NULL);
	if (ip_header == NULL && ipv6_header == NULL)
	{
		fprintf(stderr, "warning: junk packet\n");
	}
	// Dump packet info: 
	putchar('\n');
	SetConsoleTextAttribute(console, FOREGROUND_RED);
	time_passed = (double)(addr.Timestamp - base.QuadPart) / (double)freq.QuadPart;
	printf("Packet [Timestamp=%.8g, Direction=%s IfIdx=%u SubIfIdx=%u Loopback=%u]\n", time_passed, (addr.Direction == WINDIVERT_DIRECTION_OUTBOUND ? "outbound" : "inbound"), addr.IfIdx, addr.SubIfIdx, addr.Loopback);
	if (ip_header != NULL)
	{
		UINT8 *src_addr = (UINT8 *)&ip_header->SrcAddr;
		UINT8 *dst_addr = (UINT8 *)&ip_header->DstAddr;
		SetConsoleTextAttribute(console, FOREGROUND_GREEN | FOREGROUND_RED);
		printf("IPv4 [Version=%u HdrLength=%u TOS=%u Length=%u Id=0x%.4X Reserved=%u DF=%u MF=%u FragOff=%u TTL=%u Protocol=%u Checksum=0x%.4X SrcAddr=%u.%u.%u.%u DstAddr=%u.%u.%u.%u]\n",
			ip_header->Version, ip_header->HdrLength, ntohs(ip_header->TOS), ntohs(ip_header->Length), ntohs(ip_header->Id), WINDIVERT_IPHDR_GET_RESERVED(ip_header),
			WINDIVERT_IPHDR_GET_DF(ip_header), WINDIVERT_IPHDR_GET_MF(ip_header), ntohs(WINDIVERT_IPHDR_GET_FRAGOFF(ip_header)), ip_header->TTL,
			ip_header->Protocol, ntohs(ip_header->Checksum), src_addr[0], src_addr[1], src_addr[2], src_addr[3], dst_addr[0], dst_addr[1], dst_addr[2], dst_addr[3]);
	}
	//we only care about TCP packets
	if (tcp_header != NULL)
	{
		SetConsoleTextAttribute(console, FOREGROUND_GREEN);
		printf("TCP [SrcPort=%u DstPort=%u SeqNum=%u AckNum=%u HdrLength=%u Reserved1=%u Reserved2=%u Urg=%u Ack=%u Psh=%u Rst=%u Syn=%u Fin=%u Window=%u Checksum=0x%.4X UrgPtr=%u]\n",
			ntohs(tcp_header->SrcPort), ntohs(tcp_header->DstPort),
			ntohl(tcp_header->SeqNum), ntohl(tcp_header->AckNum),
			tcp_header->HdrLength, tcp_header->Reserved1,
			tcp_header->Reserved2, tcp_header->Urg, tcp_header->Ack,
			tcp_header->Psh, tcp_header->Rst, tcp_header->Syn,
			tcp_header->Fin, ntohs(tcp_header->Window),
			ntohs(tcp_header->Checksum), ntohs(tcp_header->UrgPtr));
	}

	//print the content of the packet
	{
		SetConsoleTextAttribute(console, FOREGROUND_GREEN | FOREGROUND_BLUE);
		for (unsigned int i = 0; i < packet_len; i++)
		{
			if (i % 20 == 0)
				printf("\n\t");
			printf("%.2X", (UINT8)packet[i]);
		}
		SetConsoleTextAttribute(console, FOREGROUND_RED | FOREGROUND_BLUE);
		for (unsigned int i = 0; i < packet_len; i++)
		{
			if (i % 40 == 0)
				printf("\n\t");
			if (isprint(packet[i]))
				putchar(packet[i]);
			else
				putchar('.');
		}
		putchar('\n');
	}
}