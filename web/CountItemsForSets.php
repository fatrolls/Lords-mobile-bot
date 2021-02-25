<?php
$InterestedParams = array("chp","cdef","catk","ratk", "ihp","idef","iatk");

$ItemNames=array();
$ItemScores=array();
$ItemSets=array();
LoadItemNames();

$ItemNameCount=array();
CountItemNames();

echo "<br>scores from sets you can gather<br>";
arsort( $ItemScores );
foreach( $ItemScores as $Name => $Score )
	if($ItemSets[$Name]==2)
//		echo "$Name( ".($ItemSets[$Name])." )=$Score<br>";
		echo "$Name=$Score<br>";


echo "<br>Items used in multiple set combinations<br>";
arsort( $ItemNameCount );
foreach( $ItemNameCount as $Name => $Count )
	echo "$Name=$Count<br>";

echo "<br>scores from all sets<br>";
arsort( $ItemScores );
foreach( $ItemScores as $Name => $Score )
	echo "$Name( ".($ItemSets[$Name])." )=$Score<br>";

	
	
function CountItemNames()
{
	global $ItemNames, $ItemNameCount;
	$Content = file_get_contents("Gearsets_out2.txt");
	foreach($ItemNames as $key => $Name)
		$ItemNameCount[$Name] = substr_count( strtoupper($Content), strtoupper($Name) );
}

function LoadItemNames()
{
	global $ItemNames,$ItemScores,$ItemSets;
	$f = fopen("GearsLM.txt","rt");
	if($f)
	{
		while (($line = fgets($f)) !== false) 
		{
			if($line[0]=='#')
				continue;
			if(strlen($line)<5)
				continue;
			$line = str_replace( "\t", " ", $line );
			$line = str_replace( "  ", " ", $line );
			$line = str_replace( "\n", "", $line );
			$line = str_replace( "\r", "", $line );
			$parts = explode(" ",$line);
//echo "Item name : ".$parts[2]."<br>";			
			$ItemNames[count($ItemNames)] = $parts[2];	
			
			unset( $item );
			$item = array();
			$item["slot"] = $parts[0];
			$item["GearSet"] = $parts[1];
			$item["name"] = $parts[2];
			$item["chp"] = $item["rhp"] = $item["ihp"] = $item["catk"] = $item["ratk"] = $item["iatk"] = $item["cdef"] = $item["rdef"] = $item["idef"] = $item["cdef"] = 0; // group notice warnings
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
							echo "Something is wrong for item ".$item["name"]." it has atr ".$IndName." value ".$IndVal." for line $line<br>";
						if( IsParamKnown($IndName) == 0 )
							echo "Unknown param name : $IndName for line $line <br>";
						
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
			$ItemScores[ $item["name"] ] = $item["SumScore"];
			$ItemSets[ $item["name"] ] = $item["GearSet"];
		}
		fclose($f);
	}
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

function GetParamMultiplier( $IndexName )
{
//	return $IndexName[0];
//	return $IndexName[0].$IndexName[1];
	if($IndexName[1]=='a')
		return 5;
	return 1;
}
function GetItemScore( $item )
{
	global $Debug;
	$item["SumScore"] = 0;
	foreach($item as $key => $val)
		if( IsParamImportant( $key ) )
		{
			$g = GetParamGroup( $key );
			$mul = GetParamMultiplier( $key );
			$val = (int)($val * $mul * 10)/10;
			$item["SumScore"] += $val;
		}
	if($item["SumScore"] == 0 && $Debug == 1)
	{
		echo "Error?: Item has sumscore 0 <br>";
		PrintItemInfo( $item );
	}
	return $item;
}
function IsParamKnown( $IndexName )
{
	$InterestedParams = array("chp","cdef","catk","rhp","rdef","ratk","atk","def","hp","ihp","idef","iatk");
	foreach( $InterestedParams as $key => $val )
		if( strpos( "#".$val, $IndexName ) == 1 )
			return 1;
	return 0;
}
?>