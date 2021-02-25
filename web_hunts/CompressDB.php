<?php
require_once("db_connection.php");
$year = GetYear();
$day = GetDayOfYear();
//get a list of distinct player names
$query1 = "select distinct(PlayerName) from PlayerHuntsList where day>=($day-7) and year=$year";
$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
while(list($PlayerName1) = mysqli_fetch_row($result1))
{
	$CountBack=-3;
	do{
		$stats = GetPlayerHuntsForDay($day+$CountBack,$year,$PlayerName1);
		//no more data to work on
		if(@$stats[-1]==1)
			break;
		//update short form
		$query1 = "update PlayerHunts set Lvl1='${stats[1]}',Lvl2='${stats[2]}',Lvl3='${stats[3]}',Lvl4='${stats[4]}',Lvl5='${stats[5]}' where Day=".($day+$CountBack)." and year='$year' and PlayerName='".mysqli_real_escape_string($dbi,$PlayerName1)."'";
echo "$query1<br>";
		$result2 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
		list($matched, $changed, $warnings) = sscanf($dbi->info, "Rows matched: %d Changed: %d Warnings: %d");
		if($changed == 0 && $matched == 0)
		{
			$query1 = "insert into PlayerHunts (PlayerName, Lvl1, Lvl2, Lvl3, Lvl4, year, day) values('".mysqli_real_escape_string($dbi,$PlayerName1)."','${stats[1]}','${stats[2]}','${stats[3]}','${stats[4]}','${stats[5]}',$year,".($day+$CountBack).")";
			$result2 = mysqli_query($dbi,$query1) or die("Error : 2017022001 <br> ".$query1." <br> ".mysqli_error($dbi));
	//		echo $query1;
		}
		//delete long form
		$query1 = "delete from PlayerHuntsList where day=".($day+$CountBack)." and year=$year and playername='".mysqli_real_escape_string($dbi,$PlayerName1)."'";
echo "$query1<br>";
		$result2 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
		$CountBack--;		
	}while(!isset($stats[-1]));
echo "Done Player $PlayerName1<br>";
}

function GetPlayerHuntsForDay($day,$year,$name)
{
	global $dbi;
	//get the best case for this player
	$query1 = "select lvl1,lvl2,lvl3,lvl4,lvl5 from PlayerHunts where day=$day and year=$year and playername='".mysqli_real_escape_string($dbi,$name)."'";
	$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	list($lvl1,$lvl2,$lvl3,$lvl4,$lvl5) = mysqli_fetch_row($result1);
	
	$query1 = "select lvl from PlayerHuntsList where day=$day and year=$year and playername='".mysqli_real_escape_string($dbi,$name)."'";
	$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	while(list($lvl) = mysqli_fetch_row($result1))
		@$stats[$lvl] += 1;
	if(!isset($stats))
		$stats[-1]=1;
	if(@$stats[1]<$lvl1) $stats[1]=$lvl1;
	if(@$stats[2]<$lvl2) $stats[2]=$lvl2;
	if(@$stats[3]<$lvl3) $stats[3]=$lvl3;
	if(@$stats[4]<$lvl4) $stats[4]=$lvl4;
	if(@$stats[5]<$lvl5) $stats[5]=$lvl5;
	return $stats;
}
?>
