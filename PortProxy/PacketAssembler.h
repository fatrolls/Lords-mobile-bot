#pragma once

#include <list>

class PacketAssembler
{
public:
	struct BufSizeStore
	{
		char *buf;
		int size;
	};
	PacketAssembler();
	//buffer we just read from the network
	int AddBuffer(char *buf, int len);
	//fetch an assembled packet
	char *FetchPacket(int &len);
	bool empty() { return Packets.empty(); }
private:
	//store an assembled packet to be fetched later
	void StorePacket(char *buf, int len);
	char *Buffers;
	int BuffersLen;
	int WaitForCleanStart;
	std::list<BufSizeStore*> Packets;
};