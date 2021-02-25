#if 0
void MergeFiles(char *fn1, char *fn2)
{
	FILE *f1;
	errno_t er;
	er = fopen_s(&f1, fn1, "rb");
	FILE *f2;
	er = fopen_s(&f2, fn2, "rb");
	FILE *f3;
	er = fopen_s(&f3, "P3.bin", "wb");
	int ReadCount = 1;
	char t[100];
	do
	{
		ReadCount = fread_s(&t[0], sizeof(t), 1, sizeof(t), f1);
		if (ReadCount>0)
			fwrite(&t[0], 1, ReadCount, f3);
	} while (ReadCount > 0);
	do
	{
		ReadCount = fread_s(&t[0], sizeof(t), 1, sizeof(t), f2);
		if (ReadCount>0)
			fwrite(&t[0], 1, ReadCount, f3);
	} while (ReadCount > 0);
}

void DumpUnkPacket(unsigned char *packet, int size)
{
	FILE *f;
	errno_t er = fopen_s(&f, "unk.txt", "at");
	if (f)
	{
		fprintf(f, "%04d ", size);
		for (int i = 0; i < size; i++)
			fprintf(f, "%02X ", packet[i]);
		fprintf(f, "\n\n");
		fclose(f);
	}
}

void ProcessPacket(unsigned char *packet, int size)
{
	int ReadIndex = 0;
	if (packet[ReadIndex] == 0xAC && packet[ReadIndex + 1] == 0x08)
	{
		ReadIndex += 2;
		while (ReadIndex < size)
		{
			if (packet[ReadIndex] == 0x02)
			{
				ReadIndex++;

				//99 CD 00 00 00 00 00 00 26 02 D8
				ReadIndex += 11;
			}
			if (packet[ReadIndex] == 0x09)
			{
				ReadIndex++;

				//FA 27 00 00 00 00 00 00 13 00 2A 04
				ReadIndex += 12;
			}
			else if (packet[ReadIndex] == 0x0C)
			{
				ReadIndex++;

				//10 F1 00 00 00 00 00 00 2F 00 36 02 03 - ?
				ReadIndex += 13;

				//48 65 6C 6C 66 69 72 65 20 53 6E 61 6B 65 4C 61 6E 64 73 00 		Hellfire SnakeLands
				char Name[20 + 1];
				memcpy(Name, &packet[ReadIndex], 20);
				Name[20] = 0; // sanity
				printf("Packet AC 08 0C name : %s\n", Name);
				ReadIndex += 20;

				//0A 01 02 00 00 00 00 00 CA 1D 5D 00 00 00 00 00 6C 1E 08 00 00 00 00 00 0C 00 01 04 E4 AD B7 58 00 00 -> 24 bytes but it's not static ! 
				ReadIndex += 24;
			}
			else if (packet[ReadIndex] == 0x0E)
			{
				ReadIndex++;

				//13 F1 00 00 00 00 00 00 33 00 36 02
				ReadIndex += 12;

				//69 23 09 00 - 4 bytes before the name 			
				ReadIndex += 4;

				//54 78 7A 64 71 68 7A 33 00 00 00 00 00 45 63 63 43 00		Txzdqhz3
				PlayerNameDesc PD;
				memcpy(&PD, &packet[ReadIndex], sizeof(PD));
				printf("Packet AC 08 0E name : [%c%c%c]%s\n", PD.Guild[0], PD.Guild[1], PD.Guild[2], PD.Name);
				ReadIndex += sizeof(PD);	// name guild realm

				//25 02 F0 36 02 14 F3 AB B7 58 00 00 00 00 F2 01 00 00 00 00 00 00 00 00 00 00 10
				ReadIndex += 27;
			}
			else if (packet[ReadIndex] == 0x0F)
			{
				ReadIndex++;

				//12 F1 00 00 00 00 00 00 36 02 
				ReadIndex += 10;

				//61 1E 09 00 - 4 bytes before the name 			
				ReadIndex += 4;
			}
			else if (packet[ReadIndex] == 0x18)
			{
				ReadIndex++;

				//26 02 18 36 02 or just 2 ? 
				ReadIndex += 4;
			}
			//not good at all
			else
			{
				printf("leftover bytes %d. Exiting packet parsing\n", size - ReadIndex);
				DumpUnkPacket(packet, size);
				break;
			}
		}
	}
	if (size == 66)
	{

	}
}

/*
unsigned char Unknown10bytes[10];
fread_s(&Unknown10bytes[0], sizeof(Unknown10bytes), 1, sizeof(Unknown10bytes), f);

unsigned char MaybeBlockCount;
fread_s(&MaybeBlockCount, sizeof(MaybeBlockCount), 1, sizeof(MaybeBlockCount), f);
printf("Maybe block count number %d\n", MaybeBlockCount);

unsigned char Unk1;
fread_s(&Unk1, sizeof(Unk1), 1, sizeof(Unk1), f);

PlayerNameDesc PN;
int BytesRead;
int BlockCounter = 0;
while (BytesRead = fread_s(&PN, sizeof(PN), 1, sizeof(PN), f) )
{
//			fread_s(&PN, sizeof(PN), 1, sizeof(PN), f);
printf("%d)Name : [%c%c%c]%s Bytes read %d\n", BlockCounter, PN.Guild[0], PN.Guild[1], PN.Guild[2], PN.Name, (int)ftell(f));
BlockCounter++;
//			BytesRead += sizeof(PN);
}
*/
#endif