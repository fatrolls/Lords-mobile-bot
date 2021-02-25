#include <windows.h>
#include <time.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>

#include "curl/curl.h"
#pragma comment(lib, "curl\\libcurl.a")
#pragma comment(lib, "curl\\libcurl.dll.a")

struct MemoryStruct {
    char* memory;
    size_t size;
};

static size_t WriteMemoryCallback(void* contents, size_t size, size_t nmemb, void* userp)
{
    size_t realsize = size * nmemb;
    struct MemoryStruct* mem = (struct MemoryStruct*)userp;

    char* ptr = (char*)realloc(mem->memory, mem->size + realsize + 1);
    if (ptr == NULL)
    {
        return 0;
    }

    mem->memory = ptr;
    memcpy(&(mem->memory[mem->size]), contents, realsize);
    mem->size += realsize;
    mem->memory[mem->size] = 0;

    return realsize;

}

int DoHTTPPost(const char *URL, const char *PostVars)
{
    CURL* curl;
    CURLcode res = CURLE_FAILED_INIT;

    curl = curl_easy_init();
    if (curl)
    {
        //curl_version_info_data* vinfo = curl_version_info(CURLVERSION_NOW);
        struct MemoryStruct chunk;

        chunk.memory = (char*)malloc(1);  /* will be grown as needed by the realloc above */
        chunk.size = 0;    /* no data at this point */

#ifndef _DEBUG
        curl_easy_setopt(curl, CURLOPT_VERBOSE, 0L);
#endif

        curl_easy_setopt(curl, CURLOPT_URL, URL);

        //get the fingerprint to this computer so that the license can not be shared between multiple PCs
        curl_easy_setopt(curl, CURLOPT_POSTFIELDS, PostVars);

        curl_easy_setopt(curl, CURLOPT_SSL_VERIFYPEER, 0L);
        curl_easy_setopt(curl, CURLOPT_SSL_VERIFYHOST, 0L);

        /* cert is stored PEM coded in file... */
        /* since PEM is default, we needn't set it for PEM */
        curl_easy_setopt(curl, CURLOPT_SSLCERTTYPE, "PEM");

        /* set the cert for client authentication */
        curl_easy_setopt(curl, CURLOPT_SSLCERT, "curl-ca-bundle.crt");

        /* set the private key (file or ID in engine) */
 //       curl_easy_setopt(curl, CURLOPT_SSLKEY, pKeyName);

            /* send all data to this function  */
        curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, WriteMemoryCallback);

        /* we pass our 'chunk' struct to the callback function */
        curl_easy_setopt(curl, CURLOPT_WRITEDATA, (void*)&chunk);

        res = curl_easy_perform(curl);
        if (res != CURLE_OK)
        {
#ifdef _DEBUG
            const char* ErrorStr = curl_easy_strerror(res);
            fprintf(stderr, "curl_easy_perform() failed: %s\n", ErrorStr);
#endif
        }
        curl_easy_cleanup(curl);

        if (chunk.size > 0)
        {
            if (chunk.memory != NULL)
                free(chunk.memory);
            chunk.memory = NULL;
        }
    }
    if (res != CURLE_OK)
        return 1;
    return 0;
}
