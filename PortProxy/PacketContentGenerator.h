#pragma once

#define MaxGameX			1024
#define MaxGameY			512
#define TimoutScanMS		(10*60*100)
#define TimoutScanCastleMS	(2*60*100)

#define SCAN_WORLD_PACKET_NO_SIZE_SIZE 47

//#define USE_FIXED_COORDINATES_TO_SCAN
//#define USE_PERIODIC_COORDINATES_TO_SCAN
#define USE_SCANNED_COORDINATES_TO_SCAN

void InitContentGenerator();
int GeteneratePosToScan(int &x, int &y);
int GenerateAreaToScan(unsigned char **PacketContent);
void OnCastlePopupPacketReceived(int x, int y);
void OnCastleCreatePacketReceived(int x, int y, int flags);

void InitHardcodedGen(); // scan only a few hardcoded locations
void InitPeriodicGen(); // periodically scan all positions
void InitSpawnedGen(); // only scan castle positions spwaned
void InitScanPeriodicGen();

extern int ScanMapCountGen;
class PacketAssembler;
extern PacketAssembler InjectQueue;