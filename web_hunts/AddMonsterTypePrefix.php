<?php
require_once("db_connection.php");

$query1 = "DELETE FROM MonsterTypes WHERE MonsterName=''";
$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));

$MonsterNameList = "'1'";

AddNewPrefix(195,10); // voodoo shaman
AddNewPrefix(203,9); // frostwing
AddNewPrefix(59,10);
AddNewPrefix(44,10); // grim reaper
AddNewPrefix(105,10); //queen bee
AddNewPrefix(120,10);
AddNewPrefix(64,10);
AddNewPrefix(17,10);
AddNewPrefix(228,9); //gryph
AddNewPrefix(223,9); //snow beast
AddNewPrefix(130,10); //tidal titan
AddNewPrefix(234,10); //hardrox
AddNewPrefix(22,10); //megga maggot
AddNewPrefix(87,10); //blackwing
AddNewPrefix(39,10); //helldrider
AddNewPrefix(92,10); //mecha trojan
AddNewPrefix(12,10); // terror thorn
AddNewPrefix(7,10); // jade wyrm
AddNewPrefix(39,11); // hootclaw

/*
echo "Insert ignore into MonsterTypes (MonsterType, MonsterName, MonsterLevel) values (39 + 11 * 256, 'hootclaw lvl 1', 1);<br>";
echo "Insert ignore into MonsterTypes (MonsterType, MonsterName, MonsterLevel) values (40 + 11 * 256, 'hootclaw lvl 2', 2);<br>";
echo "Insert ignore into MonsterTypes (MonsterType, MonsterName, MonsterLevel) values (41 + 11 * 256, 'hootclaw lvl 3', 3);<br>";
echo "Insert ignore into MonsterTypes (MonsterType, MonsterName, MonsterLevel) values (42 + 11 * 256, 'hootclaw lvl 4', 4);<br>";
echo "Insert ignore into MonsterTypes (MonsterType, MonsterName, MonsterLevel) values (43 + 11 * 256, 'hootclaw lvl 5', 5);<br>";
*/

$query1 = "select MonsterName from MonsterTypes where MonsterName not in ($MonsterNameList)";
$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
echo "Still missing types<br>";
while(list($MonsterName) = mysqli_fetch_row($result1))
	echo $MonsterName."<br>";

function AddNewPrefix($OldId,$NewPrefix)
{
	global $dbi,$MonsterNameList;
	$query1 = "select MonsterName from MonsterTypes where MonsterType=$OldId";
	$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	list($MonsterName) = mysqli_fetch_row($result1);
	if(strlen($MonsterName)<=1)
	{
		echo "#Monster $OldId is missing from database !!!<br>";
		return;
	}
	//remove level from name
	$MonsterName = str_replace(array(" 1"," 2"," 3"," 4"," 5"), "", $MonsterName);
	//get all the possible Ids for this mosnter
	$query1 = "select * from MonsterTypes where MonsterName like '$MonsterName%' and MonsterType<256 order by monsterlevel asc";
	$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	$RowCount = 0;
	while(list($Type, $Name, $Level) = mysqli_fetch_row($result1))
	{
		$NewType = $Type + $NewPrefix * 256;
		$q2 = "Insert ignore into MonsterTypes (MonsterType, MonsterName, MonsterLevel) values ($NewType, '$Name', $Level)";
		$result2 = mysqli_query($dbi, $q2) or die("Error : 2017022002 <br> ".$q2." <br> ".mysqli_error($dbi));
		echo $q2.";<br>";
		$RowCount++;
		$MonsterNameList .= ",'$Name'";
	}
	if($RowCount != 5)
		echo "!!!!!!!!!!missing or extra monster!!!<br>";
}
