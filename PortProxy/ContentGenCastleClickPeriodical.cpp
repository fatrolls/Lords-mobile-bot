#include <Windows.h>
#include "PacketContentGenerator.h"
#include "ParseServerToClient.h"

#ifdef USE_PERIODIC_COORDINATES_TO_SCAN
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

void OnCastleCreatePacketReceived(int x, int y, int flags)
{

}
#endif
void InitPeriodicGen()
{

}