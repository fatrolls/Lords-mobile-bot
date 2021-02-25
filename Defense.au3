#include "CommonFunctions.au3"

func DebugIncommingAttack( $x, $y, $PixelColor, $PixelColorRef )
;	FileWriteLine ( "Defense_Debug.txt", "Pixel at [" & $x & "," & $y & "]=" & Hex( $PixelColor ) & " Expecting " & Hex( $PixelColorRef ) );
endfunc

func IsIncommingAttack()
	; find koplayer
	Local $aPos = GetKoPlayerAndPos()
	Local $Width = $aPos[2]
	Local $Height = $aPos[3]
	Local $hWnd = $aPos[4]
	
	; check if we have almost same red color in first colum
	Local $FirstPixelColor = PixelGetColor( $aPos[0] + $Width / 4, $aPos[1] )
	$FirstPixelColor = ReduceColorPrecision( $FirstPixelColor )
	for $i = int($Width/4) to ($Width/4 + $Width/2) step 3 
		Local $CurPixelColorX = PixelGetColor( $aPos[0] + $i, $aPos[1] )
		$CurPixelColorX = ReduceColorPrecision( $CurPixelColorX )
		if( $CurPixelColorX <> $FirstPixelColor ) then
			return 0
		endif
		DebugIncommingAttack( $aPos[0] + $i, $aPos[1], $CurPixelColorX, $FirstPixelColor )
	next
	
	; check if we have almost same red color in first row
	$FirstPixelColor = PixelGetColor( $aPos[0], $aPos[1] + $Height / 4, $hWnd )
	$FirstPixelColor = ReduceColorPrecision( $FirstPixelColor )
	for $i = int($Height/4) to ($Height/4 + $Height/2) step 3 
		Local $CurPixelColorY = PixelGetColor( $aPos[0], $aPos[1] + $i, $hWnd )
		$CurPixelColorY = ReduceColorPrecision( $CurPixelColorY )
		if( $CurPixelColorY <> $FirstPixelColor ) then
			return 0
		endif
		DebugIncommingAttack( $aPos[0] + $i, $aPos[1], $CurPixelColorX, $FirstPixelColor )
	next
	
	; if we got here, it means we have an incomming attack
	return 1
endfunc

func DebugHaveShieldActive( $x, $y, $PixelColor, $PixelColorRef )
;	FileWriteLine ( "Defense_Debug.txt", "Pixel at [" & $x & "," & $y & "]=" & Hex( $PixelColor ) & " Expecting " & Hex( $PixelColorRef ) );
endfunc

func HaveShieldActive()

	Local $aPos = GetKoPlayerAndPos()
	
	; just in case something else got opened
	CloseLordsMobilePopupWindows()
	
	; click the zoom in button
	if( LMIsCastleScreen() == 0 and LMIsCastleScreen() == 0 and LMIsZoomInButtonVisible() == 0 ) then
		MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 18, $aPos[1] + 498, 1, 0 )
		Sleep( 1000 )
	endif
	MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 1000, $aPos[1] + 210, 1, 0 )
	Sleep( 1000 )
		
	; open buff list window
	MouseClick( $aPos[0] + 1000, $aPos[0] + 200 )
	Sleep( 1000 )
	
	; valid when we do not yet own battle furry
	Local $ShieldInfoPos = PixelSearch( $aPos[0] + 210, $aPos[1] + 195, $aPos[0] + 340, $aPos[1] + 215, 0x0000FF30 )
	if @error  then
		$ShieldInfoPos = PixelSearch( $aPos[0] + 210, $aPos[1] + 300, $aPos[0] + 340, $aPos[1] + 310, 0x0000FF30 )
		if @error  then
			; cleanup
			CloseLordsMobilePopupWindows()
			; click the zoom out button ( if we were on the realm screen )
			MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 18, $aPos[1] + 498, 1, 0 )
MsgBox(64, "", "no" )
			return 0
		endif
	endif
	
	; cleanup
	CloseLordsMobilePopupWindows()
	Sleep( 1000 )
	
	; click the zoom out button ( if we were on the realm screen )
	MouseClick( $MOUSE_CLICK_LEFT, $aPos[0] + 18, $aPos[1] + 498, 1, 0 )
	Sleep( 1000 )
	
MsgBox(64, "", "yes" )
	
	return 1
endfunc

func ShieldIfAttacked()
	; no need to shield
	if( IsIncommingAttack() == 0 ) then 
		return 0
	endif

	; no need to reshield
	if( HaveShieldActive() == 1 ) then
		return 0
	endif
	
	; find shield button
	
	; find shield
	
	; click shield
	
	return 1
endfunc