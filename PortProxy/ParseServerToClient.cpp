#include <stdio.h>
#include <string>
#include <Windows.h>
#include <time.h>
#include "ParseServerToClient.h"
#include "PacketContentGenerator.h"
#include "Tools.h"

int GetXYFromGUID(unsigned int GUID, int &x, int &y)
{
	unsigned char *GuidBytes = (unsigned char *)&GUID;
	unsigned int y4bits[4];
	y4bits[0] = (((unsigned int)GuidBytes[3] & 0xF0) >> 4);
	y4bits[1] = (((unsigned int)GuidBytes[1] & 0xF0) >> 4);
	y4bits[2] = (((unsigned int)GuidBytes[2] & 0x0F));
	y4bits[3] = 0; // ?
	unsigned int x4bits[4];
	x4bits[0] = (((unsigned int)GuidBytes[3] & 0x0F));
	x4bits[1] = (((unsigned int)GuidBytes[1] & 0x0F));
	x4bits[2] = 0;//?;
	x4bits[3] = 0;//?;
	y = (y4bits[0]) | (y4bits[1] << 4) | (y4bits[2] << 8);
	x = (y & 1) + 2 * ((x4bits[0]) | (x4bits[1] << 4));

	{
		IngameGUIDStruct *tguid = (IngameGUIDStruct *)&GUID;
		int ty = tguid->y0 | (tguid->y1 << 4) | (tguid->y2 << 8);
		int tx = (ty & 1) | 2 * (tguid->x0 | (tguid->x1 << 4)); // every second row, x is impair
		if (tx != x || ty != y)
			printf("Debug generating this guid with new way\n");
	}

	//sanity checks
	if (x > 512 || y > 1024)
		return 1;

	return 0;
}

unsigned int GenerateIngameGUID(int x, int y)
{
	if (x > 511 || y > 1024)
		return 0;

	//this will make the 512 with stretch to 1024 automatically so that the server thinks we are feeding him correct coordinates
//	if (y & 2 == 1)
		x = ( x << 1 ) | ( y & 1);

	IngameGUIDStruct guid;
	memset(&guid, 0, sizeof(guid));
	guid.y0 = (y >> 0) & 0x0F; // 4 bits always
	guid.y1 = (y >> 4) & 0x0F; // 4 bits always
	guid.y2 = (y >> 8) & 0x0F; // 4 bits always
	//skip 1 bit since it's already inside the Y
	guid.x0 = (x >> 1) & 0x0F; // 4 bits always
	guid.x1 = (x >> 5) & 0x0F; // 4 bits always

	int tempx, tempy;
	GetXYFromGUID(*(unsigned int*)&guid, tempx, tempy);
	if (tempx != x || tempy != y)
	{
		printf("Debug generating this guid with new way\n");
		return 0;
	}
	return *(unsigned int*)&guid;
}


//76 6F 76 61 6E 20 62 69 6C 00 00 00 00 32 55 41 43 00
int SearchNextName(unsigned char *packet, int size, int Start, int &StringType)
{
	//search for realm first
	int Ind = Start - 1;
	while (Ind < size)
	{
		Ind++;
		GenericMapObject *PD = (GenericMapObject *)&packet[Ind];

		//we are not searching for new object types atm
		if (PD->ObjectType > OBJECT_TYPE_MAX_KNOWN)
			continue;

		if (PD->B.Realm > MAX_KNOWN_REALM_ID)
			continue;

		if (PD->ObjectType != OBJECT_TYPE_MAYBE_ARMY && PD->ObjectType != OBJECT_TYPE_MONSTER && (PD->B.CastleLevel > MAX_KNOWN_CASTLE_LEVEL || PD->B.CastleLevel == 0))
			continue;

		// this happens when server responds to object list query
		// could be a realm number
		if (PD->B.Name[0] != 0)
			//		if (packet[Ind + 16] == 0x043 && packet[Ind + 17] == 0x00)	// no player name, could be maybe a monster without a name ? Based on object type ?
		{
			int x, y;
			if (PD->ObjectType == OBJECT_TYPE_MAYBE_ARMY || GetXYFromGUID(PD->GUID, x, y) == 0)
			{
				if (OneStringOnSize((unsigned char*)PD->B.Name, sizeof(PD->B.Name) + 1, 0, sizeof(PD->B.Name)))
				{
					if (OneStringOnSize((unsigned char*)PD->B.Guild, sizeof(PD->B.Guild) + 1, 0, sizeof(PD->B.Guild)))
					{
						StringType = 1;
						return Ind;
					}
				}
			}
		}
		// empty resource fields have no realm !
		// 00 14 03 19 01 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 02
		if (PD->ObjectType >= OBJECT_TYPE_RESOURCE_FOOD && PD->ObjectType <= OBJECT_TYPE_GEM_RESOURCE)
		{
			//resource node level
			if (PD->B.CastleLevel <= 5 && PD->B.MEx.MinedPercent == 0 && PD->B.MEx.SomeTimestamp == 0)
			{
				int x, y;
				if (GetXYFromGUID(PD->GUID, x, y) == 0
					&& IsAllZero(PD->B.Name, sizeof(PD->B.Name), 0, sizeof(PD->B.Name))
					&& IsAllZero(PD->B.Guild, sizeof(PD->B.Guild), 0, sizeof(PD->B.Guild))
					)
				{
					//most probably this is an empty resource node
					StringType = 1;
					return Ind;
				}
			}
		}
		//object type 10 is just strange. Can be found around dark nests. Does not have a valid name. Just some values
		if (PD->ObjectType == OBJECT_TYPE_MONSTER)
		{
			if ((PD->M.HealthPCT < 105.0f || PD->M.HealthPCT > 0.0f) && PD->M.Level <= 5 && PD->M.Level > 0 && PD->M.Time < 0x01000000)
				//			if (PD->B.Realm == 0 && PD->B.CastleLevel == 0 && PD->B.MEx.MinedPercent == 0 && PD->B.MEx.ResourceMax == 0 && PD->B.MEx.SomeTimestamp == 0)
			{
				StringType = 1;
				return Ind;
			}
		}

		/*		// maybe this is a guild name
		//48 65 6E 74 61 69 20 67 61 20 64 61 69 73 75 6B 69 00 00 00
		//42 6F 72 6E 20 74 6F 20 62 65 20 57 69 6C 64 00 00 00 00 00
		if (OneStringOnSize(packet, size, Ind, 20))
		{
		StringType = 2;
		return Ind;
		}*/
	}
	return 0;
}

/*
Got client click packet :
0B 00 9A 08 0B 00 00 00 72 00 5A
AC 08 0C 2F 7F 00 00 00 00 00 00 2F 00 72 00 5A 6D 69 73 66 69 74 6D 61 79 68 65 6D 37 31 31 00 00 00 00 00 02 01 01 00 00 00 00 00 F5 D1 00 00 00 00 00 00 21 00 00 00 00 00 00 00

- no shield
AC 08 0C 0E 30 00 00 00 00 00 00 2F 00 00 00 20 4D 61 6C 61 6E 67 20 76 61 6E 20 6A 61 76 61 00 00 00 00 00 07 05 01 00 00 00 00 00 D1 36 04 00 00 00 00 00 15 09 00 00 00 00 00 00
- shield
AC 08 0C 0E 30 00 00 00 00 00 00 2F 00 00 00 21 4C 68 52 20 41 63 41 64 65 6D 69 41 00 00 00 00 00 00 00 00 02 01 01 00 00 00 00 00 8F 37 00 00 00 00 00 00 00 00 00 00 00 00 00 00
*/
int ParsePacketCastlePopup(unsigned char *packet, int size)
{
	//print info about it
	CastlePopupInfo *CD = (CastlePopupInfo *)&packet[3];
	int x, y;
	if (GetXYFromGUID(CD->GUID, x, y) != 0)
		return PPHT_DID_NOT_TOUCH_IT;

	OnCastlePopupPacketReceived(x, y);
	//let's do some basic checkings if we are guessing this packet correctly
	//	if (OneStringOnSize(CD->GuildFullName, sizeof(CD->GuildFullName), 0, sizeof(CD->GuildFullName)) == 0)
	//		return;

#ifdef _DEBUG
	//maybe later we want to re-analize it
	PrintDataHexFormat(packet, size, 0, size);
	//humanly readable format
	printf("Parsing castle popup packet\n");
	//	printf("GUID : %08X == %02X %02X %02X %02X\n", CD->GUID, CD->GUID >> 0 & 255, CD->GUID >> 8 & 255, CD->GUID >> 16 & 255, CD->GUID >> 24 & 255);
	printf("x, y = %d %d\n", x, y);
	PrintFixedLenString((char*)"guild long name : ", CD->GuildFullName, sizeof(CD->GuildFullName), 1);
	printf("VIP : %u\n", (unsigned int)CD->VIPLevel);
	printf("GuildR : %u\n", (unsigned int)CD->GuildRank);
	printf("Might : %u\n", (unsigned int)CD->Might);
	printf("Kills : %u\n", (unsigned int)CD->Kills);
	//	if (MapCastlePackets.find(CD->GUID) == MapCastlePackets.end())
	//		printf("Could not find constructor packet!\n"); // does not seem to matter
	printf("\n");
#endif

	//store it for later
//	CastlePopupInfo *CD2 = (CastlePopupInfo *)malloc(sizeof(CastlePopupInfo));
//	memcpy(CD2, CD, sizeof(CastlePopupInfo));
//	ClickCastlePackets[GenMyGUID(x, y)] = CD2;

	//send it over HTML
/*
	std::map<int, GenericMapObject*>::iterator fc = MapCastlePackets.find(GenMyGUID(x, y));
	if (fc != MapCastlePackets.end())
	{
		GenericMapObject *p1 = fc->second;
		CastlePopupInfo *p2 = CD2;
		if (SkipInsertOnlyDebug == 0)
		{
			if (p1->ObjectType == OBJECT_TYPE_PLAYER)
				QueueObjectToProcess(p1->ObjectType, p1->B.Realm, x, y, p1->B.Name, p1->B.Guild, p2->GuildFullName, p1->B.CastleLevel, p2->Kills, p2->VIPLevel, p2->GuildRank, p2->Might, p1->B.PEx.StatusFlags, 0, p1->B.PEx.Title, p1->M.Type, p1->B.MEx.ResourceMax);
		}
	}
	else if (CD2->Kills > 0 && CD2->Might > 0) //can be resource click or monster click also
		printf("Investigate why there is no create packet for castle at %d %d - %s\n", x, y, CD2->GuildFullName);
	*/
	return PPHT_DID_NOT_TOUCH_IT;
}


int BadPlayerPacketsFound = 0;
int UnknownRealm = -1;
void ParsePacketQueryTileObjectReply(unsigned char *packet, int size)
{
	int StructsFound = 0;
	int NameStart = 0;
	int PrevNameStart = 0;
	int PrevPrevNameStart = 0;
	int StringType;
	while (NameStart = SearchNextName(packet, size, NameStart, StringType))
	{
		//PrintDataMultipleFormats(packet, size, PrevNameStart, NameStart);
		int NameEnd = NameStart;
		//dump cur name
		if (StringType == 1)
		{
			NameEnd = NameStart + sizeof(BuildingObjectDesciption);

#ifdef _DEBUG
//			PrintDataHexFormat(packet, size, PrevNameStart, NameStart);
//			PrintDataHexFormat(packet, size, NameStart, NameEnd);
#endif

			GenericMapObject *PD = (GenericMapObject *)&packet[NameStart];
			int x, y;
			if (GetXYFromGUID(PD->GUID, x, y) == 0 && PD->B.CastleLevel <= 25)
			{
#ifdef _DEBUG
				StructsFound++;
				printf("%d)x, y = %d %d\n", StructsFound, x, y);
				printf("Type:%d\n", PD->ObjectType);
				if (PD->ObjectType == OBJECT_TYPE_PLAYER)
				{
					OnCastleCreatePacketReceived(x, y, PD->B.PEx.StatusFlags);
					PrintFixedLenString("name : [", PD->B.Guild, sizeof(PD->B.Guild), 0);
					PrintFixedLenString("]", PD->B.Name, sizeof(PD->B.Name), 1);
					printf("building Level:%d\n", PD->B.CastleLevel);
					printf("statusFlags:%02X\n", PD->B.PEx.StatusFlags);
					printf("Title:%d\n", PD->B.PEx.Title);
					printf("Guild Realm:%d\n", PD->B.PEx.RealmGuild);
					printf("Extended ID:%d\n", PD->B.PEx.ExtendedTypeId);
					//					if (PD->B.PEx.ExtendedTypeId != 0 && PD->B.PEx.ExtendedTypeId != 513)
					//						printf("unk2 is not 0\n");
				}
#endif
				//store it for later
/*				if (PD->ObjectType == OBJECT_TYPE_PLAYER)
				{
					GenericMapObject *CD2 = (GenericMapObject *)malloc(sizeof(GenericMapObject));
					memcpy(CD2, PD, sizeof(GenericMapObject));
					MapCastlePackets[GenMyGUID(x, y)] = CD2;

					if (UnknownRealm == -1)
						UnknownRealm = CD2->B.Realm;

					//send it over HTML
					GenericMapObject *p1 = CD2;
					if (SkipInsertOnlyDebug == 0)
						QueueObjectToProcess(p1->ObjectType, p1->B.Realm, x, y, p1->B.Name, p1->B.Guild, NULL, p1->B.CastleLevel, 0, 0, 0, 0, p1->B.PEx.StatusFlags, 0, p1->B.PEx.Title, 0, p1->B.MEx.ResourceMax);
				}
				if (PD->ObjectType >= OBJECT_TYPE_RESOURCE_FOOD && PD->ObjectType <= OBJECT_TYPE_GEM_RESOURCE && UnknownRealm != -1)
				{
					GenericMapObject *CD2 = (GenericMapObject *)malloc(sizeof(GenericMapObject));
					memcpy(CD2, PD, sizeof(GenericMapObject));
					MapCastlePackets[GenMyGUID(x, y)] = CD2;

					//send it over HTML
					GenericMapObject *p1 = CD2;
					if (SkipInsertOnlyDebug == 0)
						QueueObjectToProcess(p1->ObjectType, UnknownRealm, x, y, p1->B.Name, p1->B.Guild, NULL, p1->B.CastleLevel, 0, 0, 0, 0, 0, 0, 0, 0, p1->B.MEx.ResourceMax);
				}
				if (PD->ObjectType == OBJECT_TYPE_MONSTER && UnknownRealm != -1)
				{
					GenericMapObject *CD2 = (GenericMapObject *)malloc(sizeof(GenericMapObject));
					memcpy(CD2, PD, sizeof(GenericMapObject));
					MapCastlePackets[GenMyGUID(x, y)] = CD2;

					//send it over HTML
					GenericMapObject *p1 = CD2;
					if (SkipInsertOnlyDebug == 0)
						QueueObjectToProcess(p1->ObjectType, UnknownRealm, x, y, NULL, NULL, NULL, p1->M.Level, 0, 0, 0, 0, 0, 0, 0, p1->M.Type, 0);
				}*/
			}
			else if (PD->ObjectType == OBJECT_TYPE_MAYBE_ARMY)
			{
				//army has extra 6 bytes
				NameEnd += 6;
			}
			else //if (PD->ObjectType != OBJECT_TYPE_MAYBE_ARMY)
			{
				printf("%d)Incorrect player data found above. Parse it manually : %s t=%d x=%d y=%d c=%d\n", BadPlayerPacketsFound++, PD->B.Name, PD->ObjectType, x, y, PD->B.CastleLevel);
			}
		}
		//remember ...
		if (NameEnd != NameStart)
		{
			PrevPrevNameStart = PrevNameStart;
			PrevNameStart = NameEnd;
		}
		if (NameEnd == NameStart)
			NameStart++;
		else
			NameStart = NameEnd;
	}
#ifdef _DEBUG
	if (StructsFound == 0)
		printf("Query list returned 0 objects !\n");
	if (PrevNameStart < size)
	{
		printf("Remaining unconsumed bytes : \n");
		//	PrintDataMultipleFormats(packet, size, PrevNameStart, size);
		PrintDataHexFormat(packet, size, PrevNameStart, size);
	}
	printf("\n\n");
#endif
}

int ProcessPacket1(unsigned char *packet, int size)
{
	// some invalid id packet ?
	if (size <= 17)
		return PPHT_DID_NOT_TOUCH_IT;

	// castle popup packets
	if (packet[0] == 0xAC && packet[1] == 0x08 && packet[2] == 0x0C)
	{
		ParsePacketCastlePopup(packet, size);
		return PPHT_DID_NOT_TOUCH_IT;
	}

	// visible object query rely. Castles, mines ... 
	if (packet[0] == 0xAC && packet[1] == 0x08 && (packet[2] == 0x02 || packet[2] == 0x03 || packet[2] == 0x0F || packet[2] == 0x0D || packet[2] == 0x0E || packet[2] == 0x09 || packet[2] == 0x18 || packet[2] == 0x17 || packet[2] == 0x16))
	{
		ParsePacketQueryTileObjectReply(packet, size);
		return PPHT_DID_NOT_TOUCH_IT;
	}/**/
#if 0

#ifdef _DEBUG
	printf("we are skipping this packet : ");
	//	PrintDataHexFormat(packet, size, 0, size);
//	PrintDataHexFormat(packet, size, 0, min(size, 10));
#endif

	if (packet[0] == 0x26 && packet[1] == 0x0B)
	{
		//		ProcessSomePlayerNameRelated(packet, size);
		return;
	}

	if (packet[0] == 0x12 && packet[1] == 0x0E)
	{
		//12 0E 00 70 98 BC 58 00 00 00 00 04 29 00 00 00 10 00 20 00 00 00 00 00 00 00 00 00 01 00 00 00 00 00 00 00 
		//12 0E 01 70 98 BC 58 00 00 00 00 E4 0C 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 
		return;
	}

	if (packet[0] == 0xAC && packet[1] == 0x08 && packet[2] == 0x01)
	{
		//AC 08 01 80 20 01 00 00 00 00 00 2B 00 0B 01 DD 02 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 03 80 FC 0A 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00
		//AC 08 01 90 1B 01 00 00 00 00 00 2B 00 1B 01 9F 03 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 04 68 6B 0E 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 
		return;
	}

	if (packet[0] == 0xAC && packet[1] == 0x08 && packet[2] == 0x12)
	{
		//AC 08 12 FE 77 01 00 00 00 00 00 44 01 AE 28 0B 00 36 91 BC 58 00 00 00 00 4B 07 00 00 6F 0B 00 00 
		//AC 08 12 FF 77 01 00 00 00 00 00 44 01 AE 28 0B 00 1C 8E BC 58 00 00 00 00 09 00 00 00 8E 0E 00 00 
		return;
	}

	if (packet[0] == 0xAC && packet[1] == 0x08 && packet[2] == 0x05)
	{
		//AC 08 05 C6 35 00 00 00 00 00 00 68 00 38 52 42 3A 
		//AC 08 05 73 2F 00 00 00 00 00 00 58 00 DC 52 42 3A 05 73 2F 00 00 00 00 00 00 58 00 C7 52 42 3A 07 73 2F 00 00 00 00 00 00 58 00 18 52 42 3A 18 68 00 
		return;
	}

	if (packet[0] == 0xAC && packet[1] == 0x08 && packet[2] == 0x2A)
	{
		//AC 08 2A 9B D6 00 00 00 00 00 00 F9 00 87 00 00 07 9C D6 00 00 00 00 00 00 F9 00 87 00 00 00  
		return;
	}

	if (packet[0] == 0xAC && packet[1] == 0x08 && packet[2] == 0x11)
	{
		//AC 08 11 DA 88 01 00 00 00 00 00 94 01 3F E4 0B 00 53 51 4D 
		return;
	}

	if (packet[0] == 0x5F && packet[1] == 0x1B)
	{
		//5F 1B 43 00 54 4F 31 43 68 69 70 73 69 6E 64 69 70 00 00 00 80 76 7D 00 B8 F0 0D 00 01 EE AA BC 58 00 00 00 00 
		return;
	}

	if (packet[0] == 0x9E && packet[1] == 0x18)
	{
		//9E 18 CB C0 BC 58 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 00 
		return;
	}
	// chat packets
	if (packet[0] == 0xBB && packet[1] == 0x0B)
	{
		//BB 0B 00 00 01 00 99 95 BC 58 00 00 00 00 00 00 00 00 00 00 00 00 BD 08 00 00 00 00 00 00 01 65 00 00 4D 61 6E 74 69 63 30 72 33 00 00 00 00 01 00 00 00 00 00 00 00 00 
		//BB 0B 00 00 01 00 92 95 BC 58 00 00 00 00 00 00 00 00 00 00 00 00 BC 08 00 00 00 00 00 00 01 65 00 00 41 6E 6E 75 6E 61 6B 69 20 31 31 00 00 01 00 00 00 00 00 00 00 00 
		return;
	}

	if (packet[0] == 0x28 && packet[1] == 0x0C)
	{
		//28 0C 00 01 CB C0 BC 58 00 00 00 00 00 00 00 00 00 00 00 00 07 0B 00 00 00 00 00 00 00 00 06 00 00 00 05 00 A8 0F 00 13 00 00 00 03 00 00 00 00 05 00 00 00 06 00 00 00 00 0F 00 00 00 09 00 00 00 00 1F 00 01 00 02 00 A2 0F 00 07 00 00 00 04 00 00 00 00 
		//28 0C 00 02 CB C0 BC 58 00 00 00 00 00 00 00 00 00 00 00 00 06 1F 00 01 00 0E 00 00 00 00 0F 00 00 00 06 00 A8 0F 00 1F 00 01 00 00 00 00 00 00 1A 00 01 00 10 00 A6 0F 00 3C 00 03 00 06 00 00 00 00 0C 00 00 00 07 00 00 00 00
		return;
	}
#ifdef _DEBUG
	printf("Unk packet : \n");
	//	PrintDataMultipleFormats(packet, size, PrevNameStart, size);
	PrintDataHexFormat(packet, size, 0, size);
	printf("\n\n");
#endif
#endif
	return PPHT_DID_NOT_TOUCH_IT;
}

int OnServerToClientPacket(unsigned char *packet, unsigned int len)
{
	int ret = PPHT_DID_NOT_TOUCH_IT;
	unsigned int BytesParsed = 0;
	while (BytesParsed < len)
	{
		unsigned short SubPacketLen = *(unsigned short *)&packet[BytesParsed];
		if (BytesParsed + SubPacketLen <= len)
		{
			int status = ProcessPacket1(&packet[BytesParsed + 2], SubPacketLen - 2);
			if (status > ret)
				ret = status;
		}
		BytesParsed += SubPacketLen;
	}
	return ret;
}
/*
unsigned char *PacketCircularBuffer[MAX_PACKET_CIRCULAR_BUFFER];
int PacketCircularBufferReadIndex = 0;
int PacketCircularBufferWriteIndex = 0;
int	KeepThreadsRunning = 1;

void QueuePacketToProcess(unsigned char *data, int size)
{
	if (size <= 0)
		return;
	unsigned char *t = (unsigned char*)malloc(size + 2 + 2);
	*(unsigned short *)t = size;
	memcpy(t + 2, data, size);
	PacketCircularBuffer[PacketCircularBufferWriteIndex] = t;
	PacketCircularBufferWriteIndex = (PacketCircularBufferWriteIndex + 1) % MAX_PACKET_CIRCULAR_BUFFER;
}

DWORD WINAPI BackgroundProcessPackets(LPVOID lpParam)
{
	while (KeepThreadsRunning == 1)
	{
		//can we pop a packet from the queue ?
		if (PacketCircularBufferReadIndex != PacketCircularBufferWriteIndex)
		{
			//pop one buffer from the circular queue to reduce the chance of a thread collision
			int PopIndex = PacketCircularBufferReadIndex;
			unsigned char *PopBuffer = PacketCircularBuffer[PopIndex];
			PacketCircularBuffer[PopIndex] = NULL;
			PacketCircularBufferReadIndex = (PacketCircularBufferReadIndex + 1) % MAX_PACKET_CIRCULAR_BUFFER;
			//if this is a valid buffer than we try to process it
			if (PopBuffer != NULL)
			{
				//parse the packet and if it is a packet we want we will use a HTTP API to push it into our DB. The http API runs async
				printf("process packet : in queue %d\n", PacketCircularBufferWriteIndex - PopIndex);
				ProcessPacket1(&PopBuffer[2], *(unsigned short*)PopBuffer);
				//we no longer need this buffer
				free(PopBuffer);
			}
		}
		else
		{
			PacketCircularBufferReadIndex = PacketCircularBufferWriteIndex = 0;
			//avoid 100% CPU usage. There is no scientific value here
			Sleep(10);
		}
	}
	KeepThreadsRunning = 0;
	return 0;
}

int		pDataArray = 0;
HANDLE	PacketProcessThreadHandle = 0;
void	CreateBackgroundPacketProcessThread()
{
	//1 processing thread is enough
	if (PacketProcessThreadHandle != 0)
		return;

	//make our queue empty
	memset(PacketCircularBuffer, 0, sizeof(PacketCircularBuffer));

	//create the processing thread 
	DWORD   PacketProcessThreadId;
	PacketProcessThreadHandle = CreateThread(
		NULL,						// default security attributes
		0,							// use default stack size  
		BackgroundProcessPackets,   // thread function name
		&pDataArray,				// argument to thread function 
		0,							// use default creation flags 
		&PacketProcessThreadId);	// returns the thread identifier 

	printf("Done creating background thread to parse network packets\n");
}

void	StopThreadedPacketParser()
{
	if (PacketProcessThreadHandle == 0)
		return;

	//signal that we want to break the processing loop
	KeepThreadsRunning = 2;
	//wait for the processing thread to finish
	while (KeepThreadsRunning != 0)
		Sleep(10);
	//close the thread properly
	CloseHandle(PacketProcessThreadHandle);
	PacketProcessThreadHandle = 0;
}
*/