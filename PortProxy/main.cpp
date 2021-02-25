#include <stdio.h>
#include <Windows.h>
#include "Connections.h"
#include "PacketContentGenerator.h"
#include "StreamInfo.h"
#include "ConsoleListener.h"

int __cdecl main(int argc, char **argv)
{
	//generate scan location for every castle click packet we receive
	InitContentGenerator();
	//dump packets in raw format to console ?
	InitShowPacketInfo(0);
	InitConnections();
	StartListenConsole();
/*	{
		char c[100];
		c[0] = 0;
		while (c[0] != 'q')
			scanf_s("%c", &c, 1);
	}/**/
	while (WorkerThreadAlive == 1)
		Sleep(1000);

	ShutDownConnections();
}
