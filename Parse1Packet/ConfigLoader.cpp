#include <stdio.h>
#include <cstringt.h>
#include "ConfigLoader.h"

GlobalConfig GlobalConfigs;

int chrpos(const char *str, const char c)
{
	int i = 0;
	while (str[i] != 0)
	{
		if (str[i] == c)
			return i;
		else
			i++;
	}
	return -1;
}

int strcmp2(const char *s1, const char *s2)
{
	int i = 0;
	while (s1[i] == s2[i] && s2[i] != 0 && s1[i] != 0)
		i++;
	//did we found the whole s2 ?
	if (s2[i] == 0)
		return 0;
	return 1;
}

void RemoveEOL(char *s)
{
	int i = 0;
	while (s[i] != 0)
	{
		if (s[i] == '\n' || s[i] == '\r')
			s[i] = 0;
		i++;
	}
}

int GenericStrLoader(const char *Filename, const char *ConfName, char *Store, int MaxStore)
{
	FILE* f;
	errno_t er = fopen_s(&f, Filename, "rt");
	if (f == NULL)
		return 1;
	while (!feof(f))
	{
		char ConfigFileLine[1500];
		char *ret = fgets(ConfigFileLine, sizeof(ConfigFileLine), f);
		if (ret && strcmp2(ConfigFileLine, ConfName) == 0)
		{
			int strposI = chrpos(ConfigFileLine, '=');
			if (strposI > 0)
			{
				fclose(f);
				strcpy_s(Store, MaxStore, &ConfigFileLine[strposI + 1]);
				RemoveEOL(Store);
				return 0;
			}
		}
	}
	Store[0] = 0;
	return -1;
}

int GetIntConfig(const char *Filename, const char *ConfName, int *Store)
{
	char ConfigFileValue[1500];
	int ret = GenericStrLoader(Filename, ConfName, ConfigFileValue, sizeof(ConfigFileValue));
	if (ret != 0)
		return ret;
	*Store = atoi(ConfigFileValue);
	return 0;
}

int GetStrConfig(const char *Filename, const char *ConfName, char *Store, int MaxBytes)
{
	return GenericStrLoader(Filename, ConfName, Store, MaxBytes);
}

int GetFloatConfig(const char *Filename, const char *ConfName, float *Store)
{
	char ConfigFileValue[1500];
	int ret = GenericStrLoader(Filename, ConfName, ConfigFileValue, sizeof(ConfigFileValue));
	if (ret != 0)
		return ret;
	*Store = (float)atof(ConfigFileValue);
	return 0;
}

void LoadAllConfigs()
{
	GlobalConfigs.AutoPickCard = -1;
	GetIntConfig("init.cfg", "CardIndex", &GlobalConfigs.AutoPickCard);
	GetStrConfig("init.cfg", "UploadURL", GlobalConfigs.UploadURL, sizeof(GlobalConfigs.UploadURL));
}