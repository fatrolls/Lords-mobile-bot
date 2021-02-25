#pragma once

int OneStringOnSize(unsigned char *packet, int size, int Ind, int StringSize);
int IsAllZero(void *packet, int size, int Ind, int count);
void PrintDataHexFormat(unsigned char *packet, int size, int From, int To);
void PrintFixedLenString(char *PreStr, char *str, int len, int WithNewLine);
void HexToByteStr(char *HexStr, unsigned char *ByteStr, int &size);