#pragma once

void InitContentGenerator();
int GeteneratePosToScan(int &x, int &y);
int GenerateAreaToScan(unsigned char **PacketContent);
void OnCastlePopupPacketReceived(int x, int y);