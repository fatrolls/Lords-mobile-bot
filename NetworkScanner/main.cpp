#include <conio.h>
#include <stdio.h>
#include "ParsePackets.h"
#include "CapturePackets.h"
#include "HTTPSendData.h"
#include "LordsMobileControl.h"
#include "ConsoleListener.h"
#include <Windows.h>

void OfflineParsing()
{
	HttpSendStartup();
//	ProcessPacketDebug("AC 08 17 05 00 4F 28 08 00 54 75 64 69 36 39 00 00 00 00 00 00 00 23 42 4C 43 00 13 03 2D 14 03 47 8F 24 D2 58 00 00 00 00 C3 00 00 00 00 00 00 00 00 00 00 00 ");
//	ProcessPacketDebug("AC08170000C84200000000000000000000000000000000000000000000000000000000040373084461726B2E6E657374000000000000004300010000000000010200000000000000000000000000040395084461726B2E6E6573740000000000000043000200000000000102000000000000000000000000000403780100000000000000000000000000000000000003A0BB0D0000000000000000000000000000000000040367057920692064206900000000000059595243000266E301001FB50942806DD35800000000000000000403E1024368614D79636148205048000044432143000380FC0A001283AC42A66FD3580000000000000000040315040000000000000000000000000000000000000380FC0A00000000000000000000000000000000000503500A03130032270D0058F5C04200000000000000000000000000000000000000000000000000000000050356084461726B2E6E65737400000000000000430003000000000001020000000000000000000000000005031B0353756E6C6F76696E6738320000544F31430003603D0800E926B942AC68D35800000000000000000503F4034C656F6E61726420716E000000442F43430003603D080066669E424568D35800000000000000000503100300000000000000000000000000000000000002CCC60300000000000000000000000000000000000503C60300000000000000000000000000000000000002CCC6030000000000000000000000000000000000050366040000000000000000000000000000000000000380FC0A0000000000000000000000000000000000EC07AC0816C00B05039B046B696D61736F2031323300000048525643000380FC0A00F0A726420D6BD35800000000000000000503EE02436170744368614D616E6F770048525643000380FC0A00CBA19542885CD35800000000000000000503F303566970616B2054483900000000544823430003603D0800C3F558426859D35800000000000000000503F908494E4920436F6D6520544F0000000000430015000000000000000000000000000000000000000005035C036363686F6E6764610000000000595952430003603D0800F628C4426E6CD35800000000000000000503C50342484437370000000000000000444321430003603D0800D34D4A42B85AD35800000000000000000503A5084359383700000000000000000057434E43001804000043000000000000000000000000000000000503AC03466F72596F757575757500000054482D430003603D080052B81E420570D35800000000000000000503390362727A51515100000000000000487532430003603D080014AE9F42A458D358000000000000000005036F086363686F6E6764610000000000595952430018000000430000000000000000000000000000000005033F084461726B2E6E65737400000000000000430003000000000001020000000000000000000000000005031908446520427265636874000000000000004300170000000000000000000000000000000000000000050384036B776331393938000000000000636E5A430003603D080014AE1F42515CD358000000000000000005032108646576616C6431323334353600482D4843000E04000043000000000000000000000000000000000503D3084461726B2E6E65737400000000000000430004000000000001020000000000000000000000000005032004467572696F7573206E69636F0046464143000380FC0A007B141642366BD3580000000000000000050347086C696820686F61672067696100566E4843001600000043000000000000000000000000000000000503A8084C65696E61646500000000000048753243001400000043000000000000000000000000000000000503130148554E42616C617A7300000000487532430003419205007D3F7D428462D35800000000000000000503FB0300000000000000000000000000000000000002CCC60300000000000000000000000000000000000503D502566970616B205448390000000054482343000380FC0A00C3F55842DF59D3580000000000000000050306034C656F6E61726420716E000000442F43430003603D080066669E42AC5ED35800000000000000000503820100000000000000000000000000000000000003BA9D0000000000000000000000000000000000000503D90443756C792030393733000000005A6F4F43000380FC0A0014AE27424261D35800000000000000000503FF084A6564692049726F6E20427500544F3343001900000043000000000000000000000000000000000503B008456E726100000000000000000000000043001600000000000000000000000000000000000000000503650873616B616C3100000000000000487532430013000000430000000000000000000000000000000005036B085A6F206F4F7A2054480000000000000043001100000000000000000000000000000000000000000503B90100000000000000000000000000000000000003D44305000000000000000000000000000000000005038C0869206C6F6E656C790000000000514B31430019000000480000000000000000000000000000000005030F086C656B6F393131000000000000315541430019080000430000000000000000000000000000000005037E04000000000000000000000000000000000000031D690000000000000000000000000000000000000503C4084A504E39393900000000000000000000430012000000000000000000000000000000000000000005039A084461726B2E6E6573740000000000000043000400000000000102000000000000000000000000000503DA034E6F6F6D205448000000000000485256430003603D080000002042F960D35800000000000000000503BF02436176696E204E677579656E00504F5743000380FC0A00E926B1424760D35800000000000000000503AE044F6B616D69204E534E0000000044433243000380FC0A00B29DA3423B71D35800000000000000000503940300000000000000000000000000000000000002CCC60300000000000000000000000000000000000503FE05416C65636B73313131000000004D524443000266E30100294C9A418D61D35800000000000000000503230A0210002E260D000000C84200000000000000000000000000000000000000000000000000000000140377084461726B2E6E6573740000000000000043000200000000000102000000000000000000000000001403090368696C6C363233000000000000636E5A430003603D080014AE57429664D3580000000000000000140386035475646936390000000000000023424C430002CCC603006DE78642A06CD35800000000000000001403FD036D616E7469616E78696E673100636E5A430003603D0800EE7CAF42C368D358000000000000000014038F045369616D33312045500000000044432143000380FC0A00BE9F72429C6ED3580000000000000000140335047920692064206900000000000059595243000210090500D9CE7B42646DD35800000000000000001403580300000000000000000000000000000000000002CCC6030000000000000000000000000000000000EC07AC0816C00B1403DB0100000000000000000000000000000000000003A0BB0D000000000000000000000000000000000014031B086C4174616265796C0000000000547E4F43001900000043000000000000000000000000000000001403FB040000000000000000000000000000000000000380FC0A00000000000000000000000000000000001403F60142484437370000000000000000444321430003A0BB0D005EBA9742F158D358000000000000000014038C024D725265636900000000000000542D4D43000380FC0A00D7A33042B14AD358000000000000000014032F08587844656D6F6E576F6C6658000000004300140000000000000000000000000000000000000000140319010000000000000000000000000000000000000292BC02000000000000000000000000000000000014030D086A7573742073657074656D620000000043001200000000000000000000000000000000000000001403C0095365616E31393836000000000044432143001904000043000000000000000000000000000000001403E109777574746973616B203234310044432143001800000043000000000000000000000000000000001403D0095365616E31393836000000000044432143001904000043000000000000000000000000000000001403D7034B415241424153313936380000444321430002CCC60300E17A0A42AE56D35800000000000000001403F1095365616E31393836000000000044432143001904000043000000000000000000000000000000001403E0084A617A7A333135000000000000444321430019000000430000000000000000000000000000000014031D056F6B626E696300000000000000636E5A43000266E301003D6A6941B25ED358000000000000000014035A0849416D4661726D554E6F46610031756B43000D00000043000000000000000000000000000000001403AE020000000000000000000000000000000000000380FC0A0000000000000000000000000000000000140303034A6F686E6E7943783133000000444332430002CCC60300F62824420D6AD358000000000000000014034C046167756E672030303700000000492E414300030ABB0700CDCC0C428C55D358000000000000000014036E086167756E672030303700000000492E41430011000000430000000000000000000000000000000014035C085468617474696E683030370000000000430012000000000000000000000000000000000000000014035D084675726B616E593700000000000000004300110000000000000000000000000000000000000000140362084461726B2E6E6573740000000000000043000400000000000102000000000000000000000000001403BB0100000000000000000000000000000000000003A0BB0D0000000000000000000000000000000000140347085475646936390000000000000023424C4300190000004300000000000000000000000000000000140345084461726B2E6E65737400000000000000430004000000000001020000000000000000000000000014033E0100000000000000000000000000000000000003505A050000000000000000000000000000000000140361085354414C4B45523839000000003155414300190000004300000000000000000000000000000000140372084461726B2E6E6573740000000000000043000300000000000102000000000000000000000000001403F0084D697374206B696C6C6572000044432143001900000043000000000000000000000000000000001403840100000000000000000000000000000000000002544B0600000000000000000000000000000000001403240300000000000000000000000000000000000003603D0800000000000000000000000000000000001403D1084461726B2E6E6573740000000000000043000300000000000102000000000000000000000000001403F80267676973733230303800000000636E5A43000380FC0A00E17A34423A54D358000000000000000014035E084461726B2E6E657374000000000000004300030000000000010200000000000000000000000000140365084461726B2E6E65737400000000000000430002000000000001020000000000000000000000000014039D084461726B2E6E6573740000000000000043000400000000000102000000000000000000000000001403170A02100030260D00BE368C42000000000000000000000000000000000000000000000000000000001403AB0A03130034270D000000C8420000000000000000000000000000000000000000000000000000000014039F0A03130035270D000000C842000000000000000000000000000000000000000000000000000000001403670A03120079270D000000C842000000000000000000000000000000000000000000000000000000001403DD0A04100097270D0040ABC342000000000000000000000000000000000000000000000000000000001403F40A041300AA270D000000C8420000000000000000000000000000000000000000000000000000000015038A084461726B2E6E65737400000000000000430003000000000001020000000000000000000000000015032E084461726B2E6E6573740000000000000043000400000000000102000000000000000000000000001503C9040000000000000000000000000000000000000380FC0A00000000000000000000000000000000001503ED04595952206B6C0000000000000059595243000380FC0A00AE47B142F671D3580000000000000000D907AC0816840A150334010000000000000000000000000000000000000364340100000000000000000000000000000000001503140100000000000000000000000000000000000002544B06000000000000000000000000000000000015032308594D4A2031323300000000000000000043001200000000000000000000000000000000000000001503DE08546F6E32560000000000000000546F3043001908000043000000000000000000000000000000001503C108426F73734669676874657200005E4C5E430018000000430000000000000000000000000000000015038801000000000000000000000000000000000000028F480000000000000000000000000000000000001503A708646464787878313100000000004D554B4300190000004300000000000000000000000000000000150327086C697069000000000000000000000000430012000000000000000000000000000000000000000015037308536869726C79363638000000005E4C5E430018040000430000000000000000000000000000000015031708436176696E204E677579656E00504F5743001900000043000000000000000000000000000000001503F50100000000000000000000000000000000000002DF4A06000000000000000000000000000000000015035E02484B4E36370000000000000000547E4F43000380FC0A001D5A3C42396BD358000000000000000015038001000000000000000000000000000000000000026D2800000000000000000000000000000000000015032F0A011200A22E0D000000C8420000000000000000000000000000000000000000000000000000000015031D01000000000000000000000000000000000000036F610000000000000000000000000000000000001503B3086B6173747920717565656E000000000043001500000000000000000000000000000000000000001503BA087468656F7A7800000000000000522C4943001300000043000000000000000000000000000000001503CB085261696E426F77206B77000000522C4943001804000043000000000000000000000000000000001503BB084C414B4F4E4500000000000000522C494300170000004300000000000000000000000000000000150354044D617279616E6E3133000000004875324300021009050012831A428052D358000000000000000015033804437265616D20203320313400004E634743000380FC0A007B142E427736D358000000000000000015039A0873746F6E6570776E000000000000000043001100000000000000000000000000000000000000001503E408534678696E676B6F6E00000000636E5A43001804000043000000000000000000000000000000001503E00100000000000000000000000000000000000003A0BB0D00000000000000000000000000000000001503A0046D616E7469616E78696E673100636E5A43000380FC0A00EE7CAF42A668D35800000000000000001503B4086F6B626E696300000000000000636E5A430010000000430000000000000000000000000000000015030808526F6B756E00000000000000004846534300170000004300000000000000000000000000000000150365084461726B2E6E657374000000000000004300050000000000010200000000000000000000000000150346040000000000000000000000000000000000000380FC0A000000000000000000000000000000000015032A0300000000000000000000000000000000000003F03D0000000000000000000000000000000000001503E90100000000000000000000000000000000000002B504010000000000000000000000000000000000150387014B4E4E32303039000000000000777362430003A0BB0D00D9CEA742F04ED35800000000000000001503C40867676973733230303800000000636E5A430019000000430000000000000000000000000000000015034703000000000000000000000000000000000000022AF00200000000000000000000000000000000001503A40100000000000000000000000000000000000003A0BB0D000000000000000000000000000000000015037F086F4F42696E526144696E4F6F0047734443001800000043000000000000000000000000000000001503D4086D616E7469616E78696E673100636E5A430019040000430000000000000000000000000000000015037605466F75724D6F73740000000000485256430003B01E04003373DB41B266D35800000000000000001503AE0A0110007E250D000000C8420000000000000000000000000000000000000000000000000000000015039F0A0213006B260D000000C8420000000000000000000000000000000000000000000000000000000015032B0A021200A8260D00F4CF1D42000000000000000000000000000000000000000000000000000000001503FB084461726B2E6E6573740000000000000043000200000000000102000000000000000000000000003A90000067617264696E3134000000000032554143000403A5F402F1C771D3580000000099000000000000000000000010EC87000050657472696B207300000000004D52444300F003FFA502422B70D358000000006F0C000000000000050000001A4D5900006C656B6F393131000000000000315541430005030F8301E05567D35800000000E90E000000000000000000000C588600007A6F4F20506F6B656D6F6E00005A6F4F4300E302705E03E0DA6FD358000000006D08000000000000050000001A2202AC08170B00477800004B477065654F6E527573696100303E524300CB0120F20383156DD3580000000000150000000000000000000010C98D0000636F6F6C31335448390000000054482343001503066503924C71D35800000000F90300000000000000000000108D8B00004E75205448390000000000000054482343005503B01503C9D670D35800000000D30100000000000000000000076F5500005376656E626F00000000000000527C4C4300B003E2C90223B066D358000000000D10000000000000050000001A7B91000053636F7270696F6E2030310000527C4C430014032904033E1372D35800000000780000000000000000000000105E91000053636F7270696F6E2030310000527C4C430014031A04033E0D72D358000000007B000000000000000000000010F59100004B41524142415331393638000044432143001403D22403212F72D3580000000037000000000000000000000010879000005361646971204D4200000000004B637243005403791403FBD971D35800000000A50100000000000000000000072790000068696C6C363233000000000000636E5A4300250310140324C371D35800000000F7000000000000000000000007488C00004C6164794B69747479436174004443324300440308E3024FFE70D358000000004A020000000000000000000010DC860000626162796769726C31323232006B676E4300960271F203A4F66FD35800000000F00200000000000000000000160C0001045872D358000000000A00AC081814031815033E00AC080C9C280100000000002F0014037B0000000000000000000000000000000000000000000034060000000028B968000000000000000000000000001300AC080F9D280100000000001403F59100000C0001046772D358000000001000130E0100444C6F7264206F6620491100F2030D0000000EF3F9ABF77F000000");
	ParseOfflineDump("p_good");
	//wait for the HTTP queue to finish
	while (IsHTTPQueueEmpty() == 0)
		Sleep(10);
	return;
}

void OnlineScanParsing()
{
	//move the screen, close popups
	LordsMobileControlStartup();

	HttpSendStartup();
	//	HTTPPostData(67, 1, 2, "Tudi", "wib", "sea wolves", 3, 4, 5, 6, 7, 8, 9);

	//parse network packets and intiate a http-post to insert them into the DB
	CreateBackgroundPacketProcessThread();

	//listen to console to send commands to game control
	StartListenConsole();

	// listen to network interface, assemble packets, queue them to the process queue
	StartCapturePackets(1);
}

void main()
{
//	OfflineParsing(); return;
	OnlineScanParsing();

	printf("Waiting for packets to come and process\n. Press 'a' key to exit");
	char AKey = ' ';
	while (AKey != 'a')
		scanf_s("%c", &AKey, sizeof(AKey));

	//shut everything down
	StopCapturePackets();
	StopThreadedPacketParser();
	HttpSendShutdown();
	LordsMobileControlShutdown();
	StopListenConsole();
	printf("Properly shut everything down. Exiting\n");
}
