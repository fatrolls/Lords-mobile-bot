#include <time.h>
#include "LordsMobileControl.h"
#include "HTTPSendData.h"
#include <windows.h>
#include <stdio.h>
#include "Tools.h"
#include "ParsePackets.h"
#include "CapturePackets.h"
#include "PrepareSendPacket.h"

int		KeepGameScanThreadsRunning = 1;
int		ThreadParamScanGameData = 0;
HANDLE	ScanGameProcessThreadHandle = 0;
int		InverseScanOrder = (time(NULL) / (60*24)) % 2;

void InvertGameScanDirection()
{
	InverseScanOrder = 1 - InverseScanOrder;
	printf("New scan direction is : %d\n", InverseScanOrder);
}

bool ClickPlayerPacketCatched = false;
bool ObjectListPacketReceived = false;

#define MinGameX	5
#define MinGameY	5
#define MaxGameX	1024
#define MaxGameY	512
#define ScanJumpSizeX	13
#define ScanJumpSizeY	9

enum ScanStatuses
{
	SS_WaitingForClickPlayerPacket,
	SS_ReceivedClickPlayerPacket,
	SS_ClickPlayerPacketSent,
	SS_ObjectListPacketReceived,
	SS_RestartScan,
	SS_WaitExternalParseStart,
	SS_WaitExternalParseEnd,
	SS_StartNewScanCycle,
	SS_UpdatePlayerInfo,
};

ScanStatuses ScanStatus = SS_WaitingForClickPlayerPacket;
int ScanX = MinGameX;
int ScanY = MinGameY;
#define ExternalParserPeriod 0
unsigned int WaitExternalParserTimeoutStamp = 0;

unsigned int *IngameMapUpdateStamp = NULL;
void InitIngameMap()
{
	int SafeMemorySize = sizeof(unsigned int) * (MaxGameX + 50) * (MaxGameY + 50);
	IngameMapUpdateStamp = (unsigned int *)malloc(SafeMemorySize);
	memset(IngameMapUpdateStamp, 0, SafeMemorySize);
}

void GetLeastUpdatedLocation(int *OutX, int * OutY)
{
	if (IngameMapUpdateStamp == NULL)
		InitIngameMap();
	unsigned int MinStamp = 0;
	for (int y = MinGameY; y < MaxGameY; y++)
		for (int x = MinGameX; x < MaxGameX; x++)
			if (IngameMapUpdateStamp[y * MaxGameY + x] < MinStamp)
				MinStamp = IngameMapUpdateStamp[y * MaxGameY + x];
}

u_char *ClickPlayerPacketRetSample = NULL;
int ClickPlayerPacketSize = 0;
int ClickPlayerPacketHeaderSize = 0;
void HandleCastleClickPacket(const unsigned char *pkt_data, int len, const unsigned char *GameData, int GameLen)
{
	if (ClickPlayerPacketRetSample != NULL)
		return;
	printf("Received castle click packet\n");
	//is this a guid data ?
	int x, y;
	if (GetXYFromGUID(*(unsigned int*)GameData[7], x, y) != 0)
		return;
	printf("Castle location at click is : %d %d\n", x, y);

	ClickPlayerPacketRetSample = (u_char*)malloc(len);
	ClickPlayerPacketSize = len;
	memcpy(ClickPlayerPacketRetSample, pkt_data, len);
	ClickPlayerPacketHeaderSize = GameData - pkt_data;
}

static unsigned char *ScanWorldPacketSample = NULL;
static unsigned short ScanWorldPacketSampleSize = 0;
//static unsigned short ScanWorldPacketSampleDataStart = 0;
static unsigned char *ScanWorldPacketsLoaded = NULL;
static unsigned int ScanWorldPacketsLoadedCount = 0;
static unsigned int ScanWorldPacketsLoadedSent = 0;
void HandleLoadMapSpawnsPacket(const unsigned char *pkt_data, int len, const unsigned char *GameData, int GameLen)
{
	if (ScanWorldPacketSampleSize != 0)
		return;
	printf("Found a packet we could replicate to scan the world\n");
	ScanWorldPacketSampleSize = len;
	ScanWorldPacketSample = (unsigned char *)malloc(len);
	memcpy(ScanWorldPacketSample, pkt_data, len);
//	ScanWorldPacketSampleDataStart = (GameData - pkt_data) + 2;
	ClickPlayerPacketCatched = true;
}

#define SCAN_WORLD_PACKET_SIZE 49
#define SCAN_WORLD_PACKET_NO_SIZE_SIZE 47
void OnLordsClientPacketReceived(const unsigned char *pkt_data, int len, const unsigned char *GameData, int GameLen)
{
	//first 2 bytes are packet len. Unless these confirm the size, the packet did not arrive completely
	//0b 00 9a 08 50 00 00 00 67 02 f2
    //0b 00 9a 08 51 00 00 00 77 02 12
    if (GameLen == 11 && GameData[0] == 11 && GameData[1] == 0x00 && GameData[2] == 0x9A && GameData[3] == 0x08)
		HandleCastleClickPacket(pkt_data, len, GameData, GameLen);

    //is this a scroll screen packet ? try to remember it. Size includes the 2 bytes to store size
    if (GameLen == 49 && GameData[0] == 49 && GameData[1] == 0x00 && GameData[2] == 0x99 && GameData[3] == 0x08)
		HandleLoadMapSpawnsPacket(pkt_data, len, GameData, GameLen);

    return;
}

void ConstructClickPlayerPacket()
{
    if (ClickPlayerPacketRetSample == NULL)
        return;
	int x, y;
	GetLeastUpdatedLocation(&x, &y);
	unsigned int GUID = GenerateIngameGUID(x,y);
	*(unsigned int*)ClickPlayerPacketRetSample[ClickPlayerPacketHeaderSize + 7] = GUID;
	printf("Constructing castle click packet for location : %d %d\n", x, y);
}

void ConstructScanPacket()
{
    if (ScanWorldPacketSample == NULL)
        return;
    memcpy(&ScanWorldPacketSample[ScanWorldPacketSampleSize - SCAN_WORLD_PACKET_NO_SIZE_SIZE], &ScanWorldPacketsLoaded[SCAN_WORLD_PACKET_NO_SIZE_SIZE * ScanWorldPacketsLoadedSent], SCAN_WORLD_PACKET_NO_SIZE_SIZE);
    ScanWorldPacketsLoadedSent++;
    ScanWorldPacketsLoadedSent = ScanWorldPacketsLoadedSent % ScanWorldPacketsLoadedCount;

    printf("Constructing a scan map packet : %d\n", ScanWorldPacketsLoadedSent);
}

void SendClickPlayerPacket()
{
	if (ClickPlayerPacketCatched == false)
		return;
	PrepareAndSendPacket(ClickPlayerPacketRetSample, ClickPlayerPacketSize, 11);
}

void SendScanPacket()
{
    if (ScanWorldPacketSample == NULL)
        return;
	PrepareAndSendPacket(ScanWorldPacketSample, ScanWorldPacketSampleSize, SCAN_WORLD_PACKET_SIZE);
}

//this is already done in ParsePackets.cpp
void ParseObjectPacketAndGenerateOutput()
{
}

void OnCastlePopupPacketReceived(int x, int y)
{
	OnMapLocationUpdate(x, y);
}

void OnMapLocationUpdate(int x, int y)
{
	if (IngameMapUpdateStamp == NULL)
		InitIngameMap();

	#define UPDATE_DISTANCE 10

	int StartX = x - UPDATE_DISTANCE;
	int EndX = x + UPDATE_DISTANCE;
	StartX = MAX(StartX, 0);
	EndX = MIN(EndX, MaxGameX);

	int StartY = y - UPDATE_DISTANCE;
	int EndY = y + UPDATE_DISTANCE;
	StartY = MAX(StartY, 0);
	EndY = MIN(EndY, MaxGameX);

	for (int y = StartY; y < EndY; y++)
		for (int x = StartX; x < EndX; x++)
		{
			int xDist = abs(UPDATE_DISTANCE / 2 - x);
			int yDist = abs(UPDATE_DISTANCE / 2 - y);
			IngameMapUpdateStamp[y * MaxGameY + x] = MAX(IngameMapUpdateStamp[y * MaxGameY + x], GetTickCount() + xDist * yDist);
		}

	//presume we received an update for our last request 
	WaitExternalParserTimeoutStamp = GetTickCount();
}

DWORD WINAPI BackgroundProcessScanGame(LPVOID lpParam)
{
	int LoopCounter = 0;
	unsigned int WaitServerReplyTimout = 0;
	while (KeepGameScanThreadsRunning == 1)
	{
		if (ScanStatus == SS_WaitingForClickPlayerPacket)
		{
			if (ClickPlayerPacketCatched == true)
				ScanStatus = SS_ReceivedClickPlayerPacket;
		}
		if (ScanStatus == SS_ReceivedClickPlayerPacket || ScanStatus == SS_StartNewScanCycle)
		{
//			ConstructClickPlayerPacket();
            ConstructScanPacket();
//			SendClickPlayerPacket();
            SendScanPacket();
//			printf("Will try to scan ingame area at %d:%d\n", ScanX, ScanY);
			ScanStatus = SS_ClickPlayerPacketSent;
			WaitServerReplyTimout = GetTickCount() + 10000;
		}
		if (ScanStatus == SS_ClickPlayerPacketSent && (ObjectListPacketReceived == true || WaitServerReplyTimout < GetTickCount()))
		{
			ParseObjectPacketAndGenerateOutput();
			ObjectListPacketReceived = false;
			ScanX = (ScanX + ScanJumpSizeX);
			if (ScanX > MaxGameX)
			{
				ScanX = MinGameX;
				ScanY = ScanY + ScanJumpSizeY;
			}
			if (ScanY > MaxGameY)
				ScanStatus = SS_RestartScan;
			else
				ScanStatus = SS_WaitExternalParseStart;
		}
		if (ScanStatus == SS_RestartScan)
		{
			ScanX = MinGameX;
			ScanY = MinGameY;
			ScanStatus = SS_WaitExternalParseStart;
		}
		if (ScanStatus == SS_WaitExternalParseStart)
		{
//			HTTP_GenerateMaps();
			WaitExternalParserTimeoutStamp = ExternalParserPeriod + GetTickCount();
			ScanStatus = SS_WaitExternalParseEnd;
		}
		if (ScanStatus == SS_WaitExternalParseEnd && WaitExternalParserTimeoutStamp < GetTickCount())
		{
//			remove("KingdomScanStatus.txt");
			ScanStatus = SS_StartNewScanCycle;
		}
	}
	KeepGameScanThreadsRunning = 0;
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
            printf("Found a fetch data client packet\n");
            PrintDataHexFormat(PacketBytes, ByteCount - 2, 0, ByteCount - 2);
            ScanWorldPacketsLoaded = (unsigned char*)realloc(ScanWorldPacketsLoaded, (ScanWorldPacketsLoadedCount + 1) * SCAN_WORLD_PACKET_NO_SIZE_SIZE + 10);
            memcpy(&ScanWorldPacketsLoaded[ScanWorldPacketsLoadedCount * SCAN_WORLD_PACKET_NO_SIZE_SIZE], PacketBytes, SCAN_WORLD_PACKET_NO_SIZE_SIZE);
            ScanWorldPacketsLoadedCount++;
        }
        BytesRead = fread(&ByteCount, 1, 2, f);
    }
    fclose(f);
}

void	LordsMobileControlStartup()
{
	//1 processing thread is enough
	if (ScanGameProcessThreadHandle != 0)
		return;

    LoadScanPacketsFromFile();

	//create the processing thread 
	DWORD   PacketProcessThreadId;
	ScanGameProcessThreadHandle = CreateThread(
		NULL,							// default security attributes
		0,								// use default stack size  
		BackgroundProcessScanGame,		// thread function name
		&ThreadParamScanGameData,		// argument to thread function 
		0,								// use default creation flags 
		&PacketProcessThreadId);		// returns the thread identifier 

	printf("Done creating background thread to scan ingame\n");
}

void	LordsMobileControlShutdown()
{
	if (ScanGameProcessThreadHandle == 0)
		return;

	//signal that we want to break the processing loop
	KeepGameScanThreadsRunning = 2;
	//wait for the processing thread to finish
	while (KeepGameScanThreadsRunning != 0)
		Sleep(10);
	//close the thread properly
	CloseHandle(ScanGameProcessThreadHandle);
	ScanGameProcessThreadHandle = 0;
}