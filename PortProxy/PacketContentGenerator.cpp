#include <Windows.h>
#include <stdio.h>
#include "PacketContentGenerator.h"
#include "ParseServerToClient.h"
#include "Tools.h"

//we want to click all locations at first go
void InitContentGenerator()
{
	//if we catch a scan packet, we will try to override it
	InitScanPeriodicGen();
	//try to open file to load mapscan packets
	InitHardcodedGen();
	InitPeriodicGen();
}
