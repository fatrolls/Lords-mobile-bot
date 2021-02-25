<?php
error_reporting(E_ALL & ~E_NOTICE);
$AttackPower['ii'] = 0.20;
$AttackPower['aa'] = 0.20;
$AttackPower['cc'] = 0.20;
$AttackPower['tt'] = 0.20; // ?

$AttackPower['ic'] = 0.08;
$AttackPower['ci'] = 0.54;
$AttackPower['ia'] = 0.64;	//0.54 ?
$AttackPower['ai'] = 0.04;
$AttackPower['ca'] = 0.08;
$AttackPower['ac'] = 0.54;
$AttackPower['it'] = 0.00;
$AttackPower['ti'] = 0.56;
$AttackPower['at'] = 0.08;
$AttackPower['ta'] = 0.44;
$AttackPower['ct'] = 0.02;
$AttackPower['tc'] = 0.58;

echo"<br>";
unset($A);unset($D);
$A['i'] = 100;
$D['i'] = 100;
print_r($A);echo"<br>";print_r($D);echo"<br>";
echo "Expecting 80 i, 80 i<br>";
AttackSim( $A, $D);
print_r($A);echo"<br>";print_r($D);echo"<br>";

echo"<br>";
unset($A);unset($D);
$A['i'] = 100;
$D['a'] = 100;
print_r($A);echo"<br>";print_r($D);echo"<br>";
echo "Expecting 96 i, 36 a<br>";
AttackSim( $A, $D);
print_r($A);echo"<br>";print_r($D);echo"<br>";

echo"<br>";
unset($A);unset($D);
$A['i'] = 100;
$D['c'] = 100;
print_r($A);echo"<br>";print_r($D);echo"<br>";
echo "Expecting 46 i, 92 c<br>";
AttackSim( $A, $D);
print_r($A);echo"<br>";print_r($D);echo"<br>";

echo"<br>";
unset($A);unset($D);
$A['c'] = 100;
$D['a'] = 100;
print_r($A);echo"<br>";print_r($D);echo"<br>";
echo "Expecting 46 c, 92 a<br>";
AttackSim( $A, $D);
print_r($A);echo"<br>";print_r($D);echo"<br>";

echo"<br>";
unset($A);unset($D);
$A['i'] = 100;
$D['i'] = 50;
$D['a'] = 50;
//50 * 0.2 + 50 * 0.04 = 12 != 9 = 12 * 0.75
//100 * 0.2 + 100 * 0.64 = 84 != 27			100 * 0.2 + 100 * 0.54 = 74 / 2 = 37 => 37 * 0.75 = 27.75
print_r($A);echo"<br>";print_r($D);echo"<br>";
echo "Expecting 91 c, 23 i 50 a<br>";
AttackSim( $A, $D);
print_r($A);echo"<br>";print_r($D);echo"<br>";

echo"<br>";
unset($A);unset($D);
$A['i'] = 100;
$D['i'] = 50;
$D['c'] = 50;
//50 * 0.2 + 50 * 0.54 = 37 != 34 = 37 * 0.95
print_r($A);echo"<br>";print_r($D);echo"<br>";
echo "Expecting 66 c, 27 i 50 a<br>";
AttackSim( $A, $D);
print_r($A);echo"<br>";print_r($D);echo"<br>";

echo"<br>";
unset($A);unset($D);
$A['i'] = 100;
$D['c'] = 50;
$D['a'] = 50;
print_r($A);echo"<br>";print_r($D);echo"<br>";
echo "Expecting 76 c, 38 c 50 a<br>";
AttackSim( $A, $D);
print_r($A);echo"<br>";print_r($D);echo"<br>";

echo"<br>";
unset($A);unset($D);
$A['i'] = 100;
$A['c'] = 100;
$A['a'] = 100;
$D['i'] = 100;
$D['c'] = 100;
$D['a'] = 100;
print_r($A);echo"<br>";print_r($D);echo"<br>";
echo "Expecting 34 i 86 c 100 a, 34 i 86 c 100 a<br>";
AttackSim( $A, $D);
print_r($A);echo"<br>";print_r($D);echo"<br>";

echo "<br><br>Expecting 66 i, 27 i<br>";
unset($A);
unset($D);
$A['i'] = 100;
$D['i'] = 50;
$D['c'] = 50;
AttackSim( $A, $D);
print_r($A);
echo"<br>";
print_r($D);

echo "<br><br>Expecting 81 i, 7 i<br>";
unset($A);
unset($D);
$A['i'] = 99;
$D['i'] = 33;
$D['c'] = 33;
$D['a'] = 33;
//for($i=0;$i<10;$i++)
	AttackSim( $A, $D, $i );
print_r($A);
echo"<br>";
print_r($D);

exit();

function AttackSim(&$A,&$D)
{
	$NA = $A;
	$ND = $D;
	$D = AttackSim1($NA,$ND);
	$A = AttackSim1($ND,$NA);
}

function AttackSim1(&$A,&$D,$AttackerSetup=0,$DefenderSetup=0)
{
	global $AttackPower;
	$ND = $D;
	
	//count number of troop types
	$DefenderCount = 0;
	if($D['i'])
		$DefenderCount++;
	if($D['c'])
		$DefenderCount++;
	if($D['a'])
		$DefenderCount++;
	
	$AttackerCount = 0;
	if($A['i'])
		$AttackerCount++;
	if($A['c'])
		$AttackerCount++;
	if($A['a'])
		$AttackerCount++;
	
	//main troops get a buff
	//non main troops get a debuff
	if($AttackerCount==$DefenderCount)
	{
		$Buff = 1;
		$Debuff = 1;
	}
	if(($AttackerCount==1 && $DefenderCount==2)||($AttackerCount==2 && $DefenderCount==1))
	{
		$Buff = 1.15;
		$Debuff = 0.83;
	}
	if(($AttackerCount==1 && $DefenderCount==3)||($AttackerCount==3 && $DefenderCount==1))
	{
		$Buff = 1.31;
		$Debuff = 0.19;
	}
	// not good enough, somehow even legion 2 is getting hit also
	if($AttackerCount==3 && $DefenderCount==3)
	{
		$Buff = 0.85;
		$Debuff = 0.85;
	}
//echo "AC $AttackerCount, DC $DefenderCount,  buff is $Buff debuff is $Debuff<br>";

	//calculate deads
	if($ND['i']>0)
	{
		$ND['i'] = $ND['i'] - $Buff * $A['i'] * $AttackPower['ii'] - $Debuff * ( $A['a'] * $AttackPower['ai'] + $A['c'] * $AttackPower['ci'] );
	}
	//attack penetrated infantry ?
	if($ND['i']==0 && $ND['c']>0)
	{
		$ND['c'] = $ND['c'] - $Buff * $A['i'] * $AttackPower['ic'] - $Debuff * ( $A['a'] * $AttackPower['ac'] + $A['c'] * $AttackPower['cc'] );
	}
	//attack penetrated both infantry and cav ?
	if($ND['i']==0 && $ND['c']==0 && $ND['a']>0) // meat shield protects archers
	{
		$ND['a'] = $ND['a'] - $Buff * $A['i'] * $AttackPower['ia'] - $Debuff * ( $A['a'] * $AttackPower['aa'] + $A['c'] * $AttackPower['ca'] );
	}
	if($ND['i']==0 && $ND['c']==0 && $ND['a']==0 && $ND['t']>0) // meat shield protects archers
	{
		$ND['t'] = $ND['t'] - $Buff * $A['i'] * $AttackPower['it'] - $Debuff * ( $A['a'] * $AttackPower['at'] + $A['c'] * $AttackPower['ct'] );
	}
		
	return $ND;
}
?>