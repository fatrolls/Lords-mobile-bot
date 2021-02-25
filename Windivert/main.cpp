#include <stdio.h>
#include <Windows.h>
#include "Connections.h"
#include "PacketContentGenerator.h"
#include "StreamInfo.h"

int __cdecl main(int argc, char **argv)
{
	//generate scan location for every castle click packet we receive
	InitContentGenerator();
	//dump packets in raw format to console ?
	InitShowPacketInfo(0);
	InitConnections();
	char c = 'a';
	while (c != 'q')
		scanf_s("%c", &c, 1);
	ShutDownConnections();
}
