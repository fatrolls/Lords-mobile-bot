#include <Windows.h>
#include <stdio.h>
#include "ParseServerToClient.h"
#include "Tools.h"

#define MaxGameX			1024
#define MaxGameY			512
#define TimoutScanMS		(10*60*100)
#define TimoutScanCastleMS	(2*60*100)

#define SCAN_WORLD_PACKET_NO_SIZE_SIZE 47

#define USE_FIXED_COORDINATES_TO_SCAN
#ifdef USE_FIXED_COORDINATES_TO_SCAN
	unsigned int Validxy[500];
	unsigned int ValidxyCount = 0;
	unsigned int ValidxySent = 0;
	int GeteneratePosToScan(int &px, int &py)
	{
		px = Validxy[ValidxySent++];
		py = Validxy[ValidxySent++];
		ValidxySent = ValidxySent % ValidxyCount;
		return 0;
	}
	void OnCastlePopupPacketReceived(int x, int y)
	{
	}
#else
struct ScanStatusOnCoord
{
	unsigned int StatusTimout; // can rescan this location once the timout expires
	unsigned int ObjectType;
};

ScanStatusOnCoord GameMap[MaxGameY][MaxGameX];

int ContinueScanX = 0;
int ContinueScanY = 0;
int GeteneratePosToScan(int &px, int &py)
{
	//after a rescan without timeouts, we should just start scanning what we can
	if (ContinueScanY == 0 && ContinueScanX == 0)
	{
		for (int y = 0; y < MaxGameY; y++)
			for (int x = 0; x < MaxGameX; x++)
				GameMap[y][x].StatusTimout = 0; // mark all cells timeout
	}

	unsigned int TickNow = GetTickCount();
	for (int y = ContinueScanY; y < MaxGameY; y++)
		for (int x = ContinueScanX; x < MaxGameX; x++)
		{
			if (GameMap[y][x].StatusTimout < TickNow)
			{
				px = x;
				py = y;
				ContinueScanY = y;
				ContinueScanX = x + 1;
				GameMap[y][x].StatusTimout = TickNow + TimoutScanMS;
				return 0; // no issues
			}
		}

	ContinueScanY = 0;
	ContinueScanX = 0;

	//nothing to update yet
	return 1;
}

//if we managed to click a castle, we want to monitor this location with bigger frequency
void OnCastlePopupPacketReceived(int x, int y)
{
	if (y <0 || y> MaxGameY)
		return;
	if (x <0 || x> MaxGameX)
		return;
	GameMap[y][x].StatusTimout = GetTickCount() + TimoutScanCastleMS;
	GameMap[y][x].ObjectType = OBJECT_TYPE_PLAYER;
}
#endif

unsigned char *ScanWorldPacketsLoaded = NULL;
int ScanWorldPacketsLoadedCount = 0;
int ScanWorldPacketsLoadedSent = 0;
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

//we want to click all locations at first go
void InitContentGenerator()
{
	//if we catch a scan packet, we will try to override it
	LoadScanPacketsFromFile();
	//try to open file to load mapscan packets
	Validxy[ValidxyCount++] = 0;	Validxy[ValidxyCount++] = 2; 
	Validxy[ValidxyCount++] = 1;	Validxy[ValidxyCount++] = 3;
	Validxy[ValidxyCount++] = 2;	Validxy[ValidxyCount++] = 0;
	Validxy[ValidxyCount++] = 2;	Validxy[ValidxyCount++] = 2;
	Validxy[ValidxyCount++] = 3;	Validxy[ValidxyCount++] = 5;
	Validxy[ValidxyCount++] = 5;	Validxy[ValidxyCount++] = 1;
}
