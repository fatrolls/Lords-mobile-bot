<?php
set_time_limit( 7 * 30 * 60 );
/*
- 25 waves
*/

$UnitStats['i']['d'] = 1.5;
$UnitStats['i']['a'] = 0.5;
$UnitStats['i']['h'] = 113;

$ret=Fight( 13, 0, 0, 0, 13, 0, 0, 0 );
echo "test1 : ".$ret[1]['i']."<br>";
$ret=Fight( 14, 0, 0, 0, 14, 0, 0, 0 );
echo "test1 : ".$ret[1]['i']."<br>";

$BestMatch1 = 9999999999;
$BestMatch2 = 9999999999;
$BestMatch3 = 9999999999;

for( $d=1;$d<150;$d++)
//for( $d=-15;$d<15;$d++)
{
	for( $a=1;$a<500;$a++)
//	for( $a=-15;$a<15;$a++)
	{
		for( $h=1;$h<1000;$h++)
		{
//			$UnitStats['i']['d'] = $d;
//			$UnitStats['i']['a'] = $a;
			$UnitStats['i']['d'] = $d/100;
			$UnitStats['i']['a'] = $a/100;
//			$UnitStats['i']['d'] = 2.0 + (float)$d/(float)10;
//			$UnitStats['i']['a'] = 1.0 + (float)$a/(float)10;
			$UnitStats['i']['h'] = $h;
			$ret1 = Fight( 100, 0, 0, 0, 100, 0, 0, 0 );
			$ret2 = Fight( 14, 0, 0, 0, 14, 0, 0, 0 );
			$ret3 = Fight( 200, 0, 0, 0, 100, 0, 0, 0 );
			if(abs(80 - $ret1[1]['i']) <= $BestMatch1 && abs(13 - $ret2[1]['i']) <= $BestMatch2 && abs(188 - $ret3[1]['i']) <= $BestMatch3 )
			{
				$BestMatch1 = abs(80 - $ret1[1]['i']);
				$BestMatch2 = abs(13 - $ret2[1]['i']);
				$BestMatch3 = abs(188 - $ret3[1]['i']);
				echo "Maybe solution 1: ".$ret1[1]['i']." - ".$UnitStats['i']['d']." - ".$UnitStats['i']['a']." - ".$UnitStats['i']['h']."<br>";
				echo "Maybe solution 2: ".$ret2[1]['i']." - ".$ret2[2]['i']."<br>";
				echo "Maybe solution 3: ".$ret3[1]['i']." - ".$ret3[2]['i']."<br>";
			}
//			else echo "<br>";
//			if( $d==1 && $a==2 && $h==13 )
//				echo "wtf";
		}
	}
}

function Fight( $Inf1, $Rang1, $Cav1, $Treb1, $Inf2, $Rang2, $Cav2, $Treb2)
{
	global $UnitStats;
	$InfHP1 = $Inf1 * $UnitStats['i']['h'];
	$InfHP2 = $Inf2 * $UnitStats['i']['h'];
	for($Cycle=0;$Cycle<25;$Cycle++)
	{
		//right now testing i/i, a/a, c/c fights !
		$InfHP1 = OneHit( $Inf1, $InfHP1, $Inf2, $UnitStats['i']['d'], $UnitStats['i']['a'] );
		$InfHP2 = OneHit( $Inf2, $InfHP2, $Inf1, $UnitStats['i']['d'], $UnitStats['i']['a'] );
		
		$Inf1 = $InfHP1 / $UnitStats['i']['h'];
		$Inf2 = $InfHP2 / $UnitStats['i']['h'];
	}
//	echo "$Inf1 / $Inf2";
	$ret[1]['i'] = $Inf1;
	$ret[2]['i'] = $Inf2;
	return $ret;
}

function OneHit($DefenderCount,$DefenderHP,$AttacketCount,$DefenderDefense,$AttackerAttack)
{
	//survived = defender_count * defender_hp - attacket_count * ( defender_defense - attacker_attack )
	//$survived = $DefenderCount * $DefenderHP - $AttacketCount * ( $DefenderDefense - $AttackerAttack );
	$NewHP = $DefenderHP - $AttacketCount * ( $DefenderDefense - $AttackerAttack );
	/*
25 iterations	
Maybe solution 1: 86.36151943393 - 3.4 - 1.4 - 342
Maybe solution 2: 12.09061272075 - 12.09061272075
Maybe solution 3: 187.38895631677 - 71.695601985014
Maybe solution 1: 86.36151943393 - 3.4 - 2.4 - 171
Maybe solution 2: 12.09061272075 - 12.09061272075
Maybe solution 3: 187.38895631677 - 71.695601985014
*/
	
	//survived = defender_count * defender_hp * defender_defense - attacket_count * attacker_attack
//	$NewHP = $DefenderHP * ( 1 + $DefenderDefense / 100 ) - $AttacketCount * $AttackerAttack;
/*
*/
	
	//survived = defender_count * ( defender_hp + defender_defense ) - attacket_count * attacker_attack
//	$NewHP = $DefenderHP + $DefenderCount * $DefenderDefense - $AttacketCount * $AttackerAttack;
/*
Maybe solution 1: 91.991635491977 - 0.6 - 2.1 - 450
Maybe solution 2: 12.878828968877 - 12.878828968877
Maybe solution 3: 196.05313403487 - 79.921772441062
*/	
	return $NewHP;
}
?>