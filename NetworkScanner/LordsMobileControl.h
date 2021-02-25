#pragma once

void LordsMobileControlStartup();
void LordsMobileControlShutdown();
void InvertGameScanDirection();
void OnMapLocationUpdate(int x, int y);
//try to capture a jump packet
void OnLordsClientPacketReceived(const unsigned char *pkt_data, int len, const unsigned char *GameData, int GameLen);
void OnCastlePopupPacketReceived(int x, int y);