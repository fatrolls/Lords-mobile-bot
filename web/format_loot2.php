<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('memory_limit','64M');
$EnergyToSpendPerDay = 80000;
$MonsterHitCost[1] = 1952;
$MonsterHitsToKill[1] = 2; // 1 grim reaper, 1 queen bee, 1 frost wing
$MonsterHitCost[2] = 3255;
$MonsterHitsToKill[2] = 7; // 7 for grim reaper, 5 for Queen, 6 saberfang, 4 frostwing, 5 helldrider, 9 tidal titan
$MonsterHitCost[3] = 5208;
$MonsterHitsToKill[3] = 13;
$MonsterHitCost[4] = 9114;
$MonsterHitsToKill[4] = 23; // 23 garg, 20 frost wing 
/*
1	1952
5	3255
12	5208
23	9114
*/
	
//count each (monster type + monster level + loot type)(count)
$FoundLootTypes = array();
$FoundMonsterTypes = array();
$FoundLevelTypes = array();
$MonsterLevelLoot_Count = array();
$Level_Count = array();
$MonsterLevel_Count = array();
$LevelLoot_Count = array();
$LevelHits_RowsInFile_Count = array();
$LootGroupLooted_RowsInFile_Counts = array();
$LootNr = $HitsMadeInFiles = 0;
$FoundLootTypes["10 Minute Research Speed up"]=0;
$FoundLootTypes["15 Minute Research Speed up"]=0;
$FoundLootTypes["30 Minute Research Speed up"]=0;
$FoundLootTypes["60 Minute Research Speed up"]=0;
$FoundLootTypes["3h Research Speed up"]=0;
$FoundLootTypes["8h Research Speed up"]=0;
$FoundLootTypes["15h Research Speed up"]=0;
$FoundLootTypes["10 Minute Speed up"]=0;
$FoundLootTypes["15 Minute Speed up"]=0;
$FoundLootTypes["30 Minute Speed up"]=0;
$FoundLootTypes["60 Minute Speed up"]=0;
$FoundLootTypes["3h Speed up"]=0;
$FoundLootTypes["8h Speed up"]=0;
$FoundLootTypes["15h Speed up"]=0;
$FoundLootTypes["50 Gems"]=0;
$FoundLootTypes["100 Gems"]=0;
$FoundLootTypes["200 Gems"]=0;
$FoundLootTypes["300 Gems"]=0;
$FoundLootTypes["400 Gems"]=0;
$FoundLootTypes["500 Gems"]=0;
$FoundLootTypes["600 Gems"]=0;
$FoundLootTypes["800 Gems"]=0;
$FoundLootTypes["mat 0"]=0;
$FoundLootTypes["mat 1"]=0;
$FoundLootTypes["mat 2"]=0;
$FoundLootTypes["mat 3"]=0;
$FoundLootTypes["mat 4"]=0;
$FoundLootTypes["4h Shield"]=0;
$FoundLootTypes["8h Shield"]=0;
$FoundLootTypes["24h Shield"]=0;
$FoundLootTypes["3Day Shield"]=0;
$FoundLootTypes["10k Ore"]=0;
$FoundLootTypes["50k Ore"]=0;
$FoundLootTypes["150k Ore"]=0;
$FoundLootTypes["10k Stone"]=0;
$FoundLootTypes["50k Stone"]=0;
$FoundLootTypes["150k Stone"]=0;
$FoundLootTypes["500k Stone"]=0;
$FoundLootTypes["10k Wood"]=0;
$FoundLootTypes["50k Wood"]=0;
$FoundLootTypes["150k Wood"]=0;
$FoundLootTypes["500k Wood"]=0;
$FoundLootTypes["30k Food"]=0;
$FoundLootTypes["150k Food"]=0;
$FoundLootTypes["500k Food"]=0;
$FoundLootTypes["2m Food"]=0;
$FoundLootTypes["3k Gold"]=0;
$FoundLootTypes["15k Gold"]=0;
$FoundLootTypes["50k Gold"]=0;
$FoundLootTypes["200k Gold"]=0;
$FoundLootTypes["Jewel"]=0;
$FoundLootTypes["Random Relocator"]=0;
$FoundLootTypes["Relocator"]=0;
$FoundLootTypes["Common Hero Chest"]=0;
$FoundLootTypes["Uncommon Hero Chest"]=0;
$FoundLootTypes["Rare Hero Chest"]=0;
$FoundLootTypes["Epic Hero Chest"]=0;

if( 1==1 )
	LoadDataFromFile( "loots.txt" );
else if( 1==1 )
{
	LoadDataFromFile( "loots_pers_1hits.txt" );
//	LoadDataFromFile( "loots_pers_7hits.txt", 7 );
}
else if( 1==1 )
	LoadDataFromFile( "loots_pers.txt" );

echo "Hits loaded $HitsMadeInFiles<br>";
echo "Loots loaded $LootNr<br>";

//count loots that dropped, based on level, based on monster type....
for($i=0;$i<$LootNr;$i++)
{
	$Index_MonsterLevelLoot = GetIndexMonsterLevelLoot($Loots[$i]);
	$Index_MonsterLevel = GetIndexMonsterLevel($Loots[$i]);
	$Index_LevelLoot = GetIndexLevelLoot($Loots[$i]);
	$Index_Level = GetIndexLevel($Loots[$i]);
	
	//data for loot chances based on monster type and level
	$MonsterLevelLoot_Count[$Index_MonsterLevelLoot]++;										// required to calc drop percantage based on monster type
	$MonsterLevel_Count[$Index_MonsterLevel]++;												// required to calc drop percantage based on monster level
	$MonsterLevelLootStacked_Count[$Index_MonsterLevelLoot] += $Loots[$i]["LootCount"];		// total loot drop of this type
	$LevelLoot_Count[$Index_LevelLoot]++;
	$Level_Count[$Index_Level]++;
		
	$FoundLootTypes[$Loots[$i]["Loot"]]=1;
	$FoundMonsterTypes[$Loots[$i]["Monster"]]=$Loots[$i]["Monster"];
	$FoundLevelTypes[$Loots[$i]["Level"]]=$Loots[$i]["Level"];
}

//merge loot group counts for level 
$LootGroupLootedCounts = array();
$FoundLootGroupTypes = array();
$FoundLootGroupTypes["Gear material"] = 0;
$FoundLootGroupTypes["Gold"] = 0;
$FoundLootGroupTypes["Resource"] = 0;
$FoundLootGroupTypes["Shield hours"] = 0;
$FoundLootGroupTypes["Super Rare mat"] = 0;
$FoundLootGroupTypes["gem"] = 0;
$FoundLootGroupTypes["speedup hours"] = 0;
$FoundLootGroupTypes["Jewel"] = 0;
$FoundLootGroupTypes["holy star"] = 0;
$FoundLootGroupTypes["troop heal"] = 0;
$FoundLootGroupTypes["wall repair"] = 0;

for($i=0;$i<$LootNr;$i++)
	if($Loots[$i]["LootGroup"])
	{
		$LootGroupStackedCounts[$Loots[$i]["Level"]][$Loots[$i]["LootGroup"]] += $Loots[$i]["LootNumericCountWithStacks"]; // do not count double drop chance here
		$LootGroupLootedCounts[$Loots[$i]["Level"]][$Loots[$i]["LootGroup"]]++;
		$FoundLootGroupTypes[$Loots[$i]["LootGroup"]]=1;
	}

sort($FoundMonsterTypes);
sort($FoundLevelTypes);
foreach($FoundLootGroupTypes as $key => $val)
	if($val == 0)
		unset($FoundLootGroupTypes[$key]);
/*{
	ksort($FoundLootTypes);
	$FoundLootTypes = KeySortInt($FoundLootTypes);
	foreach($FoundLootTypes as $key => $val)	
		echo "\$FoundLootTypes[\"$key\"]=0;<br>";
}/**/

foreach($FoundLootTypes as $key => $val)
	if($val == 0)
		unset($FoundLootTypes[$key]);


//loot group chances
$RowIndex = 0;
$ColIndex = 0;
$LevelGroupChances[$RowIndex][$ColIndex++] = "Monster Level";
$LevelGroupChances[$RowIndex][$ColIndex++] = "Hit count";
foreach($FoundLootGroupTypes as $key => $val)
{
//	$LevelGroupChances[$RowIndex][$ColIndex++] = "$key dropchance"; 
//	$LevelGroupChances[$RowIndex][$ColIndex++] = "$key avg drop per hit"; 
	$LevelGroupChances[$RowIndex][$ColIndex++] = "$key total gathered"; 
}
foreach($FoundLevelTypes as $ML)
{
	$RowIndex++;
	$ColIndex = 0;
	$SumOfChances = 0;
	
	$LevelGroupChances[$RowIndex][$ColIndex++] = $ML;
	$LevelGroupChances[$RowIndex][$ColIndex++] = $LevelHits_RowsInFile_Count[$ML];

	foreach($FoundLootGroupTypes as $LG => $val)
	{
		if($LootGroupStackedCounts[$ML][$LG]>0)
		{
//echo "ml = $ML, lg = $LG, number of rows contained this lg ".$LootGroupLooted_RowsInFile_Counts[$ML][$LG]." , we hit this level ".$LevelHits_RowsInFile_Count[$ML]." times<br>";
//			$LootGroupDropChance = $LootGroupLooted_RowsInFile_Counts[$ML][$LG] * 100 / $LevelHits_RowsInFile_Count[$ML];
//			$SumOfChances += $LootGroupDropChance;
//			$LevelGroupChances[$RowIndex][$ColIndex++] = ToKM( ToPrec( $LootGroupDropChance ) )." %";
//			$AvgDropCountOnHit = $LootGroupStackedCounts[$ML][$LG] / $LootGroupLooted_RowsInFile_Counts[$ML][$LG];
//			$AvgDropCountOnHit = $LootGroupStackedCounts[$ML][$LG] / $LevelHits_RowsInFile_Count[$ML];
//			$LevelGroupChances[$RowIndex][$ColIndex++] = ToKM( ToPrec( $AvgDropCountOnHit ) );						// avg drop count on hit
			$LevelGroupChances[$RowIndex][$ColIndex++] = ToKM( ToPrec( $LootGroupStackedCounts[$ML][$LG] ) );		// sum of dropcounts so far
		}
		else
		{
//			$LevelGroupChances[$RowIndex][$ColIndex++] = "";
			$LevelGroupChances[$RowIndex][$ColIndex++] = "";
		}
	}
//	if($SumOfChances>201)
//		echo "Dropchance is larger than 100% = $SumOfChances!!<br>";
}

//gen table for chances 
$RowIndex = 0;
$ColIndex = 0;
$MonsterLootChancesTable[$RowIndex][$ColIndex++] = "Monster";
$MonsterLootChancesTable[$RowIndex][$ColIndex++] = "Level";
$MonsterLootChancesTable[$RowIndex][$ColIndex++] = "Kill Count";
foreach($FoundLootTypes as $key => $val)
{
	$MonsterLootChancesTable[$RowIndex][$ColIndex++] = $key;
}
foreach($FoundLevelTypes as $ML)
{
	foreach($FoundMonsterTypes as $MN)
	{
		$ChanceSum = 0;
		$Index_MonsterLevel = GetIndexMonsterLevel("",$MN,$ML);
		if($MonsterLevel_Count[$Index_MonsterLevel]>0 )
		{
			$RowIndex++;
			$ColIndex = 0;
			$MonsterLootChancesTable[$RowIndex][$ColIndex++] = $MN;
			$MonsterLootChancesTable[$RowIndex][$ColIndex++] = $ML;
			$MonsterLooted = $MonsterLevel_Count[$Index_MonsterLevel];
			$MonsterLootChancesTable[$RowIndex][$ColIndex++] = $MonsterLooted;

			foreach($FoundLootTypes as $key => $val)
			{
				$Index_MonsterLevelLoot = GetIndexMonsterLevelLoot("",$MN,$ML,$key);
				if($MonsterLevelLoot_Count[$Index_MonsterLevelLoot])
					$chance = ToPrec( $MonsterLevelLoot_Count[$Index_MonsterLevelLoot] * 100 / $MonsterLevel_Count[$Index_MonsterLevel] )."%";
				else
					$chance = "";
				$MonsterLootChancesTable[$RowIndex][$ColIndex++] = $chance;
				$ChanceSum += $chance;
			}
		}
		if($ChanceSum>100.1)
			echo "Error ! : $ChanceSum is larger than 100<br>";
	}
}

echo "LootGroup average drop count from guild gifts based on monster level<br>";
PrintTableHTML($LevelGroupChances);
echo "Chance of a loot type to drop from guild gifts based on monster type and level<br>";
PrintTableHTML($MonsterLootChancesTable);
echo "<br>";


function LoadDataFromFile($FileName,$HitCount=1)
{
	global $Loots,$LootNr,$HitsMadeInFiles,$LevelHits_RowsInFile_Count,$LootGroupLooted_RowsInFile_Counts;
	$f = fopen( $FileName, "rt" );
	if($f)
	{
		$LineNrThisFile = 0;
//		$LootNr = 0;
		while (($line = fgets($f)) !== false) 
		{
			$LineNrThisFile++;
			$line = trim($line);
			$lines = ExplodeLineToMultiLines($line); //in case of personal hits
			$MonsterLevel = (int)($line[3]);
			$LevelHits_RowsInFile_Count[$MonsterLevel]+=$HitCount;
	//print_r($lines);
			if(count($lines)==0)
				echo "$FileName : Error on line $LineNrThisFile : $line<br>";
			unset($CountOncePerLineLootGroup);
			foreach($lines as $ind => $tline )
			{
				$struct = ParseLine($tline);
				if($struct=="" )
				{
					if(strlen($line) > 5)
						echo "$FileName : Error on line $LineNrThisFile : $line<br> Subline : $tline<br>";
					continue;
				}
				//only count once loot group per line. This way we can merge amounts per line when calculating AVG
				if($struct["LootGroup"])
				{
//if($struct["LootGroup"]=="Resource" && $struct["Level"]==1) echo "$line    ".isset($CountOncePerLineLootGroup[$struct["LootGroup"]])."<br>";
					if(!isset($CountOncePerLineLootGroup[$struct["LootGroup"]]))
					{
						$LootGroupLooted_RowsInFile_Counts[$struct["Level"]][$struct["LootGroup"]]++;
						$CountOncePerLineLootGroup[$struct["LootGroup"]]=1;
					}
				}				
				$Loots[$LootNr++] = $struct;
			}
	//		$struct["Energy"] = $MonsterHitsToKill[$struct["Monster"]][$struct["Level"]] * $MonsterHitCost[$struct["Level"]];
		}
		fclose($f);
//		echo "$FileName : Loots loaded : $LootNr from $LineNrThisFile lines. Loot per hit is :".($LootNr/$LineNrThisFile/$HitCount)."<br>";
	}
	else 
		echo "Could not open file : $FileName";
	$HitsMadeInFiles += $LineNrThisFile * $HitCount;
}

function KeySortInt( $list )
{
	$OldList = $list;
	//get a list of possible keys
	foreach($OldList as $key1 => $val)
	{
		$IntKey = (int)$key1;
//echo "$IntKey<br>";
		if(strpos("#".$key1,"Common Hero")==1)
			$IntKey = 0x7FFFFFFF-400;
		else if(strpos("#".$key1,"Uncommon Hero")==1)
			$IntKey = 0x7FFFFFFF-300;
		else if(strpos("#".$key1,"Rare Hero")==1)
			$IntKey = 0x7FFFFFFF-200;
		else if(strpos("#".$key1,"Epic Hero")==1)
			$IntKey = 0x7FFFFFFF-100;
		else if($IntKey==0)	//full string value ? yuck
			$IntKey = 0x7FFFFFFF;
		else if(strpos("#".$key1,$IntKey." G")==1)
			$IntKey *= 100000;	//fake scale for the sake of grouping
		else if(strpos("#".$key1,$IntKey."h Sh")==1)
			$IntKey *= 30000000;	//fake scale for the sake of grouping
		else if(strpos("#".$key1,$IntKey."k")==1)
			$IntKey *= 1000;
		else if(strpos("#".$key1,$IntKey."h")==1)
			$IntKey *= 60;
		$IntKeys[$IntKey]=$key1;
	}
	ksort($IntKeys);
//	print_r($IntKeys);
	foreach($IntKeys as $SelectedKey => $valt)
	{
		foreach($OldList as $key1 => $val)
		{
			$IntKey = (int)$key1;
	//echo "$IntKey<br>";
			if(strpos("#".$key1,"Common Hero")==1)
				$IntKey = 0x7FFFFFFF-400;
			else if(strpos("#".$key1,"Uncommon Hero")==1)
				$IntKey = 0x7FFFFFFF-300;
			else if(strpos("#".$key1,"Rare Hero")==1)
				$IntKey = 0x7FFFFFFF-200;
			else if(strpos("#".$key1,"Epic Hero")==1)
				$IntKey = 0x7FFFFFFF-100;
			else if($IntKey==0)	//full string value ? yuck
				$IntKey = 0x7FFFFFFF;
			else if(strpos("#".$key1,$IntKey." G")==1)
				$IntKey *= 100000;	//fake scale for the sake of grouping
			else if(strpos("#".$key1,$IntKey."h Sh")==1)
				$IntKey *= 30000000;	//fake scale for the sake of grouping
			else if(strpos("#".$key1,$IntKey."k")==1)
				$IntKey *= 1000;
			else if(strpos("#".$key1,$IntKey."h")==1)
				$IntKey *= 60;
			if($SelectedKey == $IntKey)
				$NewList[$key1] = $val;
		}
	}
//	print_r($NewList);
	return $NewList;
}

function PrintTableExcel($table,$rows)
{
	echo "\n\n\n";
	for($i=0;$i<$rows;$i++)
	{
		foreach($table[$i] as $key => $val)
			echo "$val\t";
		echo "\n";			
	}
}

function PrintTableHTML($table)
{
	$rows = count($table);
	echo "<table border=1>";
		for($i=0;$i<$rows;$i++)
		{
		echo "<tr>";
			foreach($table[$i] as $key => $val)
				echo "<td>$val</td>";
		echo "</tr>";			
		}
	echo "</table>";
}

function GetIndexMonsterLevelLoot($Loots, $Monster="", $Level="", $Loot="" )
{
	if( $Monster != "" )
	{
		return $Monster.$Level.$Loot;
	}
	return $Loots["Monster"].$Loots["Level"].$Loots["Loot"];
}

function GetIndexMonsterLevel($Loots, $Monster="", $Level="")
{
	if( $Monster != "" )
	{
		return $Monster.$Level;
	}
	return $Loots["Monster"].$Loots["Level"];
}

function GetIndexLevelLoot($Loots, $Level="", $Loot="" )
{
	if( $Level != "" )
	{
		return $Level.$Loot;
	}
	return $Loots["Level"].$Loots["Loot"];
}

function GetIndexLevel($Loots, $Level="" )
{
	if( $Level != "" )
	{
		return $Level;
	}
	return $Loots["Level"];
}

function LookupMonster( $m )
{
	if( $m == "gr")
		return "Gream Reaper";
	if( $m == "bw")
		return "BlackWing";
	if( $m == "qb")
		return "Queen Bee";
	if( $m == "mm" )
		return "Mega Maggot";
	if( $m == "sb" || $m == "sf")
		return "Saberfang";
	if( $m == "gi")
		return "Gryphon";
	if( $m == "mc")
		return "Mecha Trojan";
	if( $m == "ja")
		return "Jade Wyrm";
	if( $m == "bo")
		return "Bon Vivian";
	if( $m == "gg")
		return "Gargantuas";
	if( $m == "fw")
		return "Frostwing";
	if( $m == "hd")
		return "Hell Drider";
	if( $m == "sn")
		return "Snow Beast";
	if( $m == "tt")
		return "Tidal Titan";
	if( $m == "te")
		return "Terrorthorn";
	if( $m == "no")
		return "Noceros";
	if( $m == "vs")
		return "Voodoo Shaman";
	
	echo "Could not find Monster type $m<br>";
	return "";
}

function LookupLevel( $l )
{
	$l = (int)$l;
	if( $l <=0 || $l > 5 )
	{
		echo "Could not find Monster Level $l<br>";
		return "";
	}
	
	return $l;
}

function LookupLootType( $l, $m )
{
	if( $l == "50gems")
		return "50 Gems";
	if( $l == "100gems")
		return "100 Gems";
	if( $l == "200gems")
		return "200 Gems";
	if( $l == "300gems")
		return "300 Gems";
	if( $l == "400gems")
		return "400 Gems";
	if( $l == "500gems")
		return "500 Gems";
	if( $l == "600gems")
		return "600 Gems";
	if( $l == "800gems")
		return "800 Gems";
	
	if( $l == "10m")
		return "10 Minute Speed up";
	if( $l == "15m")
		return "15 Minute Speed up";
	if( $l == "30m")
		return "30 Minute Speed up";
	if( $l == "60m")
		return "60 Minute Speed up";
	if( $l == "3h")
		return "3h Speed up";
	if( $l == "8h")
		return "8h Speed up";
	if( $l == "15h")
		return "15h Speed up";
	if( $l == "24h")
		return "24h Speed up";
	
	if( $l == "10mr")
		return "10 Minute Research Speed up";
	if( $l == "15mr")
		return "15 Minute Research Speed up";
	if( $l == "30mr")
		return "30 Minute Research Speed up";
	if( $l == "60mr")
		return "60 Minute Research Speed up";
	if( $l == "3hr")
		return "3h Research Speed up";
	if( $l == "8hr")
		return "8h Research Speed up";
	if( $l == "15hr")
		return "15h Research Speed up";
	if( $l == "24hr")
		return "24h Research Speed up";
	
	if( $l == "10wood")
		return "10k Wood";
	if( $l == "50wood")
		return "50k Wood";
	if( $l == "150wood")
		return "150k Wood";
	if( $l == "500wood")
		return "500k Wood";
	
	if( $l == "10stone")
		return "10k Stone";
	if( $l == "50stone")
		return "50k Stone";
	if( $l == "150stone")
		return "150k Stone";
	if( $l == "500stone")
		return "500k Stone";
	if( $l == "1.5stone")
		return "1.5m Stone";
	
	if( $l == "10ore")
		return "10k Ore";
	if( $l == "50ore")
		return "50k Ore";
	if( $l == "150ore")
		return "150k Ore";
	if( $l == "500ore")
		return "500k Ore";
	if( $l == "1.5ore")
		return "1.5m Ore";
	
	if( $l == "30food")
		return "30k Food";
	if( $l == "150food")
		return "150k Food";
	if( $l == "500food")
		return "500k Food";
	if( $l == "2mfood")
		return "2m Food";
	
	if( $l == "3gold")
		return "3k Gold";
	if( $l == "15gold")
		return "15k Gold";
	if( $l == "50gold")
		return "50k Gold";
	if( $l == "200gold")
		return "200k Gold";
	
	// Voodoo Shaman
	if( $m == "vs")
	{
		if( $l == "doll")
		{
			return "mat 0";
			return "Stuffed Doll";
		}
		if( $l == "gem")
		{
			return "mat 1";
			return "Sacred Gem";
		}
		if( $l == "drum")
		{
			return "mat 2";
			return "Tribal Drum";
		}
		if( $l == "mask")
		{
			return "mat 3";
			return "Ritual Mask";
		}
	}

	// Noceros
	if( $m == "no")
	{
		if( $l == "vial")
		{
			return "mat 0";
			return "Lightning Vial";
		}
		if( $l == "chorn")
		{
			return "mat 1";
			return "Chipped Horn";
		}
		if( $l == "ehorn")
		{
			return "mat 2";
			return "Electric Horn";
		}
		if( $l == "hide")
		{
			return "mat 3";
			return "Crackling Hide";
		}
	}
	
	// terror thorn
	if( $m == "te")
	{
		if( $l == "seed")
		{
			return "mat 0";
			return "Terrorthorn Seed";
		}
		if( $l == "jar")
		{
			return "mat 1";
			return "Honey Jar";
		}
		if( $l == "teeth")
		{
			return "mat 2";
			return "Terror Teeth";
		}
		if( $l == "vine")
		{
			return "mat 3";
			return "Terror Vine";
		}
		if( $l == "pollen")
		{
			return "mat 4";
			return "Terror Pollen";
		}
	}
	
	// tidal titan
	if( $m == "tt")
	{
		if( $l == "pearl")
		{
			return "mat 0";
			return "Glistening Pearl";
		}
		if( $l == "echo")
		{
			return "mat 1";
			return "Drowned Echo";
		}
		if( $l == "breeze")
		{
			return "mat 2";
			return "Ocean Breeze";
		}
		if( $l == "alloy")
		{
			return "mat 3";
			return "Submerged Alloy";
		}
	}
	
	// snow beast
	if( $m == "sn" )
	{
		if( $l == "bell")
		{
			return "mat 0";
			return "Festive Bell";
		}
		if( $l == "antler")
		{
			return "mat 1";
			return "Beast Antlers";
		}
		if( $l == "blood")
		{
			return "mat 2";
			return "Beast Blood";
		}
		if( $l == "paw")
		{
			return "mat 3";
			return "Beast Paw";
		}
	}

	// Hell Drider
	if( $m == "hd" )
	{
		if( $l == "brain")
		{
			return "mat 0";
			return "Mutated Brain";
		}
		if( $l == "scraps")
		{
			return "mat 3";
			return "Metal Scraps";
		}
		if( $l == "core")
		{
			return "mat 2";
			return "Smoldering Core";
		}
		if( $l == "hhorn")
		{
			return "mat 1";
			return "Devil Horn";
		}
	}	
	// frostwing
	if( $m == "fw" )
	{
		if( $l == "heart")
		{
			return "mat 0";
			return "Frostwing Heart";
		}
		if( $l == "horn")
		{
			return "mat 2";
			return "Frostwing Horn";
		}
		if( $l == "scale")
		{
			return "mat 1";
			return "Frostwing Scale";
		}
		if( $l == "claw")
		{
			return "mat 3";
			return "Frostwing Claw";
		}
	}
	
	//gargantua
	if( $m == "gg" )
	{
		if( $l == "geye")
		{
			return "mat 0";
			return "Gargantuan Eye";
		}
		if( $l == "fang")
		{
			return "mat 2";
			return "Gargantuan Fang";
		}
		if( $l == "locks")
		{
			return "mat 1";
			return "Gargantuan Locks";
		}
		if( $l == "bolt")
		{
			return "mat 3";	
			return "Iron Bolts";
		}
		if( $l == "skin")
		{
			return "mat 4";	
			return "Tattooed Skin";
		}
	}
	
	//blackwing
	if( $m == "bw" )
	{
		if( $l == "eye")
		{
			return "mat 0";
			return "Glowing Eye";
		}
		if( $l == "scale")
		{
			return "mat 2";
			return "Slimy Scale";
		}
		if( $l == "horn")
		{
			return "mat 3";
			return "Crusty Horn";
		}
		if( $l == "egg")
		{
			return "mat 1";	// should match with gryphon egg
			return "Fossilized Egg";
		}
	}

	// mega maggot
	if( $m == "mm" )
	{
		if( $l == "toxin")
		{
			return "mat 0";
			return "Corrosive Toxin";
		}
		if( $l == "egg")
		{
			return "mat 2";
			return "Mega Egg Sac";
		}
		if( $l == "tail")
		{
			return "mat 3";
			return "Maggot Tail";
		}
		if( $l == "teeth")
		{
			return "mat 4";
			return "Maggot Teeth";
		}
		if( $l == "barb")
		{
			return "mat 1";
			return "Mega Barb";
		}
	}
	
	//gream reaper
	if( $m == "gr" )
	{
		if( $l == "skull")
		{
			return "mat 0";
			return "Cursed Skull";
		}
		if( $l == "soul")
		{
			return "mat 1";
			return "Corrupted Soul";
		}
		if( $l == "shroud")
		{
			return "mat 2";
			return "Ghostly Shroud";
		}
		if( $l == "wing")
		{
			return "mat 3";
			return "Terrorwing";
		}
	}
	
	//queen bee
	if( $m == "qb" )
	{
		if( $l == "venom")
		{
			return "mat 0";
			return "Queen Venom";
		}
		if( $l == "stinger")
		{
			return "mat 1";
			return "Royal Stinger";
		}
		if( $l == "husk")
		{
			return "mat 3";
			return "Buzzing Husk";
		}
		if( $l == "chrysalis")
		{
			return "mat 2";
			return "Bee Chrysalis";
		}
	}
	
	//saberfang
	if( $m == "sb" )
	{
		if( $l == "sucker")
		{
			return "mat 0";
			return "Blood Sucker";
		}
		if( $l == "sclaw")
		{
			return "mat 1";
			return "Savage Claw";
		}
		if( $l == "hide")
		{
			return "mat 3";
			return "Prehistoric Hide";
		}
		if( $l == "tooth")
		{
			return "mat 2";
			return "Saber Tooth";
		}
	}
	
	//gryphon
	if( $m == "gi" )
	{
		if( $l == "gcore")
		{
			return "mat 0";
			return "Gryphon Core";
		}
		if( $l == "quil")
		{
			return "mat 2";
			return "Gryphon Quill";
		}
		if( $l == "egg")
		{
			return "mat 3";
			return "Gryphon Egg";
		}
	}
	
	//mecha trojan
	if( $m == "mc" )
	{
		if( $l == "blueprint")
		{
			return "mat 0";
			return "Ancient Blueprints";
		}
		if( $l == "shoe")
		{
			return "mat 1";
			return "Rusty Horseshoe";
		}
		if( $l == "gun")
		{
			return "mat 2";
			return "Refined Gunpowder";
		}
		if( $l == "spring")
		{
			return "mat 3";
			return "Oily Spring";
		}
	}
	
	//Jade wirm
	if( $m == "ja" )
	{
		if( $l == "orb")
		{
			return "mat 0";
			return "Jade Orb";
		}
		if( $l == "scale")
		{
			return "mat 1";
			return "Wyrm Scales";
		}
		if( $l == "gut")
		{
			return "mat 2";
			return "Wyrm Gut";
		}
		if( $l == "spine")
		{
			return "mat 3";
			return "Wyrm Spine";
		}
		if( $l == "horn")
		{
			return "mat 4";
			return "Wyrm Horn";
		}
	}
	
	//bon apeti
	if( $m == "bo" )
	{
		if( $l == "halo")
		{
			return "mat 0";
			return "Glowing Halo";
		}
		if( $l == "plume")
		{
			return "mat 1";
			return "Angelic Plume";
		}
		if( $l == "silk")
		{
			return "mat 2";
			return "Holy Silk";
		}
		if( $l == "bracer")
		{
			return "mat 3";
			return "Lux Bracers";
		}
	}
	
	if( $l == "chest1")
		return "Common Hero Chest";
	if( $l == "chest2")
		return "Uncommon Hero Chest";
	if( $l == "chest3")
		return "Rare Hero Chest";
	if( $l == "chest4")
		return "Epic Hero Chest";
	if( $l == "chest5")
		return "Legendary Hero Chest";
	
	if( $l == "rrelocator")
		return "Random Relocator";
	if( $l == "relocator")
		return "Relocator";
	
	if( $l == "4hshield")
		return "4h Shield";
	if( $l == "8hshield")
		return "8h Shield";
	if( $l == "24hshield")
		return "24h Shield";
	if( $l == "3dshield")
		return "3Day Shield";

	if( $l == "15mheal")
		return "15m Troop Heal";
	if( $l == "60mheal")
		return "60m Troop Heal";
	if( $l == "3hheal")
		return "3h Troop Heal";
	if( $l == "15mwall")
		return "15m Wall Repair";
	if( $l == "60mwall")
		return "60m Wall Repair";
	if( $l == "3hwall")
		return "3h Wall Repair";
	
	if( $l == "100star")
		return "100 Holy Stars";
	if( $l == "1000star")
		return "1000 Holy Stars";
	
	if( $l == "fi")
		return "False Info";
	
	if( $l == "jewel")
		return "Jewel";
	
	echo "Could not find Monster loot $l<br>";
	return "";
}

function LookupLootCountFromType( $l )
{
	if( $l == "50gems")
		return 50;
	if( $l == "100gems")
		return 100;
	if( $l == "200gems")
		return 200;
	if( $l == "300gems")
		return 300;
	if( $l == "400gems")
		return 400;
	if( $l == "500gems")
		return 500;
	if( $l == "600gems")
		return 600;
	if( $l == "800gems")
		return 800;
	
	$ToHourConvert=60.0;	//put this to 1 if want minutes
	if( $l == "10m")
		return 10/$ToHourConvert;
	if( $l == "15m")
		return 15/$ToHourConvert;
	if( $l == "30m")
		return 30/$ToHourConvert;
	if( $l == "60m")
		return 60/$ToHourConvert;
	if( $l == "3h")
		return 180/$ToHourConvert;
	if( $l == "8h")
		return 8*60/$ToHourConvert;
	if( $l == "15h")
		return 15*60/$ToHourConvert;
	if( $l == "24h")
		return 24*60/$ToHourConvert;
	
	if( $l == "10mr")
		return 10/$ToHourConvert;
	if( $l == "15mr")
		return 15/$ToHourConvert;
	if( $l == "30mr")
		return 30/$ToHourConvert;
	if( $l == "60mr")
		return 60/$ToHourConvert;
	if( $l == "3hr")
		return 180/$ToHourConvert;
	if( $l == "8hr")
		return 8*60/$ToHourConvert;
	if( $l == "15hr")
		return 15*60/$ToHourConvert;
	if( $l == "24hr")
		return 24*60/$ToHourConvert;
	
	if( $l == "3gold")
		return 3000;
	if( $l == "15gold")
		return 15000;
	if( $l == "50gold")
		return 50000;
	if( $l == "200gold")
		return 200000;

	if( $l == "4hshield" )
		return 4;
	if( $l == "8hshield" )
		return 8;
	if( $l == "24hshield" )
		return 24;
	if( $l == "3dshield" )
		return 3*24;

	if( $l == "15mheal")
		return 15;
	if( $l == "60mheal")
		return 60;
	if( $l == "3hheal")
		return 3*60;
	if( $l == "15mwall")
		return 15;
	if( $l == "60mwall")
		return 60;
	if( $l == "3hwall")
		return 3*60;

	if( $l == "100star")
		return 100;
	if( $l == "1000star")
		return 1000;
	
	if( $l == "fi")
		return 1;
	
	if( strpos( ".".$l, "2m" ) == 1 )
		return 2*1000*1000;
	if( strpos( ".".$l, "1.5" ) == 1 )
		return 1.5*1000*1000;
	if( strpos( ".".$l, "500" ) == 1 )
		return 500*1000;
	if( strpos( ".".$l, "150" ) == 1 )
		return 150*1000;
	if( strpos( ".".$l, "50" ) == 1 )
		return 50*1000;
	if( strpos( ".".$l, "30" ) == 1 )
		return 30*1000;
	if( strpos( ".".$l, "15" ) == 1	)
		return 15*1000;
	if( strpos( ".".$l, "10" ) == 1 )
		return 10*1000;
	if( strpos( ".".$l, "3" ) == 1 )
		return 3*1000;
	
	return 1;
}

function LookupLootGroup( $l, $LootParsed )
{
	if( $LootParsed == "mat 0" )
		return "Super Rare mat";
	
	if( $LootParsed == "mat 1" || $LootParsed == "mat 2" || $LootParsed == "mat 3" || $LootParsed == "mat 4" || $LootParsed == "Gear material")
		return "Gear material";

	if( $l == "100star" || $l == "1000star" )
		return "holy star";
	
	if( strpos( $l, "heal" ) )
		return "troop heal";
	
	if( strpos( $l, "wall" ) )
		return "wall repair";
	
	if( $l == "50gems" || $l == "100gems" || $l == "200gems" || $l == "300gems" || $l == "400gems" || $l == "500gems" || $l == "600gems" || $l == "800gems")
		return "gem";
	
	if( $l == "10mr" || $l == "15mr" || $l == "30mr" || $l == "60mr" || $l == "3hr" || $l == "8hr" || $l == "15hr" || $l == "24hr")
		return "speedup hours";
	
	if( $l == "10m" || $l == "15m" || $l == "30m" || $l == "60m" || $l == "3h" || $l == "8h" || $l == "15h")
		return "speedup hours";
	
	if( $l == "3gold" || $l == "15gold" || $l == "50gold" || $l == "200gold")
		return "Gold";
	
	if( strpos( $l, "wood" ) || strpos( $l, "ore" ) || strpos( $l, "stone" ) )
		return "Resource";

	if( strpos( $l, "food") )
		return "Resource";
//		return "Food";

	if( $l == "4hshield" || $l == "8hshield" || $l == "24hshield" || $l == "3dshield" )
		return "Shield hours";

	if( $l == "jewel" )
		return "Jewel";

	if( $l[0] == "r" && $l[1]== "e" )
		return "Relocator";
	
	return "";
}

function LookupLootCount( $l, $lt )
{
	$l = (int)$l;
	if( $l <=0 || $l > 128 )
	{
		echo "Could not find Monster loot count $l<br>";
		return "";
	}
	if( $l > 7 )
	{
		$IsMaterial = ( strpos( "#".$lt, "mat") == 1 );
		$IsJewel = ( strpos( "#".$lt, "Jewel") == 1 );
//echo "stangely large : $l $lt $IsMaterial $IsJewel<br>";
		if( $IsMaterial != 1 && $IsJewel != 1 )
		{
			echo "Monster loot count is strangely large $l for $lt<br>";
			return "";
		}
	}
	
	return $l;
}

function ParseLine($line)
{
	$parts = explode(" ",$line);
	
	if( count( $parts ) != 4 )
		return "";
	
	//parse-convert elements 1 by 1
	$struct["Monster"] = LookupMonster($parts[0]);
	$struct["Level"] = LookupLevel($parts[1]);
	$struct["Loot"] = LookupLootType($parts[2],$parts[0]);
	$struct["LootCount"] = LookupLootCount($parts[3],$struct["Loot"]);
	
//	echo "$line : ".$struct["LootCount"]."<br>";
//	if( $struct["Loot"] == "mat 1" || $struct["Loot"] == "mat 2" || $struct["Loot"] == "mat 3" || $struct["Loot"] == "mat 4")
//		$struct["Loot"] = "Gear material";
	
	//check if we parsed the line correctly
	foreach($struct as $key => $val)
		if( $val == "" )
			return "";
	
	//for some loots we care to calculate more detailed statistics
	$struct["LootGroup"] = LookupLootGroup($parts[2],$struct["Loot"]);
	if($struct["LootGroup"]!="")
	{
		$struct["LootNumericCountWithStacks"] = LookupLootCountFromType($parts[2]) * $struct["LootCount"];
	}

	if($struct["Level"] == 2 && strpos($parts[2],"gems") && $struct["LootNumericCountWithStacks"] < 100)
		return "";
	if($struct["Level"] == 3 && strpos($parts[2],"gems") && $struct["LootNumericCountWithStacks"] < 100)
		return "";
	if($struct["Level"] == 4 && strpos($parts[2],"gems") && $struct["LootNumericCountWithStacks"] < 100)
		return "";
	if($struct["Level"] == 1 && strpos($parts[2],"chest") && $parts[2] != "chest1")
		return "";
	if($struct["Level"] == 2 && strpos($parts[2],"chest") && $parts[2] != "chest2")
		return "";
	if($struct["Level"] == 3 && strpos($parts[2],"chest") && $parts[2] != "chest3")
		return "";
	if($struct["Level"] == 4 && strpos($parts[2],"chest") && $parts[2] != "chest4")
		return "";
	if($struct["Level"] == 5 && strpos($parts[2],"chest") && $parts[2] != "chest5")
		return "";
	
//if( $parts[0] == "hd" && $struct["Loot"] == "mat 0")	echo "$line<br>";
//if( $struct["Loot"] == "mat 0")	echo "$line ".$struct["LootCount"]."<br>";
//if( $parts[2] == "doll")	echo "$line ".$struct["Loot"]."<br>";
//	echo "$line -> ".$struct["Monster"]." ".$struct["Level"]." ".$struct["Loot"]." ".$struct["LootCount"]." - ".$struct["LootNumericCount"]." <br>";
	if($struct["LootGroup"]=="Resource" && $struct["LootNumericCountWithStacks"]<1000) echo "Loot number not good ".$struct["LootNumericCountWithStacks"]." : $line    <br>";
	//return a parsed line
	return $struct;
}

function ToPrec($num)
{
	if( (float)$num!=(int)$num)
	{
		$ret = number_format( (float)$num, 2, '.', '');
		if($ret == 0 )
			$ret = number_format( (float)$num, 4, '.', '');
		if($ret == 0 )
			$ret = number_format( (float)$num, 6, '.', '');
		return $ret;
	}
	return $num;
}

function ToKM($num)
{
	return $num;
	if( $num >= 1000 * 1000 * 1 )
		return ToPrec ( $num / ( 1000 * 1000 ) )." M";
	if( $num >= 1000 * 1 )
		return ToPrec ( $num / 1000	)." K";
	return $num;
}

function ExplodeLineToMultiLines($line)
{
	$parts = explode(" ",$line);
	
	$partcount = count( $parts );
	if( $partcount < 4 )
		return array();
	if( $partcount == 4 )
	{
		$ret[0]=$line;
		return $ret;
	}

	//pair first 2 elements with every other 2
	for($i=2;$i<$partcount;$i+=2)
		$ret[$reti++]=$parts[0]." ".$parts[1]." ".$parts[$i]." ".$parts[$i+1];
	
	return $ret;
}

function array_swap_assoc($key1, $key2, $array) 
{
	if( !isset($array[$key1]) || !isset($array[$key2]))  
		return $array;
	
	$newArray = array ();
	
  foreach ($array as $key => $value) {
    if ($key == $key1) {
      $newArray[$key2] = $array[$key2];
    } elseif ($key == $key2) {
      $newArray[$key1] = $array[$key1];
    } else {
      $newArray[$key] = $value;
    }
  }
  return $newArray;
}

?>