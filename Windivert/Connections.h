#pragma once

extern int KeepThreadsAlive;
extern int ThreadsRunning;
//create a filter for both incomming and outgoing data
void InitConnections();
void ShutDownConnections();