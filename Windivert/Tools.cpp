#include <Windows.h>
#include <stdio.h>

int IsKeyboardAscii(unsigned char c)
{
	//	if (c == 0)
	//		return 1;
	if (c >= 32 && c <= 125)
		return 1;
	if (c == '~' || c == '`')
		return 1;
	return 0;
}

int IsVisibleAscii(unsigned char c)
{
	if (c >= 32 && c <= 125 && c != ' ')
		return 1;
	if (c == '~' || c == '`')
		return 1;
	return 0;
}

int OneStringOnSize(unsigned char *packet, int size, int Ind, int StringSize)
{
	int i;

	//read string until it ends
	for (i = Ind; i < Ind + StringSize && i < size; i++)
		if (IsKeyboardAscii(packet[i]) == 0)
			break;

	//make sure the remaining ones are 0
	for (; i < Ind + StringSize && i < size; i++)
		if (packet[i] != 0)
			break;

	//one string in store ?
	if (i == Ind + StringSize)
		return 1;

	//send the nope
	return 0;
}

unsigned char *t = NULL;
int SearchNameInFile(unsigned char *Name)
{
	if (!t)
	{
		FILE *f;
		errno_t er = fopen_s(&f, "Players27.txt", "rt");
		if (f)
		{
			t = (unsigned char*)malloc(10 * 1024 * 1024);
			fread(t, 1, 10 * 1024 * 1024, f);
		}
		fclose(f);
	}
	return strstr((char*)t, (char*)Name) != NULL;
}

void PrintDataMultipleFormats(unsigned char *packet, int size, int From, int To)
{
	//dump the content from prev packet to cur
	for (int i = From; i < To; i++)
		printf("%02X ", packet[i]);
	printf("\n");

	//same content as chars. Maybe we notice a new name format
	for (int i = From; i < To; i++)
		if (IsKeyboardAscii(packet[i]) && packet[i] != 0)
			printf("% 3c", packet[i]);
		else
			printf("% 3c", ' ');
	printf("\n");

	//same content as numbers. Maybe we notice a coord format
	for (int i = From; i < To; i++)
		printf("% 3d", packet[i]);
	printf("\n");

	//same content as short numbers. Maybe we notice a coord format
	for (int i = From; i < To; i++)
		printf("% 2d ", *(unsigned short*)&packet[i]);
	printf("\n");

	/*	//same content as swapped short numbers. Maybe we notice a coord format
	for (int i = From; i < To; i++)
	printf("% 2d ", (unsigned short)((unsigned int)packet[i] * 256 + (unsigned int)packet[i + 1]));
	printf("\n"); */

	//same content as int numbers. Maybe we notice a coord format
	for (int i = From; i < To; i++)
		printf("% 2u ", *(unsigned int*)&packet[i]);
	printf("\n");

	/*	//same content as swapped int numbers. Maybe we notice a coord format
	for (int i = From; i < To; i++)
	printf("% 2u ", (unsigned int)(((unsigned int)packet[i] << 24) + ((unsigned int)packet[i + 1] << 16) + ((unsigned int)packet[i + 2] << 8) + ((unsigned int)packet[i + 3] << 0)));
	printf("\n"); */

	//same content as float numbers. Maybe we notice a coord format
	//		for (int i = PrevNameStart; i < NameStart; i++)
	//			printf("%.1f ", *(float*)&packet[i]);
	//		printf("\n");

	/*	//same content as float numbers. Maybe we notice a coord format
	for (int i = From; i < To; i++)
	{
	unsigned short HalfFloat = *(unsigned short*)&packet[i];
	unsigned int val = HalfFloat & 511;	//9 bits
	unsigned int exp = ( HalfFloat >> 9 ) & 31;	//5 bits
	for (unsigned int j = 0; j < exp; j++)
	val *= 10;
	unsigned int Sign = HalfFloat >> 14;
	printf("% 3u ", val );
	}
	printf("\n"); */
}

void PrintDataHexFormat(unsigned char *packet, int size, int From, int To)
{
	if (From == To)
		return;
	//dump the content from prev packet to cur
	for (int i = From; i < To; i++)
		printf("%02X ", packet[i]);
	printf("\n");
}

void PrintFixedLenString(char *PreStr, char *str, int len, int WithNewLine)
{
	if (PreStr != NULL)
		printf("%s", PreStr);

	for (int i = 0; i < len; i++)
	{
		if (str[i] != 0)
			printf("%c", str[i]);
		else
			break;
	}

	if (WithNewLine == 1)
		printf("\n");
}

int HexToByte(unsigned char Hex)
{
	if (Hex >= '0' && Hex <= '9')
		return Hex - '0';
	if (Hex >= 'a' && Hex <= 'f')
		return Hex - 'a' + 10;
	if (Hex >= 'A' && Hex <= 'F')
		return Hex - 'A' + 10;
	return 0;
}

void HexToByteStr(char *HexStr, unsigned char *ByteStr, int &size)
{
	int Ind = 0;
	int MaxInd = (int)strlen(HexStr);
	size = 0;
	while (HexStr[Ind] != 0 && Ind<MaxInd)
	{
		ByteStr[size] = (HexToByte(HexStr[Ind]) << 4) + HexToByte(HexStr[Ind + 1]);
		Ind += 2;
		if (HexStr[Ind] == ' ')
			Ind++;
		size += 1;
	}
}

int IsAllZero(void *packet, int size, int Ind, int count)
{
	int IsAllZero = 1;
	unsigned char *tpacket = (unsigned char *)packet;
	for (int i = Ind + count; i >= Ind; i--)
		if (tpacket[i] != 0)
		{
			IsAllZero = 0;
			break;
		}
	return IsAllZero;
}