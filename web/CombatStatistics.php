For now this is a static table. When i have enough data i will turn it into a combat simulator<br>
Below data contains ZERO combat stat changers. Infantry phalanx setup.<br>
<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
$cnt = 0;

$struct["A"]="Infantry";
$struct["AIC"]=100;
$struct["AAC"]=0;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Infantry";
$struct["DIC"]=100;
$struct["DAC"]=0;
$struct["DCC"]=0;
$struct["DTC"]=0;

$struct["AICD"]=20;
$struct["AACD"]=0;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=20;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Infantry";
$struct["AIC"]=100;
$struct["AAC"]=0;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Cavalry";
$struct["DIC"]=0;
$struct["DAC"]=0;
$struct["DCC"]=100;
$struct["DTC"]=0;

$struct["AICD"]=54;
$struct["AACD"]=0;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=0;
$struct["DCCD"]=8;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Infantry";
$struct["AIC"]=100;
$struct["AAC"]=0;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Archer";
$struct["DIC"]=0;
$struct["DAC"]=100;
$struct["DCC"]=0;
$struct["DTC"]=0;

$struct["AICD"]=4;
$struct["AACD"]=0;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=64;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Infantry";
$struct["AIC"]=100;
$struct["AAC"]=0;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Trebuchet";
$struct["DIC"]=0;
$struct["DAC"]=0;
$struct["DCC"]=0;
$struct["DTC"]=100;

$struct["AICD"]=0;
$struct["AACD"]=0;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=56;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Archer";
$struct["AIC"]=0;
$struct["AAC"]=100;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Cavalry";
$struct["DIC"]=0;
$struct["DAC"]=0;
$struct["DCC"]=100;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=8;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=0;
$struct["DCCD"]=54;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Archer";
$struct["AIC"]=0;
$struct["AAC"]=100;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Archer";
$struct["DIC"]=0;
$struct["DAC"]=100;
$struct["DCC"]=0;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=20;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=20;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Archer";
$struct["AIC"]=0;
$struct["AAC"]=100;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Trebuchet";
$struct["DIC"]=0;
$struct["DAC"]=0;
$struct["DCC"]=0;
$struct["DTC"]=100;

$struct["AICD"]=0;
$struct["AACD"]=8;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=44;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Cavalry";
$struct["AIC"]=0;
$struct["AAC"]=0;
$struct["ACC"]=100;
$struct["ATC"]=0;
$struct["D"]="Cavalry";
$struct["DIC"]=0;
$struct["DAC"]=0;
$struct["DCC"]=100;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=0;
$struct["ACCD"]=25;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=0;
$struct["DCCD"]=25;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Cavalry";
$struct["AIC"]=0;
$struct["AAC"]=0;
$struct["ACC"]=100;
$struct["ATC"]=0;
$struct["D"]="Trebuchet";
$struct["DIC"]=0;
$struct["DAC"]=0;
$struct["DCC"]=0;
$struct["DTC"]=100;

$struct["AICD"]=0;
$struct["AACD"]=0;
$struct["ACCD"]=2;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=58;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Archer ";
$struct["AIC"]=0;
$struct["AAC"]=200;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Infantry";
$struct["DIC"]=200;
$struct["DAC"]=0;
$struct["DCC"]=0;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=128;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=12;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Cavalry ";
$struct["AIC"]=0;
$struct["AAC"]=0;
$struct["ACC"]=200;
$struct["ATC"]=0;
$struct["D"]="Infantry";
$struct["DIC"]=200;
$struct["DAC"]=0;
$struct["DCC"]=0;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=0;
$struct["ACCD"]=16;
$struct["ATCD"]=0;
$struct["DICD"]=108;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Archer ";
$struct["AIC"]=0;
$struct["AAC"]=200;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Cavalry";
$struct["DIC"]=0;
$struct["DAC"]=0;
$struct["DCC"]=200;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=16;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=0;
$struct["DCCD"]=108;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Infantry ";
$struct["AIC"]=100;
$struct["AAC"]=0;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Infantry + Archer";
$struct["DIC"]=50;
$struct["DAC"]=50;
$struct["DCC"]=0;
$struct["DTC"]=0;

$struct["AICD"]=9;
$struct["AACD"]=0;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=27;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Infantry ";
$struct["AIC"]=100;
$struct["AAC"]=0;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Cavalry + Archer";
$struct["DIC"]=0;
$struct["DAC"]=50;
$struct["DCC"]=50;
$struct["DTC"]=0;

$struct["AICD"]=24;
$struct["AACD"]=0;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=0;
$struct["DCCD"]=12;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Infantry ";
$struct["AIC"]=100;
$struct["AAC"]=0;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Infantry + Cavalry";
$struct["DIC"]=50;
$struct["DAC"]=0;
$struct["DCC"]=50;
$struct["DTC"]=0;

$struct["AICD"]=34;
$struct["AACD"]=0;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=23;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Cavalry ";
$struct["AIC"]=0;
$struct["AAC"]=0;
$struct["ACC"]=100;
$struct["ATC"]=0;
$struct["D"]="Infantry + Archer";
$struct["DIC"]=50;
$struct["DAC"]=50;
$struct["DCC"]=0;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=0;
$struct["ACCD"]=27;
$struct["ATCD"]=0;
$struct["DICD"]=50;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Cavalry ";
$struct["AIC"]=0;
$struct["AAC"]=0;
$struct["ACC"]=100;
$struct["ATC"]=0;
$struct["D"]="Infantry + Cavalry";
$struct["DIC"]=50;
$struct["DAC"]=0;
$struct["DCC"]=50;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=0;
$struct["ACCD"]=13;
$struct["ATCD"]=0;
$struct["DICD"]=24;
$struct["DACD"]=0;
$struct["DCCD"]=16;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Cavalry ";
$struct["AIC"]=0;
$struct["AAC"]=0;
$struct["ACC"]=100;
$struct["ATC"]=0;
$struct["D"]="Cavalry + Archer";
$struct["DIC"]=0;
$struct["DAC"]=50;
$struct["DCC"]=50;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=0;
$struct["ACCD"]=36;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=0;
$struct["DCCD"]=24;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Archer ";
$struct["AIC"]=0;
$struct["AAC"]=100;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Infantry + Archer";
$struct["DIC"]=50;
$struct["DAC"]=50;
$struct["DCC"]=0;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=38;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=8;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Archer ";
$struct["AIC"]=0;
$struct["AAC"]=100;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Infantry + Cavalry";
$struct["DIC"]=50;
$struct["DAC"]=0;
$struct["DCC"]=50;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=33;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=8;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Archer ";
$struct["AIC"]=0;
$struct["AAC"]=100;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Archer + Cavalry";
$struct["DIC"]=0;
$struct["DAC"]=50;
$struct["DCC"]=50;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=10;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=0;
$struct["DACD"]=0;
$struct["DCCD"]=50;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Infantry + Cavalry + Archer";
$struct["AIC"]=100;
$struct["AAC"]=100;
$struct["ACC"]=100;
$struct["ATC"]=0;
$struct["D"]="Infantry + Cavalry + Archer";
$struct["DIC"]=100;
$struct["DAC"]=100;
$struct["DCC"]=100;
$struct["DTC"]=0;

$struct["AICD"]=66;
$struct["AACD"]=0;
$struct["ACCD"]=14;
$struct["ATCD"]=0;
$struct["DICD"]=66;
$struct["DACD"]=0;
$struct["DCCD"]=14;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Infantry ";
$struct["AIC"]=99;
$struct["AAC"]=0;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Infantry + Cavalry + Archer";
$struct["DIC"]=33;
$struct["DAC"]=33;
$struct["DCC"]=33;
$struct["DTC"]=0;

$struct["AICD"]=18;
$struct["AACD"]=0;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=26;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Cavalry ";
$struct["AIC"]=0;
$struct["AAC"]=0;
$struct["ACC"]=99;
$struct["ATC"]=0;
$struct["D"]="Infantry + Cavalry + Archer";
$struct["DIC"]=33;
$struct["DAC"]=33;
$struct["DCC"]=33;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=0;
$struct["ACCD"]=23;
$struct["ATCD"]=0;
$struct["DICD"]=16;
$struct["DACD"]=0;
$struct["DCCD"]=17;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Archer ";
$struct["AIC"]=0;
$struct["AAC"]=99;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Infantry + Archer + Cavalry";
$struct["DIC"]=33;
$struct["DAC"]=33;
$struct["DCC"]=33;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=24;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=9;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Infantry + Archer ";
$struct["AIC"]=51;
$struct["AAC"]=51;
$struct["ACC"]=0;
$struct["ATC"]=0;
$struct["D"]="Infantry + Archer + Cavalry";
$struct["DIC"]=34;
$struct["DAC"]=34;
$struct["DCC"]=34;
$struct["DTC"]=0;

$struct["AICD"]=26;
$struct["AACD"]=0;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=14;
$struct["DACD"]=0;
$struct["DCCD"]=0;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Infantry + Cavalry ";
$struct["AIC"]=51;
$struct["AAC"]=0;
$struct["ACC"]=51;
$struct["ATC"]=0;
$struct["D"]="Infantry + Archer + Cavalry";
$struct["DIC"]=34;
$struct["DAC"]=34;
$struct["DCC"]=34;
$struct["DTC"]=0;

$struct["AICD"]=23;
$struct["AACD"]=0;
$struct["ACCD"]=0;
$struct["ATCD"]=0;
$struct["DICD"]=22;
$struct["DACD"]=0;
$struct["DCCD"]=7;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;

$struct["A"]="Cavalry + Archer ";
$struct["AIC"]=0;
$struct["AAC"]=51;
$struct["ACC"]=51;
$struct["ATC"]=0;
$struct["D"]="Infantry + Archer + Cavalry";
$struct["DIC"]=34;
$struct["DAC"]=34;
$struct["DCC"]=34;
$struct["DTC"]=0;

$struct["AICD"]=0;
$struct["AACD"]=0;
$struct["ACCD"]=23;
$struct["ATCD"]=0;
$struct["DICD"]=18;
$struct["DACD"]=0;
$struct["DCCD"]=3;
$struct["DTCD"]=0;
$StatisticsList[$cnt++] = $struct;
//1 Infantry kills 0.08 Cavalry
//1 Infantry kills 0.64 Archer
//1 Infantry kills 0.20 Infantry
//1 Archer kills 0.06 Infantry
//1 Archer kills 0.20 Archer
//1 Archer kills 0.54 Cavalry
//1 Cavalry kills 0.54 Infantry
//1 Cavalry kills 0.08 Archer
//1 Cavalry kills 0.20 Cavalry
//100i / 50a + 50c -> (3+27)i / 8c
//						24i / 12c
/*
2.64 + 21.12 + 6.6 = 30.36a
5.94i
$AttackPower['ii'] = 0.20;
$AttackPower['ia'] = 0.64;
$AttackPower['ic'] = 0.08;
$AttackPower['ai'] = 0.06;
$AttackPower['aa'] = 0.20;
$AttackPower['ac'] = 0.54;
$AttackPower['ci'] = 0.54;
$AttackPower['ca'] = 0.08;
$AttackPower['cc'] = 0.20;

$divisor = 3.69;
foreach($AttackPower as $key => $val)
	$AttackPower[$key] = $val / $divisor;

$A['i'] = 100;
$D['i'] = 50;
$D['c'] = 50;
for($i=0;$i<4;$i++)
	AttackSim( $A, $D, $i );
print_r($A);
echo"<br>";
print_r($D);
*/
function AttackSim(&$A,&$D,$round)
{
	global $AttackPower;
	$NA = $A;
	$ND = $D;
	//calculate deads
	if($A['i']>0)
		$NA['i'] = $NA['i'] - $D['i'] * $AttackPower['ii'] - $D['a'] * $AttackPower['ai'] - $D['c'] * $AttackPower['ci'];
//		$NA['i'] = $NA['i'] - $D['i'] * $AttackPower['ii'] - $D['a'] * $AttackPower['ai'] - 0.887 * $D['c'] * $AttackPower['ci'];
	else if($A['c']>0)
		$NA['c'] = $NA['c'] - $D['i'] * $AttackPower['ic'] - $D['a'] * $AttackPower['ac'] - $D['c'] * $AttackPower['cc'];
	else if($A['i']==0 && $A['c']==0 && $A['a']>0)	// meat shield protects archers
		$NA['a'] = $NA['a'] - $D['i'] * $AttackPower['ia'] - $D['a'] * $AttackPower['aa'] - $D['c'] * $AttackPower['ca'];
	
	if($D['i']>0)
		$ND['i'] = $ND['i'] - $A['i'] * $AttackPower['ii'] - $A['a'] * $AttackPower['ai'] - $A['c'] * $AttackPower['ci'];
//		$ND['i'] = $ND['i'] - 1.15 * $A['i'] * $AttackPower['ii'] - $A['a'] * $AttackPower['ai'] - $A['c'] * $AttackPower['ci'];
	else if($D['c']>0)
		$ND['c'] = $ND['c'] - $A['i'] * $AttackPower['ic'] - $A['a'] * $AttackPower['ac'] - $A['c'] * $AttackPower['cc'];
	else if($D['i']==0 && $D['c']==0 && $D['a']>0) // meat shield protects archers
		$ND['a'] = $ND['a'] - $A['i'] * $AttackPower['ia'] - $A['a'] * $AttackPower['aa'] - $A['c'] * $AttackPower['ca'];
	
	$A = $NA;
	$D = $ND;
}
?>						 
<table border=1>
	<tr>
		<td>Attacker</td>
		<td>Defender</td>
		<td>Atackers dead</td>
		<td>Defenders dead</td>
<!--		<td>Attackers survived</td>
		<td>Defenders survived</td> -->
		<td>Attacker KillPower</td>
		<td>Defenders KillPower</td>
		<td>Attacker Combat score</td>
		<td>Defender Combat score</td>
	</tr>
	<?php
	for($i=0;$i<$cnt;$i++)
	{
		$AttackerCountTotal = ($StatisticsList[$i]["AIC"]+$StatisticsList[$i]["AAC"]+$StatisticsList[$i]["ACC"]+$StatisticsList[$i]["ATC"]);
		$DefenderCountTotal = ($StatisticsList[$i]["DIC"]+$StatisticsList[$i]["DAC"]+$StatisticsList[$i]["DCC"]+$StatisticsList[$i]["DTC"]);
		$AttackerDeadCountTotal = ($StatisticsList[$i]["AICD"]+$StatisticsList[$i]["AACD"]+$StatisticsList[$i]["ACCD"]+$StatisticsList[$i]["ATCD"]);
		$DefenderDeadCountTotal = ($StatisticsList[$i]["DICD"]+$StatisticsList[$i]["DACD"]+$StatisticsList[$i]["DCCD"]+$StatisticsList[$i]["DTCD"]);
		$AttackerKillPower = TwoDigitPrecision($DefenderDeadCountTotal / $AttackerCountTotal);
		$DefenderKillPower = TwoDigitPrecision($AttackerDeadCountTotal / $DefenderCountTotal);

		// a good combat is when we kill as most as possible, while loosing as little as possible
		$AttackerKillPCT = $DefenderDeadCountTotal * 100 / $DefenderCountTotal;
		$AttackerSurvivePCT = ( $AttackerCountTotal - $AttackerDeadCountTotal ) * 100 / $AttackerCountTotal;
		$AttackerCombatScore = (int)( $AttackerKillPCT * $AttackerSurvivePCT / 100 );
		$DefenderKillPCT = $AttackerDeadCountTotal * 100 / $AttackerCountTotal;
		$DefenderSurvivePCT = ( $DefenderCountTotal - $DefenderDeadCountTotal ) * 100 / $DefenderCountTotal;
		$DefenderCombatScore = (int)( $DefenderKillPCT * $DefenderSurvivePCT / 100 );
		
		$Attacker = "";
		$AttackerA = "";
		$AttackerD = "";
		if( $StatisticsList[$i]["AIC"] )
		{
			$Attacker .= $StatisticsList[$i]["AIC"]." Infantry ";
			if( $StatisticsList[$i]["AICD"] > 0 )
				$AttackerD .= $StatisticsList[$i]["AICD"]." Infantry ";
			$AttackerA .= ($StatisticsList[$i]["AIC"]-$StatisticsList[$i]["AICD"])." Infantry ";
		}
		if( $StatisticsList[$i]["AAC"] )
		{
			$Attacker .= $StatisticsList[$i]["AAC"]." Archer ";
			if($StatisticsList[$i]["AACD"]>0)
				$AttackerD .= $StatisticsList[$i]["AACD"]." Archer ";
			$AttackerA .= ($StatisticsList[$i]["AAC"]-$StatisticsList[$i]["AACD"])." Archer ";
		}
		if( $StatisticsList[$i]["ACC"] )
		{
			$Attacker .= $StatisticsList[$i]["ACC"]." Cavalry";
			if($StatisticsList[$i]["ACCD"]>0)
				$AttackerD .= $StatisticsList[$i]["ACCD"]." Cavalry";
			$AttackerA .= ($StatisticsList[$i]["ACC"]-$StatisticsList[$i]["ACCD"])." Cavalry ";
		}
		if( $StatisticsList[$i]["ATC"] )
		{
			$Attacker .= $StatisticsList[$i]["ATC"]." Trebuchet";
			if($StatisticsList[$i]["ATCD"]>0)
				$AttackerD .= $StatisticsList[$i]["ATCD"]." Trebuchet";
			$AttackerA .= ($StatisticsList[$i]["ATC"]-$StatisticsList[$i]["ATCD"])." Trebuchet ";
		}
	
		$Defender = "";
		$DefenderA = "";
		$DefenderD = "";
		if( $StatisticsList[$i]["DIC"] )
		{
			$Defender .= $StatisticsList[$i]["DIC"]." Infantry ";
			if($StatisticsList[$i]["DICD"]>0)
				$DefenderD .= $StatisticsList[$i]["DICD"]." Infantry ";
			$DefenderA .= ($StatisticsList[$i]["DIC"]-$StatisticsList[$i]["DICD"])." Infantry ";
		}
		if( $StatisticsList[$i]["DAC"] )
		{
			$Defender .= $StatisticsList[$i]["DAC"]." Archer ";
			if($StatisticsList[$i]["DACD"]>0)
				$DefenderD .= $StatisticsList[$i]["DACD"]." Archer ";
			$DefenderA .= ($StatisticsList[$i]["DAC"]-$StatisticsList[$i]["DACD"])." Archer ";
		}
		if( $StatisticsList[$i]["DCC"] )
		{
			$Defender .= $StatisticsList[$i]["DCC"]." Cavalry ";
			if($StatisticsList[$i]["DCCD"]>0)
				$DefenderD .= $StatisticsList[$i]["DCCD"]." Cavalry ";
			$DefenderA .= ($StatisticsList[$i]["DCC"]-$StatisticsList[$i]["DCCD"])." Cavalry ";
		}
		if( $StatisticsList[$i]["DTC"] )
		{
			$Defender .= $StatisticsList[$i]["DTC"]." Trebuchet ";
			if($StatisticsList[$i]["DTCD"]>0)
				$DefenderD .= $StatisticsList[$i]["DTCD"]." Trebuchet ";
			$DefenderA .= ($StatisticsList[$i]["DTC"]-$StatisticsList[$i]["DTCD"])." Trebuchet ";
		}
	?>
	<tr>
		<td><?php echo $Attacker; ?></td>
		<td><?php echo $Defender; ?></td>
		<td><?php echo $AttackerD;?></td>
		<td><?php echo $DefenderD;?></td>
<!--		<td><?php echo $AttackerA;?></td>
		<td><?php echo $DefenderA;?></td> -->
		<td><?php echo $AttackerKillPower;?></td>
		<td><?php echo $DefenderKillPower;?></td>
		<td><?php echo $AttackerCombatScore;?></td>
		<td><?php echo $DefenderCombatScore;?></td>
	</tr>
	<?php
	}
	?>
</table>
<?php
function TwoDigitPrecision($a)
{
	return ((int)($a * 100) ) / 100;
}
?>