#include <stdlib.h>
#include <string>
#include <conio.h>
#include <Windows.h>
#include "PacketContentGenerator.h"

#define DEFAULT_BUFLEN			1024 * 65
#define EMPTY_LINES_FOR_CLS		120

int WorkerThreadAlive = 1;

void HandleConsoleLine( char *Line )
{
	if( strncmp( Line, "exit\0", strlen( "exit\0" ) ) == 0 ) 
	{
		printf("Console Listener : Exiting console listener\n");
		WorkerThreadAlive = 0;
	}
	if (strncmp(Line, "scan\0", strlen("scan\0")) == 0)
	{
		printf("Console Listener : Force a scan\n");
		ScanMapCountGen = 1;
	}
}

DWORD WINAPI LoopListenConsole(LPVOID lpParam)
{
	char LineBuffer[ DEFAULT_BUFLEN ];
	int WriteIndex = 0;
	while( WorkerThreadAlive == 1 )
	{
		//this will not let us auto exit program until keypressed
		char c = _getch();
		if (c == '\r' || c == '\n')
		{
			LineBuffer[WriteIndex] = '\0';
			if (WriteIndex > 1)
			{
				printf("\n");
				HandleConsoleLine(LineBuffer);
			}
			WriteIndex = 0;
		}
		else if (WriteIndex < DEFAULT_BUFLEN - 1)
		{
			LineBuffer[WriteIndex] = c;
			WriteIndex++;
			printf("%c", c);
		}
	}
	return 0;
}

HANDLE	ScanConsoleThreadHandle = 0;
void StartListenConsole()
{
	WorkerThreadAlive = 1;
	if (ScanConsoleThreadHandle != 0)
		return;

	//create the processing thread 
	DWORD   ConsoleProcessThreadId;
	int		ThreadParam = 0;
	ScanConsoleThreadHandle = CreateThread(
		NULL,							// default security attributes
		0,								// use default stack size  
		LoopListenConsole,				// thread function name
		NULL,							// argument to thread function 
		0,								// use default creation flags 
		&ConsoleProcessThreadId);		// returns the thread identifier 
}

void StopListenConsole()
{
	WorkerThreadAlive = 0;
}
