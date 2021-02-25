<?php
set_time_limit( 3 * 60 );
ini_set('memory_limit','640M');
/*
100i/100i=80i/80i
200i/200i=156i/156i -> kp 0.22
300i/300i=236i/236i    -> kp 0.213333
400i/400i=312i/312i -> kp 0.22
1000i/1000i=780i/780i
150i/100i=134i/64i
200i/100i=188i/48i
250i/100i=242i/32i
100i/50i,50c=68i/29i,50c
100i/50c=79i/38c
100i/50i=95i/26i
100i/100a=96i/48a
100i/100c=48i/92c
100a/100c=92a/52c
*/
//$DefenderSurvive = combat( $Attacker, $Defender );
$coeffs=array();
FindCoefsInitial(80,100,100); //10,0,2,0
FindCoefsRefine(156,200,200,0);
//FindCoefsRefine(236,300,300,0);
FindCoefsRefine(312,400,400,0);
FindCoefsRefine(134,150,100,0);
FindCoefsRefine(188,200,100,0);
FindCoefsRefine(242,250,100,1);
echo "done ".Combat(100,100,10,0,2,0);

function IsGoodEnougCandidate($Cur,$Exp)
{
	if( $Cur >= $Exp * 0.8 && $Cur <= $Exp * 1.2 )
		return 1;
	if( $Cur / 10 >= $Exp * 0.8 && $Cur / 10 <= $Exp * 1.2 )
		return 1;
	if( $Cur / 100 >= $Exp * 0.8 && $Cur / 100 <= $Exp * 1.2 )
		return 1;
	return 0;
}

function FindCoefsRefine($DefenderSurvive, $Defender, $Attacker, $Print)
{
	global $coeffs, $coeffc;
//$DefenderSurvive = $Defender - $DefenderSurvive;	
	$TroopRatio = $Defender/$Attacker;
	$KillPower = (($Defender-$DefenderSurvive)/$Attacker);
	if($TroopRatio!=1)
		$TroopRatio = $TroopRatio * 0.916; // 0.916 at 1.5 and 2
	$KillPowerCompensated = $KillPower * $TroopRatio;
echo "Kill power now is : $KillPower ( compensated $KillPowerCompensated). Troop ratio is $TroopRatio. Survived : $DefenderSurvive<br>";
	$coeffc2 = 0;
	for($i=0;$i<$coeffc;$i++)
	{
		$c1 = $coeffs[$i][0]*$TroopRatio;
		$c2 = $coeffs[$i][1]*$TroopRatio;
		$c3 = $coeffs[$i][2];
		$c4 = $coeffs[$i][3];
		$s = Combat($Attacker, $Defender, $c1, $c2, $c3, $c4);
		if(IsGoodEnougCandidate($s,$DefenderSurvive))
		{
			if($Print)
				echo "Close match '$s' for $DefenderSurvive, $Attacker, $Defender - $c1,$c2,$c3,$c4<br>";
			$coeffs2[$coeffc2]=$coeffs[$i];
			$coeffc2++;
		}
	}
	echo "refined coefficient difference count $coeffc / $coeffc2<br>";
	unset($coeffs);
	$coeffs = $coeffs2;
	$coeffc = $coeffc2;
}

function FindCoefsInitial($DefenderSurvive, $Attacker, $Defender)
{
	global $coeffs, $coeffc;
//$DefenderSurvive = $Defender - $DefenderSurvive;
	$coeffc = 0;
	$limit = 50;
	for($c1=-$limit;$c1<$limit;$c1++)
		for($c2=-$limit;$c2<$limit;$c2++)
			for($c3=-$limit;$c3<$limit;$c3++)
				for($c4=-$limit;$c4<$limit;$c4++)
				{
					$s = Combat($Attacker, $Defender, $c1, $c2, $c3, $c4);
					if(IsGoodEnougCandidate($s,$DefenderSurvive))
					{
//						echo "Close match '$s' for $DefenderSurvive, $Attacker, $Defender - $c1,$c2,$c3,$c4<br>";
						$coeffs[$coeffc][0]=$c1;
						$coeffs[$coeffc][1]=$c2;
						$coeffs[$coeffc][2]=$c3;
						$coeffs[$coeffc][3]=$c4;
						$coeffc++;
					}
				}
}

function Combat($attacker, $defender, $CoefD1, $CoefD2, $CoefA1, $CoefA2)
{
	$Survive = $CoefD1 * $defender + $CoefD2 * $defender * $defender - ( $CoefA1 * $attacker + $CoefA2 * $attacker * $attacker );
	return $Survive;
}