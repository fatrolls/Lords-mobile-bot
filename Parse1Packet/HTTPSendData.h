#pragma once

//we are expecting this to lag behind a lot. Maybe never even catch up with the real time scanner
#define MAX_PLAYERS_CIRCULAR_BUFFER		12000

//int HTTPPostData(int k, int x, int y, char *name, char *guild, char *guildf, int clevel, __int64 kills, int vip, int grank, __int64 might, int HasPrisoners, int plevel);
void HTTP_GenerateMaps();

void HttpSendStartup();
void HttpSendShutdown();

void QueueObjectToProcess(int type, int k, int x, int y, char *name, char *guild, char *guildf, int clevel, __int64 kills, int vip, int grank, __int64 might, int StatusFlags, int plevel, int title, int monstertype, int max_amt);
int IsHTTPQueueEmpty();
int DoHTTPPost(const char* URL, const char* PostVars);