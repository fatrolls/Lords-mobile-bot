#pragma once

void PrepareAndSendPacket(unsigned char *Data, int Len, int PayloadSize);

extern unsigned int ClientBytesSent;
extern unsigned int ServerBytesSent;