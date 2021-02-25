#include <stdlib.h>
#include <string.h>
#include "PacketAssembler.h"

PacketAssembler::PacketAssembler()
{
	Buffers = NULL;
	BuffersLen = 0;
	WaitForCleanStart = 1;
}

int PacketAssembler::AddBuffer(char *packet, int len)
{
	//if we cut into an already ongoing communication, wait for a full packet before starting to parse
	if (WaitForCleanStart == 1 && *(unsigned short *)packet != len)
		return 1;
	WaitForCleanStart = 0;

	char *tbuf = NULL;
	int tlen = 0;
	//do we have a remainder of packet from previous sessions ? Merge it with this one
	if (BuffersLen != 0)
	{
		tlen = BuffersLen + len;
		tbuf = (char*)malloc(tlen + 10);
		memcpy(&tbuf[0], Buffers, BuffersLen);
		memcpy(&tbuf[BuffersLen], packet, len);
		free(Buffers);
		Buffers = NULL;
		BuffersLen = 0;
	}
	else
	{
		tlen = len;
		tbuf = packet;
	}
	//try to parse as many packets as possible
	int BytesParsed = 0;
	while (BytesParsed < tlen)
	{
		//is this the beggining of a packet
		unsigned short SubPacketLen = *(unsigned short *)&tbuf[BytesParsed];
		if (BytesParsed + SubPacketLen <= tlen)
			StorePacket(&tbuf[BytesParsed], SubPacketLen);
		else
			break; // partial packet detected, wait for more network bytes to complete it
		BytesParsed += SubPacketLen;
	}
	//are there leftover bytes ? Keep these bytes until we receive more bytes from network
	if (BytesParsed < tlen)
	{
		BuffersLen = tlen - BytesParsed;
		Buffers = (char*)malloc(BuffersLen+10);
		memcpy(Buffers, &tbuf[BytesParsed], BuffersLen);
	}
	if (tlen != len)
		free(tbuf);
	return 0;
}

void PacketAssembler::StorePacket(char *buf, int len)
{
	BufSizeStore *bs = new BufSizeStore();
	bs->buf = (char*)malloc(len);
	bs->size = len;
	memcpy(bs->buf, buf, len);
	Packets.push_back(bs);
}

char *PacketAssembler::FetchPacket(int &len)
{
	if (Packets.empty() == true)
	{
		len = 0;
		return NULL;
	}
	//pop oldest node
	BufSizeStore *bs = *(Packets.begin());
	Packets.pop_front();
	len = bs->size;
	char *ret = bs->buf;
	delete bs;
	return ret;
}