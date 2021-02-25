#include "HTTPSendData.h"

#define _WINSOCK_DEPRECATED_NO_WARNINGS 

#include <string.h>
#include <winsock2.h>
#include <windows.h>
#include <iostream>
#include <vector>
#include <locale>
#include <sstream>
#include <cctype>
#include <iomanip>
#include <sstream>
#include <string>

#include "ParsePackets.h"

using namespace std;
#pragma comment(lib,"ws2_32.lib")

string url_encode(const string value) 
{
	ostringstream escaped;
	escaped.fill('0');
	escaped << hex;

	for (string::const_iterator i = value.begin(), n = value.end(); i != n; ++i) {
		string::value_type c = (*i);

		// Keep alphanumeric and other accepted characters intact
		if (isalnum(c) || c == '-' || c == '_' || c == '.' || c == '~') {
			escaped << c;
			continue;
		}

		// Any other characters are percent-encoded
		escaped << uppercase;
		escaped << '%' << setw(2) << int((unsigned char)c);
		escaped << nouppercase;
	}

	return escaped.str();
}

void AppendURLQuery(string &url, const char *name, int val, int IsFirst=0)
{
	char temp[2000];
	if (IsFirst==0)
		sprintf_s(temp, "&%s=%d", name, val);
	else
		sprintf_s(temp, "%s=%d", name, val);
	url += temp;
}

void AppendURLQuery(string &url, const char *name, const char *val, int IsFirst = 0)
{
	if (name == NULL || val == NULL || val[0] == 0)
		return;
	char temp[2000];
	string tval = url_encode(val);
	if (IsFirst == 0)
		sprintf_s(temp, "&%s=%s", name, tval.c_str());
	else
		sprintf_s(temp, "%s=%s", name, tval.c_str());
	url += temp;
}

void strcpy_s_max(char *to, int maxto, char *src, int maxsrc)
{
	int Ind = 0;
	while (src[Ind] != 0 && Ind < maxsrc && Ind < maxto)
	{
		to[Ind] = src[Ind];
		Ind++;
	}
	if (to[Ind] != 0)
		to[Ind] = 0;
}

int HTTPPostData(string get_http, string p_url, int p_port)
{
	SOCKET Socket;
	SOCKADDR_IN SockAddr;
	int lineCount = 0;
	int rowCount = 0;
	struct hostent *host;
	locale local;
	int i = 0;

	// website url
//	string urlhttp = "127.0.0.1 : 8081";
	string url = "127.0.0.1";
	if (p_url != "")
		url = p_url;
	int URLPort = 8081;
	if (p_port > 0)
		URLPort = p_port;
	string urlhttp = url + " : " + to_string(URLPort);

	get_http += " HTTP / 1.1\r\n";
	get_http += "Host: " + urlhttp;
	//	get_http += " : " + URLPort;
	get_http += " \r\n";
	get_http += "Connection: close\r\n\r\n";

	Socket = socket(AF_INET, SOCK_STREAM, IPPROTO_TCP);
	host = gethostbyname(url.c_str());

	SockAddr.sin_port = htons(URLPort);
	SockAddr.sin_family = AF_INET;
	SockAddr.sin_addr.s_addr = *((unsigned long*)host->h_addr);

	if (connect(Socket, (SOCKADDR*)(&SockAddr), sizeof(SockAddr)) != 0)
	{
		printf("Could not connect\n");
		//system("pause");
		return 1;
	}

	// send GET / HTTP
	int BytesToSend = (int)strlen(get_http.c_str());
	int BytesTotalSent = 0;
	BytesTotalSent = send(Socket, get_http.c_str(), (int)BytesToSend, 0);

	// recieve html
//#define DEBUG_HTTP_BEHAVIOR 1
#ifdef DEBUG_HTTP_BEHAVIOR
	printf("Our http query is : %s\n", get_http.c_str());
	char buffer[10000];
	int nDataLength;
	string website_HTML;
	while ((nDataLength = recv(Socket, buffer, 10000, 0)) > 0)
	{
		int i = 0;
		while (buffer[i] >= 32 || buffer[i] == '\n' || buffer[i] == '\r')
		{
			website_HTML += buffer[i];
			i += 1;
		}
	}

	// Display HTML source 
	printf("%s\n", website_HTML.c_str());
#endif

	closesocket(Socket);
	return !(BytesTotalSent == BytesToSend);
}

int HTTPPostDataPlayer(int type, int k, int x, int y, char *name, char *guild, char *guildf, int clevel, __int64 kills, int vip, int grank, __int64 might, int StatusFlags, int plevel, int title, int monstertype, int max_amt)
{
	printf("Send http for player %s, vip %d\n", name, vip);

	//HTTP GET
	string get_http = "GET /ImportPlayerInfoFromNetwork3.php?";
	AppendURLQuery(get_http, "k", k, 1);
	AppendURLQuery(get_http, "x", x);
	AppendURLQuery(get_http, "y", y);
	if (clevel>0)
		AppendURLQuery(get_http, "CLevel", clevel);
	if (kills>0)
		AppendURLQuery(get_http, "kills", (int)kills);
	if (vip>0)
		AppendURLQuery(get_http, "vip", vip);
	if (grank>0)
		AppendURLQuery(get_http, "GuildRank", grank);
	if (might>0)
		AppendURLQuery(get_http, "might", (int)might);
	AppendURLQuery(get_http, "StatusFlags", StatusFlags);
	AppendURLQuery(get_http, "title", title);
	AppendURLQuery(get_http, "name", name);
	AppendURLQuery(get_http, "guild", guild);
	if (guildf != NULL && guildf[0]!=0)
		AppendURLQuery(get_http, "guildF", guildf);
	AppendURLQuery(get_http, "objtype", type);
	if (monstertype>0)
		AppendURLQuery(get_http, "monstertype", monstertype);
	if (max_amt>0)
		AppendURLQuery(get_http, "MaxAmtNow", max_amt);

	return HTTPPostData(get_http, "", 0);
}

void HTTP_GenerateMaps()
{
	string get_http = "GET /PostImportActions.php";
	HTTPPostData(get_http,"", 0);
	HTTPPostData(get_http, "5.79.67.171", 80);
}

void StopThreadedPlayerSender();
void CreateBackgroundPlayerProcessThread();
WSADATA wsaData;
void HttpSendStartup()
{
	if (WSAStartup(MAKEWORD(2, 2), &wsaData) != 0)
	{
		printf("WSAStartup failed.\n");
		//system("pause");
		return;
	}
	CreateBackgroundPlayerProcessThread();
}

void HttpSendShutdown()
{
	StopThreadedPlayerSender();
	WSACleanup();
}


#include <windows.h>

struct ObjectCommitStore
{
	int type; //see ingame object types to identify this

	//if it is a castle
	int k;
	int x;
	int y;
	char name[50];
	char guild[50];
	char guildf[50];
	int clevel;
	__int64 kills;
	int vip;
	int grank;
	__int64 might;
	int StatusFlags;
	int plevel;
	int title;

	//if it is a mineral
	int max_amt_now;
	int monstertype; // can be monster type also
};

ObjectCommitStore PlayerCircularBuffer[MAX_PLAYERS_CIRCULAR_BUFFER];
int PlayerCircularBufferReadIndex = 0;
int PlayerCircularBufferWriteIndex = 0;
int	KeepPlayerPushThreadsRunning = 1;

void QueueObjectToProcess(int type, int k, int x, int y, char *name, char *guild, char *guildf, int clevel, __int64 kills, int vip, int grank, __int64 might, int StatusFlags, int plevel, int title, int monstertype, int max_amt)
{
	int WriteIndex = PlayerCircularBufferWriteIndex;
	PlayerCircularBuffer[WriteIndex].type = type;
	PlayerCircularBuffer[WriteIndex].k = k;
	PlayerCircularBuffer[WriteIndex].x = x;
	PlayerCircularBuffer[WriteIndex].y = y;

	if (name != NULL)
		strcpy_s_max(PlayerCircularBuffer[WriteIndex].name, sizeof(PlayerCircularBuffer[WriteIndex].name), name, MAX_BUILDING_NAME);
	else
		PlayerCircularBuffer[WriteIndex].name[0] = 0;
	if (guild != NULL)
		strcpy_s_max(PlayerCircularBuffer[WriteIndex].guild, sizeof(PlayerCircularBuffer[WriteIndex].guild), guild, MAX_GUILD_SHORT_NAME);
	else
		PlayerCircularBuffer[WriteIndex].guild[0] = 0;
	if (guildf != NULL)
		strcpy_s_max(PlayerCircularBuffer[WriteIndex].guildf, sizeof(PlayerCircularBuffer[WriteIndex].guildf), guildf, MAX_GUILD_FULL_NAME);
	else
		PlayerCircularBuffer[WriteIndex].guildf[0] = 0;

	PlayerCircularBuffer[WriteIndex].clevel = clevel;
	PlayerCircularBuffer[WriteIndex].kills = kills;
	PlayerCircularBuffer[WriteIndex].vip = vip;
	PlayerCircularBuffer[WriteIndex].grank = grank;
	PlayerCircularBuffer[WriteIndex].might = might;
	PlayerCircularBuffer[WriteIndex].StatusFlags = StatusFlags;
	PlayerCircularBuffer[WriteIndex].plevel = plevel;
	PlayerCircularBuffer[WriteIndex].title = title;
	PlayerCircularBuffer[WriteIndex].max_amt_now = max_amt;
	PlayerCircularBuffer[WriteIndex].monstertype = monstertype;

	PlayerCircularBufferWriteIndex = (PlayerCircularBufferWriteIndex + 1) % MAX_PLAYERS_CIRCULAR_BUFFER;
}

int HTTPPostData(ObjectCommitStore &t)
{
	return HTTPPostDataPlayer(t.type, t.k, t.x, t.y, t.name, t.guild, t.guildf, t.clevel, t.kills, t.vip, t.grank, t.might, t.StatusFlags, t.plevel, t.title, t.monstertype, t.max_amt_now);
}

DWORD WINAPI BackgroundProcessPlayers(LPVOID lpParam)
{
	while (KeepPlayerPushThreadsRunning == 1)
	{
		//can we pop a packet from the queue ?
		if (PlayerCircularBufferReadIndex != PlayerCircularBufferWriteIndex)
		{
			printf("process player : in queue %d ( slots remain %d)\n", PlayerCircularBufferWriteIndex - PlayerCircularBufferReadIndex, MAX_PLAYERS_CIRCULAR_BUFFER - PlayerCircularBufferWriteIndex);
			if (HTTPPostData(PlayerCircularBuffer[PlayerCircularBufferReadIndex]) == 0)
				PlayerCircularBufferReadIndex = (PlayerCircularBufferReadIndex + 1) % MAX_PLAYERS_CIRCULAR_BUFFER;
		}
		else
		{
			PlayerCircularBufferWriteIndex = PlayerCircularBufferReadIndex = 0;
			//avoid 100% CPU usage. There is no scientific value here
			Sleep(10);
		}
	}
	KeepPlayerPushThreadsRunning = 0;
	return 0;
}

int		ThreadParamPlayerData = 0;
HANDLE	PlayerProcessThreadHandle = 0;
void	CreateBackgroundPlayerProcessThread()
{
	//1 processing thread is enough
	if (PlayerProcessThreadHandle != 0)
		return;

	//make our queue empty
	memset(PlayerCircularBuffer, 0, sizeof(PlayerCircularBuffer));

	//create the processing thread 
	DWORD   PacketProcessThreadId;
	PlayerProcessThreadHandle = CreateThread(
		NULL,						// default security attributes
		0,							// use default stack size  
		BackgroundProcessPlayers,   // thread function name
		&ThreadParamPlayerData,		// argument to thread function 
		0,							// use default creation flags 
		&PacketProcessThreadId);	// returns the thread identifier 

	printf("Done creating background thread to send data over http\n");
}

void	StopThreadedPlayerSender()
{
	if (PlayerProcessThreadHandle == 0)
		return;

	//signal that we want to break the processing loop
	KeepPlayerPushThreadsRunning = 2;
	//wait for the processing thread to finish
	while (KeepPlayerPushThreadsRunning != 0)
		Sleep(10);
	//close the thread properly
	CloseHandle(PlayerProcessThreadHandle);
	PlayerProcessThreadHandle = 0;
}

int IsHTTPQueueEmpty()
{
	return (PlayerCircularBufferReadIndex == PlayerCircularBufferWriteIndex || PlayerProcessThreadHandle == 0);
}