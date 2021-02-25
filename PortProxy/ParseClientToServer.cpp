#include <string.h>
#include <stdio.h>
#include "PacketContentGenerator.h"
#include "ParseServerToClient.h"
#include "Tools.h"
#include <Windows.h>

/*
if client does not recive a reply from the server, he will send the same packet multiple times
Got client click packet :
0B 00 9A 08 50 00 00 00 00 00 11
0B 00 9A 08 51 00 00 00 00 00 11 08 00 00 04 52 00 00 00
Reinserted packet : 59
0B 00 9A 08 50 00 00 00 00 00 11 0B 00 9A 08 51 00 00 00 00 00 11 08 00 00 04 52 00 00 00
Reinserted packet : 70
*/
//0b 00 9a 08 5b 00 00 00 77 02 26
//0b 00 9a 08 64 00 00 00 67 02 f2
//0b 00 9a 08 68 00 00 00 77 02 03
//0b 00 9a 08 6b 00 00 00 77 02 12
//0b 00 9a 08 70 00 00 00 77 02 95
//0b 00 9a 08 79 00 00 00 57 02 e6
//0b 00 9a 08 c8 00 00 00 ff 03 ff
//static int PacketSentCounter = 0x5B;
int CastleClickSerializer = 0;
static unsigned int LastEditStamp = 0;
int OnPacketForClickCastle(unsigned char *packet, unsigned int len)
{
#define CastleClickPacketBytesSize 11
	//if we receive the same packet we sent out, do not parse it again
/*	static char PrevPacketSent[CastleClickPacketBytesSize];
	if (memcmp(PrevPacketSent, packet, CastleClickPacketBytesSize) == 0)
	{
		printf("Packet got resent ?\n");
		return 0;
	}/**/

/*	{
		if (LastEditStamp > GetTickCount())
		{
			printf("allow server reply for castle click packet : \n");
			return 0;
		}
		LastEditStamp = GetTickCount() + 2000;
	}/**/
//return PPHT_DID_NOT_TOUCH_IT;

#ifdef _DEBUG
	printf("Got client click packet : \n");
	PrintDataHexFormat(packet, len, 0, len);
#endif
	CastleClickSerializer = packet[4];

	int x, y;
	if (GeteneratePosToScan(x, y) != 0)
		return PPHT_DID_NOT_TOUCH_IT; // do not change the packet

	unsigned int oldGUID = *(unsigned int*)&packet[7];
	int ox, oy;
	GetXYFromGUID(oldGUID, ox, oy);

	// write coordinates
	unsigned int GUID = GenerateIngameGUID(x, y);
	//failed to generate GUID from x, y
	if (GUID == 0)
		return PPHT_DID_NOT_TOUCH_IT;
	*(unsigned int*)&packet[7] = GUID;

//	memcpy(PrevPacketSent, packet, CastleClickPacketBytesSize);

	printf("Will try to scan map location %d %d instead of %d %d\n", y, x, ox, oy);
//	PrintDataHexFormat(packet, len, 0, len);
//	printf("\n");

	return PPHT_EDITED; // overrided content
}

int OnClientLoadMapContentPacket(unsigned char *packet, unsigned int len)
{
	//dump it into a file. we will process and analize it later
#define _DUMPPACKET_TO_FILE
#ifdef _DUMPPACKET_TO_FILE
	static FILE *FCONTENT = NULL;
	if (FCONTENT == NULL)
		errno_t er = fopen_s(&FCONTENT, "client_to_server_out", "ab");
	// might need to reassamble segmented packets later. TCP is a bytestream. The beggining of the packet should be a number indicating how much we need to read until the next packet
	if (FCONTENT)
	{
		//                fwrite(&BytesToDump, 1, 4, FCONTENT); //already present in the packet as the first 2 bytes
		fwrite(packet, 1, len, FCONTENT);
		fflush(FCONTENT);
	}
#endif
	return 1; // we are not ready to edit scan packets. Client will instant DC us

	unsigned char *NewContent;
	if (GenerateAreaToScan(&NewContent) != 0)
		return 1; // could not generate the new packet for some reason
	//override with new packet content
	memcpy(packet + 2, NewContent, len - 2);
//	memcpy(packet + 2+6, NewContent+6, len - 2-6); // 2 bytes opcode + 4 bytes packet serialization ?
	return 0;
}


int OnClientToServerSinglePacket(unsigned char *packet, unsigned int len)
{
	int ret = PPHT_DID_NOT_TOUCH_IT;
	//castle click packet
	//0b 00 9a 08 5b 00 00 00 77 02 26
	if (len == 11 && packet[0] == 11 && packet[1] == 0 && packet[2] == 0x9A && packet[3] == 0x08)
		//	if (packet[0] == 11 && packet[1] == 0 && packet[2] == 0x9A && packet[3] == 0x08)
	{
		ret = OnPacketForClickCastle(packet, len);
		if (ret != PPHT_DID_NOT_TOUCH_IT)
			return ret;
	}
	//is this a scroll screen packet ? Size includes the 2 bytes to store size
	if (len == 49 && packet[0] == 49 && packet[1] == 0x00 && packet[2] == 0x99 && packet[3] == 0x08)
	{
		ret = OnClientLoadMapContentPacket(packet, len);
		if (ret != PPHT_DID_NOT_TOUCH_IT)
			return ret;
	}/**/
	//is this "delete opened gifts" packet
	//0c 00 32 0b 85 7d bd 80 35 12 75 7e
	//0c 00 32 0b 7c 9f d0 cc cc 8f e3 84
	//0c 00 32 0b 70 1c 69 60 c2 0e f0 98
	//0c 00 32 0b c5 72 33 a6 db b0 ab 86
/*	if (len == 12 && packet[0] == 12 && packet[1] == 0 && packet[2] == 0x32 && packet[3] == 0x0B)
	{
		int ret = OnPacketForClickCastle(packet, len);
		if (ret == 0)
			return 0;
	}
	//on chat packet that is large enough
	//43 00 bb 0b 00 00 01 00 6f 84 9f 5d 00 00 00 00 97 09 0e 17 00 00 00 00 c5 02 00 00 00 00 00 00 01 00 11 00 54 75 64 69 20 73 65 63 6f 6e 64 61 00 0d 6a 63 77 00 00 05 09 00 31 32 33 34 35 36 37 38 39
	//Co]	Tudi secondajcw	123456789
	//3b 00 bb 0b 00 00 01 00 db 84 9f 5d 00 00 00 00 97 09 0e 17 00 00 00 00 c8 02 00 00 00 00 00 00 01 00 11 00 54 75 64 69 20 73 65 63 6f 6e 64 61 00 0d 6a 63 77 00 00 05 01 00 20
	if (len == 0x3b && packet[0] == 0x3b && packet[1] == 0 && packet[2] == 0xBB && packet[3] == 0x0B)
	{
		int ret = OnPacketForClickCastle(packet, len);
		if (ret == 0)
			return 0;
	}*/

	//we did not change the packet
	return ret;
}

int OnClientToServerPacket(unsigned char *packet, unsigned int len)
{
	int SummaryReturn = 1;
	//check if it is a multi packet packet
	unsigned int BytesParsed = 0;
	while (BytesParsed < len)
	{
		unsigned short SubPacketLen = *(unsigned short *)&packet[BytesParsed];
		if (BytesParsed + SubPacketLen <= len)
		{
			int ret = OnClientToServerSinglePacket(&packet[BytesParsed], SubPacketLen);
			if (ret > SummaryReturn)
				SummaryReturn = ret;
		}
		BytesParsed += SubPacketLen;
		//if we edited even 1 packet, we should recalc checksum for the packet
	}
	//we did not change the packet
	return SummaryReturn;
}