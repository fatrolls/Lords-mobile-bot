#include <AutoItConstants.au3>

func GetKoPlayer()
	Local $hWnd = WinGetHandle("[Title:KOPLAYER 1.4.1052")
	if( @error ) then
		$hWnd = WinGetHandle("[CLASS:Qt5QWindowIcon]")
	endif
	return $hWnd
endfunc

func ReduceColorPrecision( $color, $Mask = 0 )
	if( $Mask == 0 ) then
		$Mask = 0x00F0F0F0
	endif
	return BitAND( $color, $Mask )
endfunc

func GetKoPlayerAndPos()
	Local $hWnd = GetKoPlayer()
	Local $aPos = WinGetPos( $hWnd ) ; x, y, w, h
	Local $bPos[5]
	$bPos[0] = $aPos[0] + 2; there is a left pannel that can change it's size. Seems like it pinches off the content 2 pixels
	$bPos[1] = $aPos[1] + 38 ; content of the player starts below the menu bar
	$bPos[2] = $aPos[2] - 63 ; Borders
	$bPos[3] = $aPos[3] - 38 - 2 - 1; More borders
	$bPos[4] = $hWnd
	return $bPos
endfunc

; just in case our positioning is not perfect, Pixel getcolor should still work okish
func IsPixelAroundPos( $x, $y, $Color, $Mask = 0, $Radius = 0, $RelativeCords = 0 )
	if( $Radius == 0 ) then
		$Radius = 2
	endif
	if( $Mask == 0 ) then
		$Mask = 0x00F0F0F0
	endif
	if( $RelativeCords <> 0) then
		Local $aPos = GetKoPlayerAndPos()
		$x = $x + $aPos[0];
		$y = $y + $aPos[1];
	endif
	$Color = ReduceColorPrecision( $Color, $Mask )
	for $y2 = $y - $Radius to $y + $Radius
		for $x2 = $x - $Radius to $x + $Radius
			local $col = PixelGetColor( $x2, $y2 )
			$col = ReduceColorPrecision( $col, $Mask )
;			MouseMove( $x2, $y2 )
;			FileWriteLine ( "PixelsAroundMouse.txt", Hex($Color) & "!=" & Hex($col) & " Mask " & Hex($Mask) & " rad " & $Radius )
			if( $col == $Color ) then 
;				FileWriteLine ( "PixelsAroundMouse.txt", "Matched" )
				return 1
			endif
		next
	next
	return 0
endfunc

func LMIsCastleScreen()
	return ( IsPixelAroundPos( 114, 80, 0x00EFB489, 0, 0, 1 ) == 1 )
endfunc

func LMIsRealmScreen()
	return ( IsPixelAroundPos( 115, 81, 0x00DEC152, 0, 0, 1 ) == 1 )
endfunc

; pushing this button will make screen less clutered
func LMIsZoomInButtonVisible()
	Local $aPos = GetKoPlayerAndPos()
	return ( IsPixelAroundPos( $aPos[0] + 18, $aPos[1] + 498, 0x00CAA84D ) == 1 )
endfunc

; used for loading screens
func WaitImageAppear( $ImageName, $X = -1, $Y = -1, $Sleep = 500, $Timout = 3000 )
	if( $x == -1 ) then
		GetCoordFromImageFileName( $ImageName, $x, $y, 0 )
	endif
	local $Pos = ImageIsAt($ImageName, $X, $Y)
	;MsgBox( 64, "", "found at " & $Pos[0] & " " & $Pos[1] & " SAD " & $Pos[2])
	while( $Pos[2] > 32 * 32 * 3 * 10 and $Timout > 0 )
		Sleep( $Sleep ) ; wait for the window to refresh
		$Pos = ImageIsAt($ImageName, $X, $Y)
		$Timout = $Timout - $Sleep
	wend
endfunc

; used for loading screens
func WaitImageDisappear( $ImageName, $X = -1, $Y = -1, $Sleep = 500, $Timout = 3000 )
	if( $x == -1 ) then
		GetCoordFromImageFileName( $ImageName, $x, $y, 0 )
	endif
	local $Pos = ImageIsAt($ImageName, $X, $Y)
	;MsgBox( 64, "", "found at " & $Pos[0] & " " & $Pos[1] & " SAD " & $Pos[2])
	while( $Pos[2] <= 32 * 32 * 3 * 10 and $Timout > 0 )
		Sleep( $Sleep ) ; wait for the window to refresh
		$Pos = ImageIsAt($ImageName, $X, $Y)
		$Timout = $Timout - $Sleep
	wend
endfunc

func ClickButtonIfAvailable( $ImageName, $X = -1, $Y = -1, $Sleep = 500 )
	if( $x == -1 ) then
		GetCoordFromImageFileName( $ImageName, $x, $y )
	endif
	;Local $aPos = GetKoPlayerAndPos()
	Local $InfinitLoopBreak = 3
;	while( IsPixelAroundPos( $aPos[0] + 986, $aPos[1] + 36, 0x00FFBE38 ) == 1 and $InfinitLoopBreak > 0 )
	local $Pos = ImageIsAt($ImageName, $X, $Y)
	;MsgBox( 64, "", "found at " & $Pos[0] & " " & $Pos[1] & " SAD " & $Pos[2])
	while( $Pos[2] == 0 and $InfinitLoopBreak > 0 )
	
		;MouseMove( $aPos[0] + 986, $aPos[1] + 36 )
		;WinActivate( $aPos[4] )
		;MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 986, $aPos[1] + 36, 1, 0 )
		MouseClick( $MOUSE_CLICK_LEFT, $Pos[0] + 16, $Pos[1] + 16, 1, 0 )
		;ControlClick( $aPos[4],"", "", "left", 1, 986, 36)
		;_MouseClickPlus($aPos[4], "left", $aPos[0] + 986, $aPos[1] + 36 )
		;MouseDown($MOUSE_CLICK_LEFT)
		;Sleep(10000)
		;MouseUp($MOUSE_CLICK_LEFT)
		Sleep( $Sleep ) ; wait for the window to refresh
		$InfinitLoopBreak = $InfinitLoopBreak - 1
		$Pos = ImageIsAt($ImageName, $X, $Y)
	wend
endfunc

func CloseLordsMobilePopupWindows()
	ClickButtonIfAvailable("Images/Close_Help_Red_986_37.bmp",986,37)
endfunc

Func _MouseClickPlus($Window, $Button = "left", $X = "", $Y = "", $Clicks = 1)
    MsgBox(1, "", "112333")
    Local $MK_LBUTTON = 0x0001
    Local $WM_LBUTTONDOWN = 0x0201
    Local $WM_LBUTTONUP = 0x0202

    Local $MK_RBUTTON = 0x0002
    Local $WM_RBUTTONDOWN = 0x0204
    Local $WM_RBUTTONUP = 0x0205

    Local $WM_MOUSEMOVE = 0x0200

    Local $i = 0

    Select
        Case $Button = "left"
            $Button = $MK_LBUTTON
            $ButtonDown = $WM_LBUTTONDOWN
            $ButtonUp = $WM_LBUTTONUP
        Case $Button = "right"
            $Button = $MK_RBUTTON
            $ButtonDown = $WM_RBUTTONDOWN
            $ButtonUp = $WM_RBUTTONUP
    EndSelect

    If $X = "" Or $Y = "" Then
        $MouseCoord = MouseGetPos()
        $X = $MouseCoord[0]
        $Y = $MouseCoord[1]
    EndIf

    For $i = 1 To $Clicks
        DllCall("user32.dll", "int", "SendMessage", _
                "hwnd", WinGetHandle($Window), _
                "int", $WM_MOUSEMOVE, _
                "int", 0, _
                "long", _MakeLong($X, $Y))

        DllCall("user32.dll", "int", "SendMessage", _
                "hwnd", WinGetHandle($Window), _
                "int", $ButtonDown, _
                "int", $Button, _
                "long", _MakeLong($X, $Y))
		
		Sleep( 100 )
		
        DllCall("user32.dll", "int", "SendMessage", _
                "hwnd", WinGetHandle($Window), _
                "int", $ButtonUp, _
                "int", $Button, _
                "long", _MakeLong($X, $Y))
    Next
EndFunc  ;==>_MouseClickPlus

Func _MakeLong($LoWord, $HiWord)
    Return BitOR($HiWord * 0x10000, BitAND($LoWord, 0xFFFF))
EndFunc  ;==>_MakeLong

Func GetCoordFromImageFileName( $ImgName, ByRef $x, ByRef $y, $AbsoluteCoord = 0 )
	local $array = StringSplit($ImgName,"_")
	local $resCount = $array[0]
	$x=Int(Number($array[$resCount-1]))
	$y=Int(Number($array[$resCount-0]))
	if( $AbsoluteCoord <> 0 ) then
		Local $aPos = GetKoPlayerAndPos()
		$x=$aPos[0] + $x
		$y=$apos[1] + $y
	endif
endfunc

Func ImageIsAt( $ImgName, $x = -1, $y = -1 )
	; in case the image file name in a common sense format we can extract the coordinate of it
	if( $x == -1 ) then
		GetCoordFromImageFileName( $ImgName, $x, $y )
	endif
	global $dllhandle
	Local $AcceptedMisplaceError = 2
	Local $Radius = 16 + $AcceptedMisplaceError
	Local $aPos = GetKoPlayerAndPos()
	local $x2 = $x + $aPos[0]
	local $y2 = $y + $aPos[1]
	local $result = DllCall( $dllhandle, "NONE", "TakeScreenshot", "int", $x2 - $Radius, "int", $y2 - $Radius, "int", $x2 + $Radius, "int", $y2 + $Radius)
	$result = DllCall( $dllhandle, "NONE", "ApplyColorBitmask", "int", 0x00F0F0F0)
	$result = DllCall( $dllhandle, "str", "ImageSearch_SAD", "str", $ImgName)
	; put back previous screenshot. Maybe we were parsing it
	DllCall( $dllhandle, "str", "CycleScreenshots")
	local $res = SearchResultToVectSingleRes( $result )
	return $res
endfunc

func SearchResultToVectSingleRes( $result )
	local $array = StringSplit($result[0],"|")
	local $resCount = Number( $array[1] )
	;MsgBox( 64, "", "res count " & $resCount )
	local $ret[3]
	$ret[0]=-1
	$ret[1]=-1
	$ret[2]=-1
	if( $resCount > 0 ) then
		$ret[0]=Int(Number($array[2]))
		$ret[1]=Int(Number($array[3]))
		$ret[2]=Int(Number($array[4]))	; SAD
		;MouseMove( $ret[0], $ret[1] );
		;MsgBox( 64, "", "found at " & $ret[0] & " " & $ret[1] & " SAD " & $ret[2])
	endif
	return $ret
endfunc

func SearchResultToVectMultiRes( $result )
	local $array = StringSplit($result[0],"|")
	local $resCount = Number( $array[1] )
	;MsgBox( 64, "", "res count " & $resCount )
	local $ret[3]
	$ret[0]=-1
	$ret[1]=-1
	$ret[2]=-1
    For $i = 0 To $resCount
		$ret[$i * 3 + 0]=Int(Number($array[$i * 3 + 2]))
		$ret[$i * 3 + 1]=Int(Number($array[$i * 3 + 3]))
		$ret[$i * 3 + 2]=Int(Number($array[$i * 3 + 4]))	; SAD
		;MouseMove( $ret[0], $ret[1] );
		;MsgBox( 64, "", "found at " & $ret[0] & " " & $ret[1] & " SAD " & $ret[2])
	next
	return $ret
endfunc

func SearchFoodOnScreen()
	global $dllhandle
	Local $aPos = GetKoPlayerAndPos()
	; get a list of all possible gold locations
	local $result = DllCall( $dllhandle, "NONE", "TakeScreenshot", "int", $aPos[0], "int", $aPos[1], "int", $aPos[0] + $aPos[2], "int", $aPos[1]+$aPos[3])
	$result = DllCall( $dllhandle, "NONE", "ApplyColorBitmask", "int", 0x00F0F0F0)
	$result = DllCall( $dllhandle, "str", "ImageSearch_Multiple_ExactMatch", "str", "Fields_food.bmp")
	; now parse the locations we found on the screen
	local $array = StringSplit($result[0],"|")
	local $resCount = Number( $array[1] )
	;MsgBox( 64, "", "res count " & $resCount )
    For $i = 0 To $resCount
		$x=Int(Number($array[$i * 2 + 1]))
		$y=Int(Number($array[$i * 2 + 2]))
		; open up to check for level and location
		MouseMove( $x, $y );
		;MsgBox( 64, "", "found at " & $ret[0] & " " & $ret[1] & " SAD " & $ret[2])
	next
	
endfunc

func GoToKingdomViewScreen()
	ClickButtonIfAvailable("Images/KingdomViewButton_31_568.bmp",-1,-1, 4000)
	WaitScreenFinishLoading()
endfunc

func ZoomOutKingdomView()
	ClickButtonIfAvailable("Images/ZoomOutKingView_27_482.bmp")
	WaitScreenFinishLoading()
endfunc

func PushCoordDigit( $Digit )
	Local $aPos = GetKoPlayerAndPos()
	if( $Digit == 1 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 870, $aPos[1] + 295, 1, 0 )
	endif
	if( $Digit == 2 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 955, $aPos[1] + 295, 1, 0 )
	endif
	if( $Digit == 3 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 1045, $aPos[1] + 295, 1, 0 )
	endif
	
	if( $Digit == 4 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 870, $aPos[1] + 360, 1, 0 )
	endif
	if( $Digit == 5 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 955, $aPos[1] + 360, 1, 0 )
	endif
	if( $Digit == 6 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 1045, $aPos[1] + 360, 1, 0 )
	endif
	
	if( $Digit == 7 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 870, $aPos[1] + 425, 1, 0 )
	endif
	if( $Digit == 8 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 955, $aPos[1] + 425, 1, 0 )
	endif
	if( $Digit == 9 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 1045, $aPos[1] + 425, 1, 0 )
	endif
	
	if( $Digit == 0 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 890, $aPos[1] + 490, 1, 0 )
	endif

	; allow the window to update
	Sleep(500)
endfunc

func FlipNumber( $Nr )
	Local $ret = 0
	while($Nr > 0)
		local $Digit = mod($Nr,10)
		$ret = $ret * 10 + $Digit
		$Nr = Int( $Nr / 10 )
	wend
	return $ret
endfunc

func CountDigits( $Nr )
	Local $ret = 0
	while($Nr > 0)
		$ret = $ret + 1
		$Nr = Int( $Nr / 10 )
	wend
	return $ret
endfunc

func GetNthDigit( $Nr, $Index )
	$Index = $Index - 1
	for $i = 0 to $Index step 1
		$Nr = int( $Nr / 10 )
	next
	local $Digit = mod($Nr,10)
	return $Digit
endfunc

func EnterCoord( $Coord )
	; just a self reminder how far you can go
	if( $Coord > 510 ) then 
		$Coord = 510
	endif
	
	Local $aPos = GetKoPlayerAndPos()
	Local $DigitCount = CountDigits( $Coord )
	while( $DigitCount > 0 )
		local $Digit = GetNthDigit( $Coord, $DigitCount - 1 )
		PushCoordDigit($Digit)
		$DigitCount = $DigitCount - 1
	wend
	; push the ok button 
	MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 1020, $aPos[1] + 490, 1, 0 )
	; allow the window to update
	Sleep(500)
endfunc

; We presume we on kingdom view screen
func JumpToKingdomCoord( $Kingdom, $x, $y, $IsZoomedOut = 1 )
	Local $aPos = GetKoPlayerAndPos()
	; Open the coord window
	if( $IsZoomedOut == 1 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 700, $aPos[1] + 25, 1, 0 )
	endif
	if( $IsZoomedOut == 0 ) then 
		MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 700, $aPos[1] + 100, 1, 0 )
	endif
	; allow the window to open
	Sleep(500)
	; open edit Kigndom 
	;MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 410, $aPos[1] + 220, 1, 0 )
	; Enter kingdom
	;EnterCoord( $Kingdom )
	; Open Edit X
	MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 650, $aPos[1] + 255, 1, 0 )
	; allow the window to open
	Sleep(500)
	EnterCoord( $x )
	MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 775, $aPos[1] + 255, 1, 0 )
	; allow the window to update it's content
	Sleep(500)
	EnterCoord( $y )
	; click the GO button
	MouseClick($MOUSE_CLICK_LEFT, $aPos[0] + 645, $aPos[1] + 375, 1, 0 )
	; allow the window to update it's content
	WaitScreenFinishLoading()
endfunc

; about 12 coord units / screen
func DragScreenToRight()
	Local $MarginUndragged = 2	; maybe around 50 pixels
	Local $DragLatency = 0 ; might require even 100 to fully drag the screen
	Local $aPos = GetKoPlayerAndPos()
	; this should drag about  tiles if used with speed 9
	MouseMove( $aPos[0] + $aPos[2] - $MarginUndragged, $aPos[1] + $aPos[3] / 2, 0 )
	MouseDown($MOUSE_CLICK_LEFT)
	MouseMove( $aPos[0] - $DragLatency + $MarginUndragged , $aPos[1] + $aPos[3] / 2, 9 )
	MouseUp($MOUSE_CLICK_LEFT)
	
	local $SecondDragSize = 150
	MouseMove( $aPos[0] + $aPos[2] - $MarginUndragged, $aPos[1] + $aPos[3] / 2, 0 )
	MouseDown($MOUSE_CLICK_LEFT)
	MouseMove( $aPos[0] + $aPos[2] - $SecondDragSize , $aPos[1] + $aPos[3] / 2, 9 )
	MouseUp($MOUSE_CLICK_LEFT)	
	
	; make sure latency does not affect our search
	WaitScreenFinishLoading()
endfunc

; it may take variable time for the screen to finish loading
; there should be a spinning circle in the lower left corner. Or maybe the screen will fade from black to colored ...
; if the circle goes away, we can presume the game loaded the screen
func WaitScreenFinishLoading()
 ; repeat taking screenshots until there is no change between the screens
 ; there is a chance that snow wil make our screenshot change all the time
	sleep(1000)
	return;
	global $dllhandle
	Local $Radius = 16
	Local $AntiInfiniteLoop = 10000 ; timeout the function after this amount of mseconds
	Local $aPos = GetKoPlayerAndPos()
	local $x2 = 11 + $aPos[0]
	local $y2 = 12 + $aPos[1]
	local $result = DllCall( $dllhandle, "NONE", "TakeScreenshot", "int", $x2 - $Radius, "int", $y2 - $Radius, "int", $x2 + $Radius, "int", $y2 + $Radius)
	DllCall( $dllhandle, "NONE", "ApplyColorBitmask", "int", 0x00F0F0F0)
	Local $result[3] = ["1","0","0"]
	while $result[0] == '1' and $AntiInfiniteLoop > 0
		Sleep( 100 )
		$AntiInfiniteLoop = $AntiInfiniteLoop - 100
		DllCall( $dllhandle, "NONE", "TakeScreenshot", "int", $x2 - $Radius, "int", $y2 - $Radius, "int", $x2 + $Radius, "int", $y2 + $Radius)
		DllCall( $dllhandle, "NONE", "ApplyColorBitmask", "int", 0x00F0F0F0)
		$result = DllCall( $dllhandle, "NONE", "IsAnythingChanced", "int", 0, "int", 0, "int", 0, "int", 0)
	wend
endfunc

func ParseKingdomMapRegion( $Kingdom = 69, $StartX = 0, $StartY = 0, $EndX = 500, $EndY = 1000, $CallFunctionPerScreen = "SearchFoodOnScreen")
	global $BotIsRunning
	Local $aPos = GetKoPlayerAndPos()
	; search patterns : 
	; - starting from a specific location, we try to increase radius
	; - scan a box and note down locations, than sort / search in locations 
	; - start from a specific location and we box search increasing box size
	; go to the kingdom view screen
	GoToKingdomViewScreen()
	; zoom out the map to see as much as possible
	ZoomOutKingdomView()
	; jump to a specific coord on the map
	for $row=$StartY to $EndY step 10
		JumpToKingdomCoord( $Kingdom, $StartX, $row, 1 )
		Sleep(1000)
		Call( $CallFunctionPerScreen )
		for $col=$StartX to $EndX step 10
			DragScreenToRight()
			Call( $CallFunctionPerScreen )
			if($BotIsRunning == 0) then
				return
			endif
		next
	next
endfunc

func ParseResourceInfo()
	;ClickButtonIfAvailable("Images/Close_resource_popup_853_125.bmp")
	;WaitImageDisappear("Images/Close_resource_popup_853_125.bmp")
	;sleep(200)
	;MsgBox( 64, "", "parsing rss" )
	CloseResourceClick()
endfunc

func SavePlayerInfo($Name,$Might,$Kills,$Guild,$x,$y)
	FileWriteLine( "Players.txt", $Name & "\t" & $Might & "\t" & $Kills & "\t" & $Guild & "\t" & $x & "\t" & $y)
endfunc

func ParseCastleInfo()
	global $dllhandle
	Local $aPos = GetKoPlayerAndPos()
	Local $PopupStartX = 440
	Local $PopupStartY = 200
	Local $PopupEndX = 840
	Local $PopupEndY = 580
	
	; also wait for the text to load up. It seems to have a "fade" effect which kinda messes up our speed
	Local $Timout = 2000
	Local $Sleep = 100
	while( IsPixelAroundPos(566,270,0x00FFFFFF,0,0,1) == 0 and IsPixelAroundPos(569,301,0x00FFFFFF,0,0,1) == 0 and $Timout > 0 )
		Sleep( $Sleep ) ; wait for the window to refresh
		$Timout = $Timout - $Sleep
	wend

	; 661 357 player click
	; land click
	; take screenshot of popup
	DllCall( $dllhandle, "NONE", "TakeScreenshot", "int", $aPos[0] + $PopupStartX, "int", $aPos[1] + $PopupStartY, "int", $aPos[0] + $PopupEndX, "int", $aPos[1] + $PopupEndY)
	;ClickButtonIfAvailable("Images/Close_Kingdom_castle_853_127.bmp")
	;DllCall( $dllhandle, "NONE", "TakeScreenshot", "int", 0 + $PopupStartX, "int", 0 + $PopupStartY, "int", 0 + $PopupWidth, "int", 0 + $PopupHeight)
	;DllCall( $dllhandle, "NONE", "LoadCacheOverScreenshot", "str", "Screenshot_0003_0280_0325.bmp", "int", 0, "int", 0)
	DllCall( $dllhandle,"NONE","SaveScreenshot")
	MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 852, $aPos[1] + 124, 1, 0 )
	Sleep(1500) ; wait for the popup to close
	#cs
	; remove font bleeding
	DllCall( $dllhandle, "NONE", "KeepColorsMinInRegion", "int", 446 - $PopupStartX, "int", 181 - $PopupStartY, "int", 680 - $PopupStartX, "int", 205 - $PopupStartY, "int", 0x31A0AB)
	DllCall( $dllhandle, "NONE", "KeepColorsMinInRegion", "int", 446 - $PopupStartX, "int", 223 - $PopupStartY, "int", 680 - $PopupStartX, "int", 241 - $PopupStartY, "int", 0xA09E9A)
	DllCall( $dllhandle, "NONE", "KeepColorsMinInRegion", "int", 446 - $PopupStartX, "int", 249 - $PopupStartY, "int", 680 - $PopupStartX, "int", 265 - $PopupStartY, "int", 0xA09E9A)
	DllCall( $dllhandle, "NONE", "KeepColorsMinInRegion", "int", 405 - $PopupStartX, "int", 276 - $PopupStartY, "int", 680 - $PopupStartX, "int", 295 - $PopupStartY, "int", 0xA09E9A)
	DllCall( $dllhandle, "NONE", "KeepColorsMinInRegion", "int", 502 - $PopupStartX, "int", 469 - $PopupStartY, "int", 529 - $PopupStartX, "int", 482 - $PopupStartY, "int", 0xA09E9A)
	DllCall( $dllhandle, "NONE", "KeepColorsMinInRegion", "int", 543 - $PopupStartX, "int", 469 - $PopupStartY, "int", 570 - $PopupStartX, "int", 482 - $PopupStartY, "int", 0xA09E9A)
	;DllCall( $dllhandle,"NONE","SaveScreenshot")
	return;	
	; try to read the text from those locations
	Local $Name = DllCall( $dllhandle, "NONE", "OCR_ReadTextLeftToRightSaveUnknownChars", "int", 446, "int", 181, "int", 680, "int", 205) ; player name
	Local $Might = DllCall( $dllhandle, "NONE", "OCR_ReadTextLeftToRightSaveUnknownChars", "int", 446, "int", 223, "int", 680, "int", 241) ; might
	Local $Kills = DllCall( $dllhandle, "NONE", "OCR_ReadTextLeftToRightSaveUnknownChars", "int", 446, "int", 249, "int", 680, "int", 265) ; kill count
	Local $Guild = DllCall( $dllhandle, "NONE", "OCR_ReadTextLeftToRightSaveUnknownChars", "int", 405, "int", 276, "int", 680, "int", 295) ; guild name
	Local $x = DllCall( $dllhandle, "NONE", "OCR_ReadTextLeftToRightSaveUnknownChars", "int", 502, "int", 469, "int", 529, "int", 482) ; x
	Local $y = DllCall( $dllhandle, "NONE", "OCR_ReadTextLeftToRightSaveUnknownChars", "int", 543, "int", 469, "int", 570, "int", 482) ; y
	; our allmighty DB !
	SavePlayerInfo( $Name,$Might,$Kills,$Guild,$x,$y)
	#ce
	; close the popup window
	#cs
	;WaitImageDisappear("Images/Close_Kingdom_castle_853_127.bmp")
	Sleep(2000)
	$Timout = 1000
	while( CloseCastleClick() == 1 and $Timout > 0 )
		Sleep( $Sleep ) ; wait for the window to refresh
		$Timout = $Timout - $Sleep
	wend
	#ce
	;MsgBox( 64, "", "parsing castle" )
endfunc

func IsCastlePopupVisible()
	return ( IsPixelAroundPos( 518, 215, 0x00F3D51E, 0, 0, 1 ) == 1 ) ; there is golden star around VIP level
endfunc

func IsResourcePopupVisible()
;	if( ImageIsAt( "rss_info_660_246.bmp") == 1 ) then
;		return 1
;	endif
	return ( IsPixelAroundPos( 817, 297, 0x00EFC471, 0, 0, 1 ) == 1 ) ; there is a golden 'i' on resource nodes
endfunc

func CloseLandClick()
	if( IsPixelAroundPos( 853, 126, 0x00FFBD36, 0, 0, 1 ) == 1 ) then
		Local $aPos = GetKoPlayerAndPos()
		MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 853, $aPos[1] + 126, 1, 0 )
		Sleep(500)
		return 1
	endif
	return 0
endfunc

func CloseResourceClick()
	if( IsPixelAroundPos( 853, 127, 0x00FFBD36, 0, 1 ) == 1 ) then
		Local $aPos = GetKoPlayerAndPos()
		MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 853, $aPos[1] + 127, 1, 0 )
		Sleep(1500)
		return 1
	endif
	return 0
endfunc

func CloseCastleClick()
	if( IsPixelAroundPos( 852, 124, 0x00FFBE39, 0, 1 ) == 1 ) then
		Local $aPos = GetKoPlayerAndPos()
		MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 852, $aPos[1] + 124, 1, 0 )
		Sleep(500)
		return 1
	endif
	return 0
endfunc

func CloseLargeScreenClick()
	if( IsPixelAroundPos( 1255, 43, 0x00FFBE38, 0, 0, 1 ) == 1 ) then
		Local $aPos = GetKoPlayerAndPos()
		MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 1255, $aPos[1] + 43, 1, 0 )
		Sleep(500)
		return 1
	endif
	return 0
endfunc

func CloseArmyClick()
	Local $Pos = GetKoPlayerAndPos()
	if( IsPixelAroundPos( 819, 431, 0x00FFBA31, 0, 1 ) == 1 ) then
		Local $aPos = GetKoPlayerAndPos()
		MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 819, $aPos[1] + 431, 1, 0 )
		Sleep(500)
		return 1
	endif
	return 0
endfunc

func CloseRallyAttackBattleHallClick()
	if( IsPixelAroundPos( 853, 118, 0x00FFBE39, 0, 1 ) == 1 ) then
		Local $aPos = GetKoPlayerAndPos()
		MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 853, $aPos[1] + 118, 1, 0 )
		Sleep(500)
		return 1
	endif
	return 0
endfunc

func CloseScoutClick()
	if( IsPixelAroundPos( 852, 119, 0x00FFBD37, 0, 1 ) == 1 ) then
		Local $aPos = GetKoPlayerAndPos()
		MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 852, $aPos[1] + 119, 1, 0 )
		Sleep(500)
		return 1
	endif
	return 0
endfunc

func WaitPopupDataLoad()
	; wait fot the popup to appear
	;WaitImageAppear( "Images/Close_Kingdom_castle_853_127.bmp" )
	Local $Timout = 1000
	Local $Sleep = 100
	while( IsPixelAroundPos(852,126,0x00FFBD36,0,0,1) == 0 and $Timout > 0 )
		Sleep( $Sleep ) ; wait for the window to refresh
		$Timout = $Timout - $Sleep
	wend
endfunc

func ParsePopupInfo()
	Local $WrongScreenOpen = CloseLargeScreenClick() + CloseArmyClick() + CloseRallyAttackBattleHallClick() + CloseScoutClick()
	
	if( $WrongScreenOpen > 0 ) then
		return
	endif
	
	WaitPopupDataLoad()
	
	; is it a castle ?
	if( IsResourcePopupVisible() ) then
		ParseResourceInfo()
	elseif ( IsCastlePopupVisible() ) then
		ParseCastleInfo()	; could check for VIP icon for example
	else
		sleep( 200 ) ; should wait for whatever image we clicked on to dissapear
	endif

	CloseResourceClick()
endfunc

func ExtractPlayerNamesCordsMightFromKingdomScreen()
	global $dllhandle
	global $BotIsRunning
	Local $aPos = GetKoPlayerAndPos()
	Local $result;
	Local $TurfJumpIconSize = 80
	; take screenshot of kingdom view
	DllCall( $dllhandle, "NONE", "TakeScreenshot", "int", $aPos[0] + $TurfJumpIconSize, "int", $aPos[1] + $TurfJumpIconSize, "int", $aPos[0] + $aPos[2] - $TurfJumpIconSize, "int", $aPos[1] + $aPos[3] - $TurfJumpIconSize)
;DllCall( $dllhandle,"NONE","SaveScreenshot")	
	; remove water zones as they are very similar to player level gradient
	DllCall( $dllhandle,"NONE","SetGradientToColor", "int", 0xA59B63, "FLOAT", 0.162, "int", 0x00FFFFFF)
;DllCall( $dllhandle,"NONE","SaveScreenshot")	
	; remove most content. Leave only resource and player level tags on the sreen
	$result = DllCall( $dllhandle,"NONE","KeepGradient", "int", 0x00946D21, "FLOAT", 0.4)
;DllCall( $dllhandle,"NONE","SaveScreenshot")
	; search for all the "Level" tags in the screen
	$result = DllCall( $dllhandle, "str", "ImageSearch_Multipass_PixelCount", "int", 0, "int", 60, "int", 35, "int", 5, "int", 34, "int", 21)
;MsgBox( 64, "", "result: " & $result[0])
	; click on all the tags
	; now parse the locations we found on the screen
	local $array = StringSplit($result[0],"|")
	local $resCount = Number( $array[1] ) - 1
	if($resCount>50) then
		DllCall( $dllhandle,"NONE","SaveScreenshot")	; something might have went wrong. Save it for later inspectation
		return
	endif
	;MsgBox( 64, "", "res count " & $resCount )
    For $i = 0 To $resCount
		local $x=Int(Number($array[$i * 2 + 2]))
		local $y=Int(Number($array[$i * 2 + 3]))
		; open up to check for level, location, occupier
		;MouseMove( $x, $y );
		;MsgBox( 64, "", "found at " & $x & " " & $y)
		;MouseClick( $MOUSE_CLICK_LEFT, $x - 32, $y - 32, 1, 0 )
		MouseClick( $MOUSE_CLICK_LEFT, $x, $y, 1, 0 ) ; could click outside of the screen if we use -
		; avoid mouse causing overlay popups
		MouseMove( 0, 0, 0 )
		;sleep( 200 ) ; these sleeps should be converted to some dynamic waits. Not today ...
		ParsePopupInfo()
		if($BotIsRunning == 0) then
			return
		endif		
	next
endfunc