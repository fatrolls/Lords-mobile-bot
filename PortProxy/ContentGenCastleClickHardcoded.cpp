#include "PacketContentGenerator.h"

unsigned int Validxy[500];
unsigned int ValidxyCount = 0;
unsigned int ValidxySent = 0;

#ifdef USE_FIXED_COORDINATES_TO_SCAN

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
void OnCastleCreatePacketReceived(int x, int y, int flags)
{
}
void InitHardcodedGen()
{
	Validxy[ValidxyCount++] = 0;	Validxy[ValidxyCount++] = 2;
	Validxy[ValidxyCount++] = 1;	Validxy[ValidxyCount++] = 3;
	Validxy[ValidxyCount++] = 2;	Validxy[ValidxyCount++] = 0;
	Validxy[ValidxyCount++] = 2;	Validxy[ValidxyCount++] = 2;
	Validxy[ValidxyCount++] = 3;	Validxy[ValidxyCount++] = 5;
	Validxy[ValidxyCount++] = 5;	Validxy[ValidxyCount++] = 1;
}
#else
void InitHardcodedGen()
{
}
#endif