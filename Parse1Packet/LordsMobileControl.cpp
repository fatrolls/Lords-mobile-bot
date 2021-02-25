#include <time.h>
#include "LordsMobileControl.h"
#include "HTTPSendData.h"

/*
#ifdef WIN32
    #pragma comment(lib,"ImageSearch/ImageSearchDLL_x86.lib")
#else
    #pragma comment(lib,"ImageSearch/ImageSearchDLL_x64.lib")
#endif
 */

#if 0
extern void RunLordsMobileTestsNoOCR();
extern void ToggleFastScan(int YStepFast, int YStepSlow);
extern void ScanKingdomArea2(int Kingdom, int StartX, int StartY, int EndX, int EndY);

#include <windows.h>
#include <stdio.h>

int		KeepGameScanThreadsRunning = 1;
int		ThreadParamScanGameData = 0;
HANDLE	ScanGameProcessThreadHandle = 0;
int		InverseScanOrder = (time(NULL) / (60*24)) % 2;

void InvertGameScanDirection()
{
	InverseScanOrder = 1 - InverseScanOrder;
	printf("New scan direction is : %d\n", InverseScanOrder);
}

DWORD WINAPI BackgroundProcessScanGame(LPVOID lpParam)
{
	int LoopCounter = 0;
	ToggleFastScan(13, 9);
	ToggleFastScan(13, 9);
	while (KeepGameScanThreadsRunning == 1)
	{
//		RunLordsMobileTestsNoOCR();	// we do not have a break mechanism for this atm
		if (InverseScanOrder == 0)
			ScanKingdomArea2(69, LoopCounter % 5, LoopCounter % 5, 510, 1020);
		else
			ScanKingdomArea2(69, LoopCounter % 5, 1020, 510, LoopCounter % 5);
		remove("KingdomScanStatus.txt");
		HTTP_GenerateMaps();
		ToggleFastScan( 13, 9 );
	}
	KeepGameScanThreadsRunning = 0;
	return 0;
}

void	LordsMobileControlStartup()
{
	//1 processing thread is enough
	if (ScanGameProcessThreadHandle != 0)
		return;

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
#endif