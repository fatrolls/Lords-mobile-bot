#include <stdio.h>
#include <map>
#include <time.h>
#include "ParsePackets.h"
#include "HTTPSendData.h"
#include "Tools.h"

int SkipInsertOnlyDebug = 0;

std::map<int, GenericMapObject*>	MapCastlePackets;
std::map<int, CastlePopupInfo*>		ClickCastlePackets;

#define GenMyGUID(x,y) (((unsigned short)x << 16)| ((unsigned short)y))

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

	//sanity checks
	if (x > 512 || y > 1024)
		return 1;

	return 0;
}

//76 6F 76 61 6E 20 62 69 6C 00 00 00 00 32 55 41 43 00
int SearchNextName(unsigned char *packet, int size, int Start, int &StringType)
{
	//search for realm first
	int Ind = Start-1;
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
			if ((PD->M.HealthPCT < 105.0f || PD->M.HealthPCT > 0.0f) && PD->M.Level <= 5 && PD->M.Level > 0 && PD->M.Time < 0x01000000 )
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

void ParsePacketCastlePopup(unsigned char *packet, int size)
{
	//print info about it
	CastlePopupInfo *CD = (CastlePopupInfo *)&packet[3];
	int x, y;
	if (GetXYFromGUID(CD->GUID, x, y) != 0)
		return;

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
	PrintFixedLenString("guild long name : ", CD->GuildFullName, sizeof(CD->GuildFullName), 1);
	printf("VIP : %u\n", (unsigned int)CD->VIPLevel);
	printf("GuildR : %u\n", (unsigned int)CD->GuildRank);
	printf("Might : %u\n", (unsigned int)CD->Might);
	printf("Kills : %u\n", (unsigned int)CD->Kills);
	//	if (MapCastlePackets.find(CD->GUID) == MapCastlePackets.end())
	//		printf("Could not find constructor packet!\n"); // does not seem to matter
	printf("\n");
#endif

	//store it for later
	CastlePopupInfo *CD2 = (CastlePopupInfo *)malloc(sizeof(CastlePopupInfo));
	memcpy(CD2, CD, sizeof(CastlePopupInfo));
	ClickCastlePackets[GenMyGUID(x,y)] = CD2;

	//send it over HTML
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
	else if ( CD2->Kills > 0 && CD2->Might > 0 ) //can be resource click or monster click also
		printf("Investigate why there is no create packet for castle at %d %d - %s\n", x, y, CD2->GuildFullName);
}

void ParsePacketViewProfile(unsigned char *packet, int size)
{
	//44 00 56 04 00 07 00 4d 49 47 43 00 41 18 01 00 03 5a 12 17 01 00 00 00 00 a9 f6 3d 03 00 00 00 00 8a 62 03 00 3b 08 05 4f 11 05 68 12 01 65 12 03 3a 11 05 7e 12 01 94 12 03 66 12 02 7d 12 02 00 00 00 00
	/*
	44 00 				- size
	56 04
	00 07 00			- maybe guid ?
	4d 49 47 			- MIG
	43 00 				- realm
	41 18 01 00
	03 						- guild rank ?
	5a 12 17 01 00 00 00 00 - kills
	a9 f6 3d 03 00 00 00 00 - might
	8a 62 03 00
	3b 08 05
	4f 11 05 68 - item 2 ?
	12 01 65 12
	03 3a 11 05
	7e 12 01 94
	12 03 66 12
	02 7d 12 02
	00 00 00 00 - item 8
	*/
}

void ParsePacketViewProfile2(unsigned char *packet, int size)
{
	/*
	e9 00 - size
	54 04 00 - packet type ? ff
	ff 02 00 00 - victories
	8a 00 00 00 - failed attacks
	91 00 00 00 - good defense
	5a 00 00 00 - bad defense
	16 01 0c 01 00 00 00 00 - troops killed
	44 11 0b 00 00 00 00 00 - traps destroyed
	f9 b5 10 00 00 00 00 00 - troops lost
	44 87 02 00 00 00 00 00 - traps lost
	6b 14 58 00 - troops healed
	70 6d d9 00 - troops wounded
	33 00 00 00 - turfs lost
	cb 02 00 00 - turfs destroyed
	86 ec 9b 17 - enemy might destroyed
	ac 37 25 00 - 2439084 ?
	8d 00 00 00 - leaders captured
	00 00 00 00
	06 00 00 00 - leaders got captured
	00 00 00 00
	0e 00 00 00 - prisoners escaped
	00 00 00 00
	00 00 00 00
	f3 c8 0d 05 - food sent
	08 c9 de 02 - timber sent
	53 c5 bd 05 - stone sent
	65 fe fe 01 - ore sent
	d0 68 1b 01 - gold sent
	00 00 00 00
	15 49 00 00 - help sent
	c3 1b c5 6c 00 00 00 00 - rss gathered
	d0 37 00 00 - colloseum rank
	8a 00 00 00 - best rank
	c2 02 00 00 - battles won
	86 ec 9b 17 00 00 00 00  		396094598
	6b 14 58 00 00 00 00 00 - troops healed
	70 6d d9 00 00 00 00 00 - troops wounded
	00 00 00 00 00 00 00 00
	f3 c8 0d 05 00 00 00 00 - food sent
	08 c9 de 02 00 00 00 00 - timber sent
	53 c5 bd 05 00 00 00 00 - stone sent
	65 fe fe 01 00 00 00 00 - ore sent
	2c 03 37 00 00 00 00 00 		3605292
	d0 68 1b 01 00 00 00 00 - gold sent
	*/
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
			PrintDataHexFormat(packet, size, PrevNameStart, NameStart);
			PrintDataHexFormat(packet, size, NameStart, NameEnd);
#endif

			GenericMapObject *PD = (GenericMapObject *)&packet[NameStart];
			int x, y;
			if (GetXYFromGUID(PD->GUID, x, y) == 0 && PD->B.CastleLevel <= 25)
			{
#ifdef _DEBUG
				StructsFound++;
				printf("%d)x, y = %d %d\n", StructsFound, x, y);
				printf("Type:%d\n", PD->ObjectType);
				if (PD->ObjectType == OBJECT_TYPE_PLAYER || (PD->ObjectType >= OBJECT_TYPE_RESOURCE_FOOD && PD->ObjectType <= OBJECT_TYPE_GEM_RESOURCE))
				{
					PrintFixedLenString("name : [", PD->B.Guild, sizeof(PD->B.Guild), 0);
					PrintFixedLenString("]", PD->B.Name, sizeof(PD->B.Name), 1);
					printf("building Level:%d\n", PD->B.CastleLevel);
				}
				if (PD->ObjectType == OBJECT_TYPE_MONSTER)
				{
					printf("Type : %d\n", PD->M.Type);
					printf("Health : %f\n", PD->M.HealthPCT);
					printf("Time remain : %u\n", PD->M.Time);
					printf("Monster Level : %d\n", PD->M.Level);
				}
				//				printf("found it in players.txt:%d\n", SearchNameInFile(PD->B.Name));
				if (PD->ObjectType == OBJECT_TYPE_PLAYER)
				{
					printf("statusFlags:%02X\n", PD->B.PEx.StatusFlags);
					printf("Title:%d\n", PD->B.PEx.Title);
					printf("Guild Realm:%d\n", PD->B.PEx.RealmGuild);
					printf("Extended ID:%d\n", PD->B.PEx.ExtendedTypeId);
					//					if (PD->B.PEx.ExtendedTypeId != 0 && PD->B.PEx.ExtendedTypeId != 513)
					//						printf("unk2 is not 0\n");
				}
				else if (PD->ObjectType >= OBJECT_TYPE_RESOURCE_FOOD && PD->ObjectType <= OBJECT_TYPE_GEM_RESOURCE)
				{
					printf("ResourceMax:%d\n", PD->B.MEx.ResourceMax);
					printf("Mined percent:%.2f\n", PD->B.MEx.MinedPercent);
					printf("Timestamp:%d . Diff yesterday %lld minutes\n", PD->B.MEx.SomeTimestamp, (PD->B.MEx.SomeTimestamp - (time(NULL) - 24 * 60 * 60)) / 60);
					//					if (PD->B.PEx.ExtendedTypeId != 0 && PD->B.PEx.ExtendedTypeId != 513)
					//						printf("unk2 is not 0\n");
				}
#endif
				//store it for later
				if (PD->ObjectType == OBJECT_TYPE_PLAYER)
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
				}
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

//#define PARSE_CASSTLE_PACKETS
#define ONLY_GENERATE_HUNTING_GIFTS
#define PARSE_PLAYER_MIGHT_LIST
void ProcessPacket1(unsigned char *packet, int size)
{
#ifdef ONLY_GENERATE_HUNTING_GIFTS
/*	if (size == 0x16 && packet[0] == 0x2B && packet[1] == 0x0B && packet[2] == 0x12)
//	if (packet[0] == 0x2B && packet[1] == 0x0B && packet[2] == 0x12)
	{
#pragma pack(push, 1)
		struct GiftReceived22
		{
			unsigned char Opcode[3];
			unsigned short SomeCounter;
			unsigned char MonsterType;
			unsigned char Fixed0A; // OBJECT_TYPE_MONSTER
			char Name[13];
		};
#pragma pack(pop)
		GiftReceived22* pkt = (GiftReceived22*)packet;
		QueueObjectToProcess(OBJECT_TYPE_CUSTOM_MONSTER_GIFT, 0, 0, 0, pkt->Name, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, pkt->MonsterType, 0);
		pkt->Name[12] = 0;
		printf("\nCaught a gift packet, moster %d from %s\n", pkt->MonsterType, pkt->Name);
	}*/
#pragma pack(push, 1)
	struct GiftReceived49
	{
		unsigned char Opcode[3];
//		unsigned char SomeCounter;
		unsigned short  SomeCounter;
//		unsigned int  Time[2];
		unsigned short MonsterType;
//		unsigned char Unk2[5];
		char Name[13];
	};
#pragma pack(pop)
#if 0
	GiftReceived49* pkt = (GiftReceived49*)packet;
	if (pkt->Name[0] == 'T' && pkt->Name[1] == 'u' && pkt->Name[2] == 'd')
		pkt = pkt;
	int NamePos = -1;
	char* NameToFind = "Tudi69";
	int NameLen = (int)strlen(NameToFind);
	for (int i = 0; i < size; i++)
	{
		int Match = 1;
		for(int j=0;j< NameLen;j++)
			if (packet[i + j] != NameToFind[j])
			{
				Match = 0;
				break;
			}
		if (Match == 1)
			Match = 1;
	}
#endif
	if (
		//size == 0x2A && 
		packet[0] == 0x2B && packet[1] == 0x0B && packet[2] == 0x12)
		//	if (packet[0] == 0x2B && packet[1] == 0x0B && packet[2] == 0x12)
	{
		GiftReceived49* pkt = (GiftReceived49*)packet;
		pkt->Name[12] = 0;
/*		for (int i = 0; i < 12; i++)
		{
			int IsGood = 0;
			if (pkt->Name[i] >= 'a' && pkt->Name[i] <= 'z')
				IsGood = 1;
			else if (pkt->Name[i] >= 'A' && pkt->Name[i] <= 'Z')
				IsGood = 1;
			else if (pkt->Name[i] >= '0' && pkt->Name[i] <= '9')
				IsGood = 1;
			else if (pkt->Name[i] == 0)
				break;
			else
				pkt->Name[i] = '_';
		}*/
//		time_t TimeNow = time(NULL);
//		time_t TimePassed = TimeNow - pkt->Time[0];
//		if((pkt->Unk1 != 20 && pkt->Unk1 != 22) || pkt->Unk2[0] != 0 || pkt->Unk2[1] != 0 || pkt->Unk2[2] != 0 || pkt->Unk2[3] != 0 || pkt->Unk2[4] != 0)
//			pkt->Name[12] = 0;
//		if(pkt->Fixed0A == 0x0A) // gift source monster
			QueueObjectToProcess(OBJECT_TYPE_CUSTOM_MONSTER_GIFT, 0, 0, 0, pkt->Name, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, 0, pkt->MonsterType, 0);
		if(pkt->Name[0] != 0)
			printf("\rCaught a gift packet, moster %d from %s\n", pkt->MonsterType, pkt->Name);
	}
	if (packet[0] == 0x37 && packet[1] == 0x0B && packet[2] == 0x00)
	{
/*		enum GiftSourceType
		{
			GST_VIP = 7, 
			GST_SHOP = 9,
			GST_MONSTER = 10
		};*/
		enum GiftTypeGroup
		{
			GTG_RESOURCE = 3,
			GTG_SPEEDUP = 4,
			GTG_HERO_CHEST = 7,
			GTG_MATERIAL = 15
		};
#pragma pack(push, 1)
		struct GiftListEntry
		{
			int SortIndex; //does not increase 1 by 1
			unsigned char unk1_always1; // always 1
			unsigned int  Time[2];
			unsigned short MonsterType;
//			unsigned char GiftSourceType; //10(monster), 7(VIP gift)
			unsigned char GiftType;
			unsigned char GiftTypeGroup; // sometimes 3(rss),4(speedup, quest scroll),7(hero chest),15(mats)
			unsigned char GiftCount;
			unsigned char unk3_always0; // always 0 
			unsigned char MaterialQuality; // 0 (rss, speedup), 1(gray),2(green material), 3(blue mat)			char Name[13];
			char Name[13];
		};
		struct GiftListOpen
		{
			unsigned char Opcode[3];
			unsigned char Unk[9];
			unsigned char EntryCount;
			GiftListEntry Entries[1]; //there are EntryCount Entries here
		};
#pragma pack(pop)
		GiftListOpen* pkt = (GiftListOpen*)packet;
		time_t TimeNow = time(NULL);
		for (int i = 0; i < pkt->EntryCount; i++)
		{
			time_t TimePassed = TimeNow - pkt->Entries[i].Time[0];
			pkt->Entries[i].Name[12] = 0;
			unsigned int GUID = pkt->Entries[i].SortIndex;
			if (pkt->Entries[i].unk1_always1 != 1 
//				|| (pkt->Entries[i].GiftSourceType != GST_VIP && pkt->Entries[i].GiftSourceType != GST_SHOP && pkt->Entries[i].GiftSourceType != GST_MONSTER)
				|| pkt->Entries[i].unk3_always0 != 0 
				|| (pkt->Entries[i].GiftTypeGroup != GTG_RESOURCE && pkt->Entries[i].GiftTypeGroup != GTG_SPEEDUP && pkt->Entries[i].GiftTypeGroup != GTG_MATERIAL && pkt->Entries[i].GiftTypeGroup != GTG_HERO_CHEST))
			{
				printf("\rInvestigate gift case %d %d %d\n", pkt->Entries[i].unk1_always1, pkt->Entries[i].GiftTypeGroup, pkt->Entries[i].unk3_always0);
				for (int j = 0; j < sizeof(pkt->Unk); j++)
					printf("%02X ", pkt->Unk[j]);
				printf("\n");
			}
//			if (pkt->Entries[i].GiftSourceType == GST_MONSTER) //seems like this can be "anything"
				QueueObjectToProcess(OBJECT_TYPE_CUSTOM_MONSTER_GIFT_LIST, 0, GUID, pkt->Entries[i].GiftType, pkt->Entries[i].Name, NULL, NULL, pkt->Entries[i].GiftCount, (int)TimePassed, 0, 0, 0, 0, 0, 0, pkt->Entries[i].MonsterType, 0);
			if (pkt->Entries[i].Name[0] != 0)
				printf("\rgift list packet, gift %d from %s. reward group %d, count %d, quality %d. Old Entry %d %d. Time %d \n", pkt->Entries[i].MonsterType, pkt->Entries[i].Name, pkt->Entries[i].GiftTypeGroup, pkt->Entries[i].GiftCount, pkt->Entries[i].MaterialQuality, pkt->Entries[i].MonsterType >> 8, pkt->Entries[i].MonsterType & 0xFF, (int)TimePassed);
		}
	}
#endif
#ifdef PARSE_PLAYER_MIGHT_LIST
	if (packet[0] == 0x06 && packet[1] == 0x0B && packet[2] == 0x00)
	{
		unsigned char PlayersInList = *(unsigned char*)&packet[4];
#pragma pack(push, 1)
		struct PlayerMightListEntry
		{
			unsigned __int64 Unk1; //seems to be increasing as list goes along
			unsigned short Unk2; 
			char Name[13];
			char Rank;
			unsigned __int64 Might;
			unsigned __int64 Kills;
			unsigned __int64 LastSeen;
		};
#pragma pack(pop)
		PlayerMightListEntry* pkt = (PlayerMightListEntry*)&packet[5];
		for (int i = 0; i < PlayersInList; i++)
		{
			pkt[i].Name[12] = 0;
			QueueObjectToProcess(OBJECT_TYPE_CUSTOM_GUILD_MEMBER_MIGHT, 0, 0, 0, pkt[i].Name, NULL, NULL, 0, pkt[i].Kills, 0, pkt[i].Rank, pkt[i].Might, 0, 0, 0, 0, 0);
		}
	}
#endif
#ifdef PARSE_CASSTLE_PACKETS
	// some invalid id packet ?
	if (size <= 17)
		return;

	// castle popup packets
	if (packet[0] == 0xAC && packet[1] == 0x08 && packet[2] == 0x0C)
	{
		ParsePacketCastlePopup(packet, size);
		return;
	}

	// visible object query rely. Castles, mines ... 
	if (packet[0] == 0xAC && packet[1] == 0x08 && (packet[2] == 0x02 || packet[2] == 0x03 || packet[2] == 0x0F || packet[2] == 0x0D || packet[2] == 0x0E || packet[2] == 0x09 || packet[2] == 0x18 || packet[2] == 0x17 || packet[2] == 0x16))
	{
		ParsePacketQueryTileObjectReply(packet, size);
		return;
	}

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
}

void MatchPacketDumpContent()
{
	FILE *f;
	//	errno_t er = fopen_s(&f, "P3.bin", "rb");
	errno_t er = fopen_s(&f, "parsed_input.txt", "wt");
	if (!f)
	{
		printf("Could not open output file\n");
		return;
	}
	/*	{
	for (std::map<int, CastlePopupInfo*>::iterator itr = ClickCastlePackets.begin(); itr != ClickCastlePackets.end(); itr++)
	{
	int GUID = itr->first;
	std::map<int, BuildingObjectDesciption*>::iterator fc = MapCastlePackets.find(GUID);
	if (fc != MapCastlePackets.end() && fc->second->Unk8 != 8 )
	printf("unk is %d\n", fc->second->Unk8);
	}

	}/**/
	//	int t = 0;
	printf("Started dumping usable packets to text file\n");
	for (std::map<int, GenericMapObject*>::iterator itr = MapCastlePackets.begin(); itr != MapCastlePackets.end(); itr++)
	{
		int GUID = itr->first;
		GenericMapObject *p1 = itr->second;
		int x, y;
		GetXYFromGUID(p1->GUID, x, y);
		char tName[500], tGuild[5];
		//		if (p1->Unk8 != 8)
		//			printf("%d)p1->Unk8 %d\n", t++, p1->Unk8);
		int i;
		for (i = 0; i < sizeof(p1->B.Name) && p1->B.Name[i] != 0; i++) tName[i] = p1->B.Name[i];
		tName[i] = 0;
		for (i = 0; i < sizeof(p1->B.Guild) && p1->B.Guild[i] != 0; i++) tGuild[i] = p1->B.Guild[i];
		tGuild[i] = 0;
		fprintf(f, "%u \t %u \t %s \t %s \t %d", x, y, tName, tGuild, (int)p1->B.CastleLevel);
		std::map<int, CastlePopupInfo*>::iterator fc = ClickCastlePackets.find(GenMyGUID(x,y));
		if (fc != ClickCastlePackets.end())
		{
			char tGuild2[500];
			CastlePopupInfo *p2 = fc->second;
			for (i = 0; i < sizeof(p2->GuildFullName) && p2->GuildFullName[i] != 0; i++) tGuild2[i] = p2->GuildFullName[i];
			tGuild2[i] = 0;
			fprintf(f, " \t %s \t %d \t %d \t %u \t %u", tGuild2, (int)p2->GuildRank, (int)p2->Kills, (int)p2->Might, (int)p2->VIPLevel);
		}
		else
		{
			fprintf(f, " \t %s \t %d \t %d \t %u \t %u", "", (int)0, (int)0, (int)0, (int)0);
		}
		fprintf(f, "\n");
	}

	fclose(f);

	printf("Done dumping usable packets to text file\n");
}

void ParseOfflineDump(const char *FileName)
{
	//MergeFiles("P1.bin", "P2.bin"); return;
	FILE *f;
	//	errno_t er = fopen_s(&f, "P3.bin", "rb");
	//(ip.src == 192.243.47.118 && ip.dst == 192.168.1.101) || (ip.src == 192.168.1.101 && ip.dst==192.243.47.118)
	errno_t er = fopen_s(&f, FileName, "rb");
	unsigned char *PacketBuffer = (unsigned char*)malloc(65000);
	size_t ReadCount;
	int AbortAfterNPackets = 100;
	int PacketsRead = 0;
	if (f)
	{
		unsigned short ByteCount;
		while (ReadCount = fread_s(&ByteCount, sizeof(ByteCount), 1, sizeof(ByteCount), f))
		{
#ifdef _DEBUG
			printf("%d)Packet should have %d bytes in it\n", PacketsRead++, ByteCount);
#endif#endif
			if (ByteCount > 2 && ByteCount < sizeof(PacketBuffer))
			{
				ReadCount = fread_s(&PacketBuffer, sizeof(PacketBuffer), 1, ByteCount - 2, f);
				//				ProcessPacket(PacketBuffer, ByteCount - 2);
#ifdef _DEBUG
				//				PrintDataHexFormat(PacketBuffer, ByteCount - 2, 0, ByteCount - 2);
#endif
				ProcessPacket1(PacketBuffer, ByteCount - 2);
			}
			AbortAfterNPackets--;
			//			if (AbortAfterNPackets <= 0)
			//				break;
		}

		fclose(f);
	}
	else
		printf("Could not open input file\n");

	MatchPacketDumpContent();
}

#include <windows.h>
unsigned char *PacketCircularBuffer[MAX_PACKET_CIRCULAR_BUFFER];
int PacketCircularBufferReadIndex = 0;
int PacketCircularBufferWriteIndex = 0;
int	KeepThreadsRunning = 1;

void QueuePacketToProcess(unsigned char *data, int size)
{
	if (size <= 0)
		return;
#ifdef _DEBUG
	printf("Queue packet for parsing : %d\n", size);
#endif
	unsigned char *t = (unsigned char*)malloc(size + 2 + 2);
	*(unsigned short *)t = size;
	memcpy(t+2, data, size);
	PacketCircularBuffer[PacketCircularBufferWriteIndex] = t;
	PacketCircularBufferWriteIndex = (PacketCircularBufferWriteIndex + 1) % MAX_PACKET_CIRCULAR_BUFFER;
}

DWORD WINAPI BackgroundProcessPackets(LPVOID lpParam)
{
	while (KeepThreadsRunning==1)
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
//				printf("process packet : in queue %d\n", PacketCircularBufferWriteIndex - PopIndex);
#ifndef _DEBUG
				printf("\rprocess packet : in queue %d                     ", PacketCircularBufferWriteIndex - PopIndex);
#endif
				ProcessPacket1(&PopBuffer[2], *(unsigned short*)PopBuffer);
				//we no longer need this buffer
				free( PopBuffer );
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

void ProcessPacketDebug(char *HexStr)
{
	unsigned char TPacket[16000];
	int TSize;
	HexToByteStr(HexStr, TPacket, TSize);
	ProcessPacket1((unsigned char*)TPacket, TSize);
}