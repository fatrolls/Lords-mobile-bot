#include <Windows.h>
#include "PacketContentGenerator.h"
#include "ParseServerToClient.h"

#ifdef USE_SCANNED_COORDINATES_TO_SCAN
struct ScanStatusOnCoord
{
	unsigned int StatusTimout; // can rescan this location once the timout expires
	unsigned int ObjectType;
	unsigned int ObjectFlags;
};

ScanStatusOnCoord GameMap[MaxGameY][MaxGameX];

int ContinueScanX = 0;
int ContinueScanY = 0;
int GeteneratePosToScan(int &px, int &py)
{
	//after a rescan without timeouts, we should just start scanning what we can
	unsigned int TickNow = GetTickCount();
	for (int y = ContinueScanY; y < MaxGameY; y++)
	{
		for (int x = ContinueScanX; x < MaxGameX; x++)
		{
			if (GameMap[y][x].StatusTimout < TickNow && GameMap[y][x].StatusTimout != 0)
			{
				px = x;
				py = y;
				ContinueScanY = y;
				ContinueScanX = x + 1;
				return 0; // no issues
			}
		}
		ContinueScanX = 0;
	}

	ContinueScanY = 0;
	ContinueScanX = 0;

	//do not allow the client to receive a reply from the server
	py = 0;
	px = 1;

	//nothing to update yet
	return 0;
}

//if we managed to click a castle, we want to monitor this location with bigger frequency
void OnCastlePopupPacketReceived(int x, int y)
{
	if (y <0 || y> MaxGameY)
		return;
	if (x <0 || x> MaxGameX)
		return;
//	GameMap[y][x].StatusTimout = GetTickCount() + TimoutScanCastleMS;
//	GameMap[y][x].ObjectType = OBJECT_TYPE_PLAYER;
}

void OnCastleCreatePacketReceived(int x, int y, int flags)
{
	if (y <0 || y> MaxGameY)
		return;
	if (x <0 || x> MaxGameX)
		return;
	unsigned int TickNow = GetTickCount();
	if(GameMap[y][x].StatusTimout == 0 || GameMap[y][x].StatusTimout > TickNow + TimoutScanCastleMS)
		GameMap[y][x].StatusTimout = TickNow + TimoutScanCastleMS;
	GameMap[y][x].ObjectType = OBJECT_TYPE_PLAYER;
	GameMap[y][x].ObjectFlags = flags;
}

#endif

void InitSpawnedGen()
{
	unsigned int TickNow = GetTickCount();
	for (int y = 0; y < MaxGameY; y++)
		for (int x = 0; x < MaxGameX; x++)
			GameMap[y][x].StatusTimout = 0; // only scan spawned locations
}