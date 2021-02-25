#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <Windows.h>
#include "PacketContentGenerator.h"
#include "PacketAssembler.h"
#include "ParseServerToClient.h"
#include "ParseClientToServer.h"

PacketAssembler InjectQueue;

unsigned char *ScanWorldPacketsLoaded = NULL;
int ScanWorldPacketsLoadedCount = 0;
int ScanWorldPacketsLoadedSent = 0;
int ScanMapCountGen = 0;
int GenerateAreaToScan(unsigned char **PacketContent)
{
	if (ScanWorldPacketsLoadedCount == 0)
		return 1;
	*PacketContent = &ScanWorldPacketsLoaded[SCAN_WORLD_PACKET_NO_SIZE_SIZE * ScanWorldPacketsLoadedSent];
	ScanWorldPacketsLoadedSent++;
	ScanWorldPacketsLoadedSent = ScanWorldPacketsLoadedSent % ScanWorldPacketsLoadedCount;
	return 0;
}

void LoadScanPacketsFromFile()
{
	FILE *f;
	errno_t openerr = fopen_s(&f, "client_to_server", "rb");
	if (f == NULL)
	{
		printf("Could not open client_to_server file to read scan packets");
		return;
	}
	//read file content
	unsigned short ByteCount;
	unsigned char PacketBytes[65535 + 1000]; // no way to read more than this
	size_t BytesRead = fread(&ByteCount, 1, 2, f);
	while (BytesRead > 0)
	{
		BytesRead = fread(PacketBytes, 1, ByteCount - sizeof(ByteCount), f);
		//is this a packet we are looking for ?
		if (ByteCount == 49 && PacketBytes[0] == 0x99 || PacketBytes[1] == 0x08)
		{
			//check if we already loaded this scan packet
			if (ScanWorldPacketsLoaded == NULL || memcmp(ScanWorldPacketsLoaded, PacketBytes, SCAN_WORLD_PACKET_NO_SIZE_SIZE) != 0)
			{
#ifdef _DEBUG
				//			printf("Found a fetch data client packet\n");
				//			PrintDataHexFormat((unsigned char *)PacketBytes, ByteCount, 0, ByteCount);
#endif
				ScanWorldPacketsLoaded = (unsigned char*)realloc(ScanWorldPacketsLoaded, (ScanWorldPacketsLoadedCount + 1) * SCAN_WORLD_PACKET_NO_SIZE_SIZE + 10);
				memcpy(&ScanWorldPacketsLoaded[ScanWorldPacketsLoadedCount * SCAN_WORLD_PACKET_NO_SIZE_SIZE], PacketBytes, SCAN_WORLD_PACKET_NO_SIZE_SIZE);
				ScanWorldPacketsLoadedCount++;
			}
		}
		BytesRead = fread(&ByteCount, 1, 2, f);
	}
	printf("Loaded %d scan area packets \n", ScanWorldPacketsLoadedCount);
	fclose(f);
}

DWORD WINAPI PeriodicInjectScan(LPVOID lpParam)
{
	//wait a bit for game to start up
	while (1)
	{
		//wait a bit
		Sleep(1000);
		//do not overflood the game with scan packets, wait for server to send us a reply
		if (InjectQueue.empty() == false || ScanMapCountGen == 0)
			continue;
		ScanMapCountGen = 0;

/*		{
			//create a packet with content loaded from file
			char *Pkt = (char*)malloc(SCAN_WORLD_PACKET_NO_SIZE_SIZE + 2);
			*(unsigned short*)&Pkt[0] = SCAN_WORLD_PACKET_NO_SIZE_SIZE + 2;
			memcpy(&Pkt[2], &ScanWorldPacketsLoaded[SCAN_WORLD_PACKET_NO_SIZE_SIZE * ScanWorldPacketsLoadedSent], SCAN_WORLD_PACKET_NO_SIZE_SIZE);
			//queue to the network stream
			InjectQueue.AddBuffer(Pkt, SCAN_WORLD_PACKET_NO_SIZE_SIZE + 2);
			//mark so that we scan more a bit later
			ScanWorldPacketsLoadedSent++;
			ScanWorldPacketsLoadedSent = ScanWorldPacketsLoadedSent % ScanWorldPacketsLoadedCount;
		}/**/
/*		if(CastleClickSerializer != 0)
		{
			unsigned char *Pkt = (unsigned char*)malloc(11);
			*(unsigned short*)&Pkt[0] = 11;
			//0b 00 9a 08 c8 00 00 00 ff 03 ff
			Pkt[2] = 0x9a;
			Pkt[3] = 0x08;
			Pkt[4] = (++CastleClickSerializer);
			Pkt[5] = 0;
			Pkt[6] = 0;
			int x, y;
			GeteneratePosToScan(x, y);
			unsigned int GUID = GenerateIngameGUID(x, y);
			*(unsigned int*)&Pkt[7] = GUID;
			//queue to the network stream
			InjectQueue.AddBuffer((char*)Pkt, 11);
		}*/
	}
}

void InitScanPeriodicGen()
{
	LoadScanPacketsFromFile();
	//create a thread that will periodically inject packets into the stream
	HANDLE	RedirectTrafficThreadHandle = 0;
	DWORD   ThreadId;
	RedirectTrafficThreadHandle = CreateThread(NULL, 0, PeriodicInjectScan, NULL, 0, &ThreadId);
}