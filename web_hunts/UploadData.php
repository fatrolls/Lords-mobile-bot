<?php
require_once("db_connection.php");

if(!isset($name))
	die("Not a proper upload");

if(!isset($kills))
	$kills=0;

if($objtype == 110)
{
	$Level = GetMonsterLevel($monstertype);
	if($Level!=0)
	{	
		//get monster level for type
		$year = GetYear();
		$day = GetDayOfYear($kills);
		//check if we have an id for this player
		$query1 = "update PlayerHunts set Lvl$Level=Lvl$Level+1 where Day=$day and year=$year and PlayerName = '".mysqli_real_escape_string($dbi,$name)."'";
		$result1 = mysqli_query($dbi,$query1) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
		//echo $query1;

		list($matched, $changed, $warnings) = sscanf($dbi->info, "Rows matched: %d Changed: %d Warnings: %d");
		if($changed == 0)
		{
			$query1 = "insert into PlayerHunts (PlayerName, Lvl$Level, year, day) values('".mysqli_real_escape_string($dbi,$name)."',1,$year,$day)";
			$result1 = mysqli_query($dbi,$query1) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
			//echo $query1;
		}
	}
	else
	{
		echo "ERROR:Monster $monstertype level is zero";
		$f = fopen("UnknownMonsterTypes.txt","at");
		if($f)
		{
			fputs($f,"Monster $monstertype level is zero");
			fclose($f);
		}
	}
	$ForwardObjectType=109;
}
else if($objtype == 111)
{
	//check if we have an id for this player
/*	$query1 = "select 1 from PlayerHuntsList where PlayerName = '".mysqli_real_escape_string($dbi,$name)."' and guid!=$x";
	$result1 = mysqli_query($dbi,$query1) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
	list($AlreadyInserted) = mysqli_fetch_row($result1);
	if($AlreadyInserted == 1)
		die();*/

	$Level = GetMonsterLevel($monstertype);
	if($Level!=0)
	{
		//get monster level for type
		$year = GetYear();
		$day = GetDayOfYear($kills);
		$query1 = "insert into PlayerHuntsList (Lvl,Day,Year,PlayerName,GUID,Monster,Gift,GiftCount) values ($Level,$day,$year,'".mysqli_real_escape_string($dbi,$name)."',$x,$monstertype,$y,$CLevel)";
		//echo $query1;
		$result1 = mysqli_query($dbi,$query1) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
	}
	else
	{
		echo "ERROR:Monster $monstertype level is zero";
		$f = fopen("UnknownMonsterTypes.txt","at");
		if($f)
		{
			fputs($f,"Monster $monstertype level is zero");
			fclose($f);
		}
	}
	$ForwardObjectType=109;
}
//this is when local server is trying to update the remote server
else if($objtype == 109)
{
	//get monster level for type
	//$year = GetYear();
	//$day = GetDayOfYear();
	//check if we have an id for this player
	$query1 = "update PlayerHunts set Lvl1='$Lvl1',Lvl2='$Lvl2',Lvl3='$Lvl3',Lvl4='$Lvl4',Lvl5='$Lvl5' where Day='$day' and year='$year' and PlayerName='".mysqli_real_escape_string($dbi,$name)."'";
	$result1 = mysqli_query($dbi,$query1) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
//	echo $query1."<br>";
//	print_r($dbi->info);echo "<br>";
	list($matched, $changed, $warnings) = sscanf($dbi->info, "Rows matched: %d Changed: %d Warnings: %d");
	if($changed == 0 && $matched == 0)
	{
		$query1 = "insert into PlayerHunts (PlayerName, Lvl1, Lvl2, Lvl3, Lvl4, Lvl5, year, day) values('".mysqli_real_escape_string($dbi,$name)."','$Lvl1','$Lvl2','$Lvl3','$Lvl4','$Lvl5',$year,$day)";
		$result1 = mysqli_query($dbi,$query1) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
//		echo $query1;
	}
	$query1 = "update ServerVars set VarVal='".time()."' where VarName='LastUpdated'";
	$result1 = mysqli_query($dbi,$query1) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
}
if($objtype == 112)
{
	$year = GetYear();
	$day = GetDayOfYear();
	//check if we have an id for this player
	$query1 = "update PlayerMights set PlayerMight=$might,PlayerKills=$kills where Day=$day and year=$year and PlayerName = '".mysqli_real_escape_string($dbi,$name)."'";
	$result1 = mysqli_query($dbi,$query1) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
	//echo $query1;

	list($matched, $changed, $warnings) = sscanf($dbi->info, "Rows matched: %d Changed: %d Warnings: %d");
	if($changed == 0)
	{
		$query1 = "insert into PlayerMights (PlayerName, PlayerMight, PlayerKills, year, day) values('".mysqli_real_escape_string($dbi,$name)."',$might,$kills,$year,$day)";
		$result1 = mysqli_query($dbi,$query1) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
		//echo $query1;
	}
	$ForwardObjectType=112;
}

/*
if(isset($k) && $ForwardObjectType==112)
{
	$Escaped_name=urlencode($name);
	$url="http://rui.eu5.org/UploadData.php?name=$Escaped_name&objtype=$ForwardObjectType&might=$might&kills=$kills&day=$day&year=$year";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$data = curl_exec($ch);
	while(curl_errno($ch) == 28 && $retry < 5)
	{
		$data = curl_exec($ch);
		$retry++;
	}
	echo "$url<br>";
	echo "$data";
	curl_close($ch);
}

//if this is on local server, forward it to main server
if( isset($k) && $ForwardObjectType==109)
{
	$year = GetYear();
	$day = GetDayOfYear();
	//get the best case for this player
	$query1 = "select lvl1,lvl2,lvl3,lvl4,lvl5 from PlayerHunts where day=$day and year=$year and playername='".mysqli_real_escape_string($dbi,$name)."'";
	$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	list($lvl1,$lvl2,$lvl3,$lvl4,$lvl5) = mysqli_fetch_row($result1);
	
	$query1 = "select lvl from PlayerHuntsList where day=$day and year=$year and playername='".mysqli_real_escape_string($dbi,$name)."'";
	$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	while(list($lvl) = mysqli_fetch_row($result1))
		@$stats[$lvl] += 1;
	if(@$stats[1]>$lvl1) $lvl1=$stats[1];
	if(@$stats[2]>$lvl2) $lvl2=$stats[2];
	if(@$stats[3]>$lvl3) $lvl3=$stats[3];
	if(@$stats[4]>$lvl4) $lvl4=$stats[4];
	if(@$stats[5]>$lvl5) $lvl5=$stats[5];
	
	$Escaped_name=urlencode($name);
//	$url="http://rum-lm.eu5.org/UploadData.php?name=$Escaped_name&objtype=109&Lvl1=$lvl1&Lvl2=$lvl2&Lvl3=$lvl3&Lvl4=$lvl4&Lvl5=$lvl5&day=$day&year=$year";
	$url="http://rui.eu5.org/UploadData.php?name=$Escaped_name&objtype=$ForwardObjectType&Lvl1=$lvl1&Lvl2=$lvl2&Lvl3=$lvl3&Lvl4=$lvl4&Lvl5=$lvl5&day=$day&year=$year";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$data = curl_exec($ch);
	while(curl_errno($ch) == 28 && $retry < 5)
	{
		$data = curl_exec($ch);
		$retry++;
	}
	echo "$url<br>";
	echo "$data";
	curl_close($ch);
}/**/

//if this is on local server, forward it to main server
/*if( isset($k) )
{
	$Escaped_name=urlencode($name);
	//$ch = curl_init("http://rum-lm.eu5.org/UploadData.php?name=$name&monstertype=$monstertype");
	$url="http://rum-lm.eu5.org/UploadData.php?name=$Escaped_name&monstertype=$monstertype&objtype=$objtype&x=$x&y=$y&CLevel=$CLevel";
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$data = curl_exec($ch);
	while(curl_errno($ch) == 28 && $retry < 5)
	{
		$data = curl_exec($ch);
		$retry++;
	}
	echo "$url<br>";
	echo "$data";
	curl_close($ch);
}/**/

/*
$NameHash = hash("adler32",$name);
$query1 = "select rowid,Lvl$Level from PlayerHunts where Day=$day and year=$year and PlayerName = '".mysql_real_escape_string($name)."' limit 0,1";
$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
list($rowid,$killcount) = mysql_fetch_row($result1);	
*/
function GetMonsterLevel($Type)
{
	global $dbi;
//	$NameHash = hash("adler32",$name);
	$query1 = "select MonsterLevel from MonsterTypes where MonsterType='$Type' limit 0,1";
	$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	list($MonsterLevel) = mysqli_fetch_row($result1);
	if(!isset($MonsterLevel))
	{
		$query1 = "select MonsterLevel from MonsterTypes where MonsterType='".($Type & 0xFF)."' limit 0,1";
		$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
		list($MonsterLevel) = mysqli_fetch_row($result1);
	}
	if(!isset($MonsterLevel))
	{
		$query1 = "insert into MonsterTypes (MonsterType,MonsterLevel)values($Type,0)";
		$result1 = mysqli_query($dbi,$query1) or die("Error : 2017022003 <br> ".$query1." <br> ".mysqli_error($dbi));
		return 0;
	}	
	else
	{
		$query1 = "update MonsterTypes set HuntedCount= HuntedCount + 1 where MonsterType='$Type'";
		$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	}
	return $MonsterLevel;
}
/*
function GetPlayerID($name)
{
	global $dbi;
	$NameHash = hash("adler32",$name);
	$query1 = "select rowid from PlayerNames where HashedName='$NameHash' and name = '".mysql_real_escape_string($name)."' limit 0,1";
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
	list($rowid) = mysql_fetch_row( $result1 );	
	//create new 
	if( !isset($rowid) || $rowid == 0 )
	{
	}
}

function CreateNewPlayer($name)
{
	global $dbi;
	$NameHash = hash("adler32",$name);
	$query1 = "insert into PlayerNames (HashedName,name)values('$NameHash','".mysql_real_escape_string($name)."')";
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
	return GetPlayerID($name);
}*/

?>