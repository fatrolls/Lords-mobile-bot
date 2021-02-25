#pragma once
#include "windivert.h"

void InitShowPacketInfo(int LogToConsole);
void ShowPacketInfo(WINDIVERT_ADDRESS addr, unsigned char *packet, unsigned int len);