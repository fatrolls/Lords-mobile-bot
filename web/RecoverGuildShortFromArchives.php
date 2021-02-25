<?php
include("db_connection.php");
$query1 = "Select rowid,name,guild from players";
$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
while( list( $rowid,$name,$guild ) = mysql_fetch_row( $result1 ))
{
	if($name[0]=='[')
	{
		$IsInGuildPos = strpos($name,']');
		if($IsInGuildPos>0)
			$namename = substr($name,$IsInGuildPos);
		else
			$namename = $name;
	}
	//do we have a match in archives for this player
	$query2 = "Select count(*),rowid,name,guild from players_archive where name = '%$namename' and guild <> '$guild' group by guild";
echo $query2; die();
	$result2 = mysql_query($query2,$dbi) or die("2017022001".$query1);
	list( $rowid2,$name2,$guild2 ) = mysql_fetch_row( $result2 );
}
?>