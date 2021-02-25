#include <pcap.h>
#include "PrepareSendPacket.h"
#include "CapturePackets.h"

typedef struct PseudoHeader {
	unsigned long int source_ip;
	unsigned long int dest_ip;
	unsigned char reserved;
	unsigned char protocol;
	unsigned short int tcp_length;
}PseudoHeader;

unsigned short ComputeChecksum3(unsigned char *data, int plen)
{
	unsigned long sum = 0;

	for (int len = 0; len < plen; len += 2)
		sum += ((unsigned short)data[len] << 8) + data[len + 1];

	if (plen & 1)
		sum += ((unsigned long)data[plen - 1]) << 8;

	while (sum >> 16)
		sum = (sum & 0xFFFF) + (sum >> 16);

	sum = (sum >> 16) + (sum & 0xFFFF);
	sum = (sum >> 16) + (sum & 0xFFFF);
	sum = (~sum & 0xFFFF);
	return (unsigned short)sum;
}


unsigned short ComputeChecksum2(unsigned char *data, int plen)
{
	unsigned long sum = 0;

	unsigned short *temp = (unsigned short *)data;
	for (int len = 0; len < plen / 2; len++)
		sum += temp[len];

	if (plen & 1)
		sum += (unsigned long)data[plen - 1];

	while (sum >> 16)
		sum = (sum & 0xFFFF) + (sum >> 16);

	sum = (sum >> 16) + (sum & 0xFFFF);
	sum = (~sum & 0xFFFF);
	return (unsigned short)sum;
}

unsigned short ComputeChecksum(unsigned char *data, int len)
{
	unsigned long sum = 0;  /* assume 32 bit long, 16 bit short */
	unsigned short *temp = (unsigned short *)data;

	while (len > 1) {
		sum += *temp++;
		if (sum & 0x80000000)   /* if high order bit set, fold */
			sum = (sum & 0xFFFF) + (sum >> 16);
		len -= 2;
	}

	if (len)       /* take care of left over byte */
		sum += (unsigned short) *((unsigned char *)temp);

	while (sum >> 16)
		sum = (sum & 0xFFFF) + (sum >> 16);

	return (unsigned short)(~sum);
}

void CreatePseudoHeaderAndComputeTcpChecksum(tcp_header *tcp_header, ip_hdr *ip_header, unsigned char *data, unsigned int DataSize)
{
	/*The TCP Checksum is calculated over the PseudoHeader + TCP header +Data*/

	/* Find the size of the TCP Header + Data */
	int segment_len = ntohs(ip_header->ip_total_length) - ip_header->ip_header_len * 4;

	/* Total length over which TCP checksum will be computed */
	int header_len = sizeof(PseudoHeader) + segment_len;

	/* Allocate the memory */

	unsigned char *hdr = (unsigned char *)malloc(header_len);

	/* Fill in the pseudo header first */

	PseudoHeader *pseudo_header = (PseudoHeader *)hdr;

	pseudo_header->source_ip = ip_header->ip_srcaddr;
	pseudo_header->dest_ip = ip_header->ip_destaddr;
	pseudo_header->reserved = 0;
	pseudo_header->protocol = ip_header->ip_protocol;
	pseudo_header->tcp_length = htons(segment_len);


	/* Now copy TCP */
	memcpy((hdr + sizeof(PseudoHeader)), (void *)tcp_header, tcp_header->data_offset * 4);

	/* Now copy the Data */
	memcpy((hdr + sizeof(PseudoHeader) + tcp_header->data_offset * 4), data, DataSize);

	/* Calculate the Checksum */
	tcp_header->checksum = ComputeChecksum(hdr, header_len);

	/* Free the PseudoHeader */
	free(hdr);

	return;

}


void PrepareAndSendPacket(unsigned char *Data, int Len, int PayloadSize)
{
	if (ClientBytesSent == 0 || ServerBytesSent == 0)
		return;
	ip_hdr *ih = (ip_hdr *)(Data + 14); //length of ethernet header

	tcp_header *tcph = (tcp_header *)((u_char*)Data + 14 + ih->ip_header_len * 4);
	tcph->sequence = htonl(ClientBytesSent);
	tcph->acknowledge = htonl(ServerBytesSent);
	ClientBytesSent += PayloadSize;
    tcph->checksum = 0;

#if 0
	//check if i can generate correct checksum
 /*   {
		unsigned short oldIPChecksum = ih->ip_checksum;
		ih->ip_checksum = 0;
		unsigned short myIPChecksum = ComputeChecksum(Data + 14, ih->ip_header_len * 4);
		unsigned short myIPChecksum2 = htons(myIPChecksum);
		ih->ip_checksum = oldIPChecksum;
	}*/

	PseudoHeader psh;

	//    tcph->checksum = htons(ComputeChecksum(Data, Len));
	tcph->checksum = htons(ComputeChecksum((unsigned char *)tcph, &Data[Len] - (unsigned char*)tcph));
	//    tcph->checksum = ComputeChecksum2(Data, Len);
#endif
	CreatePseudoHeaderAndComputeTcpChecksum(tcph, ih, &Data[Len - PayloadSize], PayloadSize);

	SendPacket(Data, Len);
}