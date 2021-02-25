<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
set_time_limit( 30 * 60 );

$Debug = 0;

$ItemSlotNames = array("helm","body","feet","mhand","ohand","trinket","trinket");

echo "=====================================================================================================================================================<br>";
echo "*Army composition : infantry + cavalry + ranged <br>";
$InterestedParams = array("chp","cdef","catk","ratk", "ihp","idef","iatk");
unset( $itemsCatInt );
LoadItemInfo();
GenGearSet();
echo "<br><br>";

echo "=====================================================================================================================================================<br>";
echo "*Army composition : cavalry + ranged <br>";
$InterestedParams = array("chp","cdef","catk","ratk");
unset( $itemsCatInt );
LoadItemInfo();
GenGearSet();
echo "<br><br>";

echo "=====================================================================================================================================================<br>";
echo "*Army composition : infantry + ranged <br>";
$InterestedParams = array("ihp","idef","iatk","ratk");
unset( $itemsCatInt );
LoadItemInfo();
GenGearSet();
echo "<br><br>";

echo "=====================================================================================================================================================<br>";
echo "*Army composition : cavalry <br>";
$InterestedParams = array("chp","cdef","catk");
unset( $itemsCatInt );
LoadItemInfo();
GenGearSet();
echo "<br><br>";

echo "=====================================================================================================================================================<br>";
echo "*Army composition : infantry <br>";
$InterestedParams = array("ihp","idef","iatk");
unset( $itemsCatInt );
LoadItemInfo();
GenGearSet();
echo "<br><br>";

echo "=====================================================================================================================================================<br>";
echo "*Army composition : ranged <br>";
$InterestedParams = array("rdef", "rhp", "ratk");
unset( $itemsCatInt );
LoadItemInfo();
GenGearSet();
echo "<br><br>";

function LoadItemInfo()
{
	global $itemsCatInt,$ItemSlotNames;
	$f = fopen("GearsLM.txt","rt");
	if($f)
	{
		while (($line = fgets($f)) !== false) 
			if(strlen($line)>5)
		{
			$line = str_replace( "\t", " ", $line );
			$line = str_replace( "  ", " ", $line );
			$line = str_replace( "\n", "", $line );
			$line = str_replace( "\r", "", $line );
			$parts = explode(" ",$line);
			unset( $item );
			$item = array();
			$item["slot"] = $parts[0];
			$item["GearSet"] = $parts[1];
			$item["name"] = $parts[2];
			$item["chp"] = $item["rhp"] = $item["catk"] = $item["ratk"] = $item["cdef"] = $item["rdef"] = 0; // group notice warnings
			foreach($parts as $key => $val)
				if( $key > 2 )
				{
					if( $key % 2 == 0 )
						$IndName = $val;
					else
						$IndVal = (int)$val;

					if( $key % 2 == 0 )
					{
						// sanity checks
						if( $IndVal <= 0 )
							echo "Something is wrong for item ".$item["name"]." it has atr ".$IndName." value ".$IndVal." <br>";
						if( IsParamKnown($IndName) == 0 )
							echo "Unknown param name : $IndName <br>";
						
						//convert to a parsable format
						if( strcmp( $IndName, "hp" ) == 0 || $IndName[0] == 'h' )
						{
							$item["chp"] += $IndVal;
							$item["rhp"] += $IndVal;
							$item["ihp"] += $IndVal;
						}					
						else if( strcmp( $IndName, "def" ) == 0 || $IndName[0] == 'd' )
						{
							$item["cdef"] += $IndVal;
							$item["rdef"] += $IndVal;
							$item["idef"] += $IndVal;
						}					
						else if( strcmp( $IndName, "atk" ) == 0 || $IndName[0] == 'a')
						{
							$item["catk"] += $IndVal;
							$item["ratk"] += $IndVal;
							$item["iatk"] += $IndVal;
						}					
						else
							$item[$IndName] += $IndVal;
					}
				}
			$item = GetItemScore( $item );
			if($item["SumScore"] != 0)
			{
	//			PrintItemInfo( $item );
	//			$items[ count($items) ] = $item;
	//			$itemsCat[$item["slot"]][count($itemsCat[$item["slot"]])] = $item;
				
				$ItemNameIndex = ItemSlotNameToIndex( $item["slot"] );
	//echo count($itemsCatInt[$ItemNameIndex])." - $ItemNameIndex<br>";
				$itemsCatInt[$ItemNameIndex][count($itemsCatInt[$ItemNameIndex])] = $item;
			}
		}	
		fclose($f);
	}

	// filter items based on sumscore in each slot. Items with lower score will never get used anyway
	//echo "<br><br>";
	for( $i=0;$i<count($ItemSlotNames);$i++)
	{
	//	print_r( $itemsCatInt[$i] ); echo "<br><br>";
		SortItemsBasedOnScore($itemsCatInt[$i]);
	//	print_r( $itemsCatInt[$i] ); echo "<br>**<br>";
	}

	//need to duplicate the list of items where slotname is duplicated
	for( $i=1;$i<count($ItemSlotNames);$i++)
		if( $ItemSlotNames[$i-1] == $ItemSlotNames[$i] )
			$itemsCatInt[$i] = $itemsCatInt[$i-1];
		
	//print_r($items);
	@unlink("gearsOut.txt");
}

function SortItemsBasedOnScore( &$ItemList )
{
	$ItemCount = count( $ItemList );
	for($i=0;$i<$ItemCount;$i++)
		for($j=$i+1;$j<$ItemCount;$j++)
			if( $ItemList[$i]<$ItemList[$j])
			{
				$t = $ItemList[$j];
				$ItemList[$j] = $ItemList[$i];
				$ItemList[$i] = $t;
			}
}

function ItemSlotNameToIndex( $SlotName )
{
	global $ItemSlotNames,$itemsCatInt;
	foreach( $ItemSlotNames as $key => $val )
		if( $val == $SlotName )
		{
			$ItemNameIndex = $key;
//			while( isset( $itemsCatInt[$ItemNameIndex] ) )
//				$ItemNameIndex++;
			return $ItemNameIndex;
		}
	echo "!! could not find slotname '$SlotName' for item <br>";
	return -1;
}

function PrintItemInfo( $item )
{
	print_r( $item );
	echo "<br>";
}

function IsParamKnown( $IndexName )
{
	$InterestedParams = array("chp","cdef","catk","rhp","rdef","ratk","atk","def","hp","ihp","idef","iatk");
	foreach( $InterestedParams as $key => $val )
		if( strpos( "#".$val, $IndexName ) == 1 )
			return 1;
	return 0;
}
function IsParamImportant( $IndexName )
{
	global $InterestedParams;
	foreach( $InterestedParams as $key => $val )
		if( strpos( "#".$val, $IndexName ) == 1 )
			return 1;
	return 0;
}

function GetParamGroup( $IndexName )
{
//	return $IndexName[0];
//	return $IndexName[0].$IndexName[1];
	return $IndexName;
}

function GetItemScore( $item )
{
	global $Debug;
	$item["SumScore"] = 0;
	foreach($item as $key => $val)
		if( IsParamImportant( $key ) )
		{
			$g = GetParamGroup( $key );
			$item["ScroreGroups"][$g] += $val;
			$item["SumScore"] += $val;
		}
	if($item["SumScore"] == 0 && $Debug == 1)
	{
		echo "Error?: Item has sumscore 0 <br>";
		PrintItemInfo( $item );
	}
	return $item;
}

function MyEcho($what)
{
	/*
	$f = fopen("gearsOut.txt", "at");
	fputs( $f, $what."\n" );
	fclose($f);
	/**/
	echo $what;
}

function SetGearsetScore( $GearSet, $PrintInfo )
{
	if( count( $GearSet ) == 0 )
		return 0;	
	global $ItemSlotNames, $itemsCatInt;
	$SumScore = 0;
	foreach( $GearSet as $key => $val )
	{
//echo $key." ".$val;
		$CurItem = $itemsCatInt[ $key ][ $val ];
//echo " slot index '$key' = ".$ItemSlotNames[$key].", item index '$val'";
		if( $PrintInfo )
		{
			MyEcho( $key.")item name ".$CurItem["name"]." in slot ".$ItemSlotNames[ $key ]." from set ".$CurItem["GearSet"]."<br>" );
//			PrintItemInfo( $CurItem );
			foreach( $CurItem["ScroreGroups"] as $key2 => $val2 )
				$GearSetScore[ $key2 ] += $val2;
		}
		$SumScore += $CurItem["SumScore"];
	}
	//square each of the scores, than multiply each other
	if( $PrintInfo )
	{
		echo "Item set attribute summary : ";
		foreach( $GearSetScore as $key => $val )
		{
			// if we want to have equal amount of values
	/*		{
				$logs = sqrt( $val );
				$SumScore *= $logs;
			}/**/
			// if we want to have max amount of values
/*			{
				$SumScore += $val;
			}/**/
			if( $PrintInfo )
				MyEcho( $key." +".$val."%,  " );
	//echo $key."=".$val."->".(int)$logs."=".(int)$SumScore.",";
		}
		MyEcho( "Total army boost = ".$SumScore." %<br>" );
	}
	return $SumScore;
}

function IsGearsetFromCollectableParts( $GearSet )
{
	global $ItemSlotNames, $itemsCatInt;
	foreach( $GearSet as $key => $val )
	{
//echo $key." ".$val;
		$CurItem = $itemsCatInt[ $key ][ $val ];
//echo $CurSlotName." ".$CurSlotIndex." ".$CurItem["GearSet"][0];
		if( $CurItem["GearSet"][0] != '1' && $CurItem["GearSet"][0] != '2' )
			return 0;
	}
	return 1;
}

function CountDifferentSets( $GearSet )
{
	global $ItemSlotNames, $itemsCatInt;
	$DiffSetCount = 0;
	foreach( $GearSet as $key => $val )
	{
//echo $key." ".$val;
		$CurItem = $itemsCatInt[ $key ][ $val ];
		if( $CurItem["GearSet"][0] == '1' || $CurItem["GearSet"][0] == '2' || !isset( $unique[ $CurItem["GearSet"] ]) )
		{
			$unique[ $CurItem["GearSet"] ] = 1;
			$DiffSetCount++;
		}
	}
	return $DiffSetCount;
}

function GenGearSet()
{
	global $ItemSlotNames, $itemsCatInt, $InterestedParams;
	
	echo "Looking for item combinations to maximize stats : ";
	foreach( $InterestedParams as $key => $val )
		echo "$val ";
	echo "<br>";
	
	// we want to pick items for these slots
	$SlotCount = count($ItemSlotNames);
	for($PickIndex = 0; $PickIndex < $SlotCount; $PickIndex++ )
	{
		$CurGearset[$PickIndex] = 0;
		$CurGearsetMax[$PickIndex] = count($itemsCatInt[$PickIndex]);
	}
//print_r($itemsCatInt[0]);
//print_r($CurGearsetMax);	
	//generate combinations
	$LoopCounter=0;
	$BestScore = 0;
	$BestCraftable = 0;
	$BestMultiSet = 0;
	while( isset($CurGearset[$SlotCount]) == false )
	{
		//get score for this setup
		$CurScore = SetGearsetScore($CurGearset,0);
		if( $CurScore > $BestScore )
		{
			$BestScore = $CurScore;
			$BestScoreSet = $CurGearset;
//SetGearsetScore($CurGearset,1);break;
		}
		if( $CurScore >= $BestCraftable && IsGearsetFromCollectableParts($CurGearset) )
		{
			$BestCraftable = $CurScore;
			$BestCraftableSet = $CurGearset;
			while( isset( $BestCraftableSets[$CurScore] ) )
				$CurScore += 0.01;
			$BestCraftableSets[$CurScore] = $CurGearset;
		}
		if( $CurScore > $BestMultiSet )
		{
			$Sets = CountDifferentSets( $CurGearset );
			if( $Sets >= $SlotCount )
			{
				$BestMultiSet = $CurScore;
				$BestMultiSetSet = $CurGearset;
			}
		}
		//gen next setup
		$Ind = 0;
		$CurGearset[$Ind]++;
		while($CurGearset[$Ind]>=$CurGearsetMax[$Ind] && $Ind < $SlotCount )
		{
			//reset this index
			$CurGearset[$Ind] = 0;
			//increase next index
			$Ind++;
			$CurGearset[$Ind]++;
		}
		$LoopCounter++;
//		if($LoopCounter>10)			break;
	}
	echo "<br>Best item set score you can get by heavy monster hunting + buying item packs: <br>";
	SetGearsetScore($BestScoreSet,1);
	echo "<br>Best item set if you are a casual player that hardly spends money on the game : <br>";
	SetGearsetScore($BestCraftableSet,1);
	echo "<br>Best item set if you invest a lot in monster hunting : <br>";
	SetGearsetScore($BestMultiSetSet,1);
	
	krsort($BestCraftableSets);
	echo "<br>Top x item sets that you can craft from resource gathering and monster hunting : <br>";	
	//print out the best 10 variants
	$i=0;
	foreach($BestCraftableSets as $key => $val )
	{
		SetGearsetScore($BestCraftableSets[$key],1);
		$i++;
		if($i>=5) break;
	}
}
?>