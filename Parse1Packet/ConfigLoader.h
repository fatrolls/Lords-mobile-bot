#pragma once

extern "C"
{
	/*
		If you have time, rewrite this to be something fast and cached...
	*/
	int GetIntConfig(const char *Filename, const char *ConfName, int *Store);
	int GetStrConfig(const char *Filename, const char *ConfName, char *Store, int MaxBytes);
	int GetFloatConfig(const char *Filename, const char *ConfName, float *Store);
}

struct GlobalConfig
{
	int		AutoPickCard; // which network card to auto pick on startup
	char	UploadURL[255];
};

extern GlobalConfig GlobalConfigs;

void LoadAllConfigs();