#include "Defense.au3"
#RequireAdmin

Opt("PixelCoordMode",1)
Opt("MustDeclareVars", 1)
HotKeySet("1", "ExitBot")
HotKeySet("{Esc}", "ExitBot")
HotKeySet("3", "TempFunc")

global $BotIsRunning = 1
global $dllhandle = DllOpen ( "ImageSearchDLL_x86.dll" )

;Load OCR related data
DllCall( $dllhandle, "NONE", "OCR_LoadFontsFromDir", "str", "OCRFonts", "str", "KCM_")

while( $BotIsRunning == 1)
	Sleep(1000)
wend

DllClose ( $dllhandle )

exit

func ExitBot()
	global $BotIsRunning = 0
endfunc

func TempFunc()
#cs
	;MsgBox( 64, "", "Digitcount " & CountDigits(1234 ) & " " & GetNthDigit(1234,0) &"-" & GetNthDigit(1234,1) &"-" & GetNthDigit(1234,2) &"-")
	;JumpToKingdomCoord( 67, 123, 456 )
	;JumpToKingdomCoord( 67, 789, 10 )
	local $x
	local $y
	GetCoordFromImageFileName( "Images/Close_Help_Red_986_37.bmp", $x, $y )
	MsgBox( 64, "", "res count " & $x & " " & $y )
#ce
	;ParseKingdomMapRegion(69,0,0,37,10,"ScreenshotCleanRegion");
	;ExtractPlayerNamesCordsMightFromKingdomScreen()
	;ParseKingdomMapRegion(69,0,30,37,37,"ExtractPlayerNamesCordsMightFromKingdomScreen");
	ParseKingdomMapRegion(69,10,27,500,1000,"ExtractPlayerNamesCordsMightFromKingdomScreenDLL");
endfunc

func ExtractPlayerNamesCordsMightFromKingdomScreenDLL()
	global $dllhandle
	DllCall( $dllhandle, "NONE", "CaptureVisibleScreenGetPlayerLabels")
endfunc

func ScreenshotCleanRegion()
	global $dllhandle
	Local $aPos = GetKoPlayerAndPos()
	Local $TurfJumpIconSize = 80
	; take screenshot of kingdom view
	DllCall( $dllhandle, "NONE", "TakeScreenshot", "int", $aPos[0] + $TurfJumpIconSize, "int", $aPos[1] + $TurfJumpIconSize, "int", $aPos[0] + $aPos[2] - $TurfJumpIconSize, "int", $aPos[1] + $aPos[3] - $TurfJumpIconSize)
	DllCall( $dllhandle,"NONE","SaveScreenshot")
endfunc