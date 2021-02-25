<?php
ini_set('memory_limit','640M');
//$DisableCaching=1;
if(!isset($dbi))
	include("db_connection.php");

echo "All data is generated based on past 31 days(if available).<br>";

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$query1 = "select 1 from players_archive where lastupdated>UNIX_TIMESTAMP()-31*24*60*60 group by name";
$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
$PlayersSeenTotal = mysql_num_rows($result1);

$query1 = "select 1 from players where lastupdated>UNIX_TIMESTAMP()-31*24*60*60 group by name";
$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
$PlayersSeenNow = mysql_num_rows($result1);

echo "Players seen in total: $PlayersSeenTotal<br>";
echo "Players seen now : $PlayersSeenNow. Difference is ".($PlayersSeenTotal-$PlayersSeenNow)." ( = players who temporary visited are also counted in )<br>";

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$query1 = "select count(*) from player_renames where NewNameSeenAt>UNIX_TIMESTAMP()-31*24*60*60";
$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
list( $PlayerRenames ) = mysql_fetch_row( $result1 );

echo "Player renames seen : $PlayerRenames <br>";

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$query1 = "select 1 from players_archive where lastupdated>UNIX_TIMESTAMP()-31*24*60*60 group by concat( (x*10000+y), name )";
$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
$PlayerRelocates = mysql_num_rows($result1);
//list( $PlayerRelocates ) = mysql_fetch_row( $result1 );

echo "Player relocates found : $PlayerRelocates <br>";

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//select oldest but not older than 31 days data for players
$query1 = "select kills,might,name from players";
$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
$SumKillsStart = 0;
$SumKillsNow = 0;
$SumMightStart = 0;
$SumMightNow = 0;
while( list( $kills,$might,$name ) = mysql_fetch_row( $result1 ))
{
	$query2 = "select kills,might from players_archive where lastupdated>UNIX_TIMESTAMP()-31*24*60*60 and name = '".mysql_real_escape_string($name)."' order by lastupdated asc limit 0,1";
	$result2 = mysql_query($query2,$dbi) or die("2017022001".$query2);
	list( $kills2,$might2 ) = mysql_fetch_row( $result2 );
	if($might>0)
	{
		$SumKillsStart += $kills2;
		$SumKillsNow += $kills;
		$SumMightStart += $might2;
		$SumMightNow += $might;
	}
}

echo "Player kills old : ".GetValShortFormat($SumKillsStart)." <br>";
echo "Player kills now : ".GetValShortFormat($SumKillsNow)." . Difference ".GetValShortFormat($SumKillsNow-$SumKillsStart)."<br>";

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo "Player might old : ".GetValShortFormat($SumMightStart)." <br>";
echo "Player might now : ".GetValShortFormat($SumMightNow)." . Difference ".GetValShortFormat($SumMightNow-$SumMightStart)."<br>";

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//select oldest but not older than 31 days data for players
$query1 = "select might,name from players_archive order by name,lastupdated";
$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
$PrevMight = 0;
$PrevName = "";
$SumMightBurned = 0;
while( list( $might,$name ) = mysql_fetch_row( $result1 ))
{
	if($PrevName == $name)
	{
		if($PrevMight>$might)
			$SumMightBurned += ($PrevMight-$might);
	}
	$PrevMight = $might;
	$PrevName = $name;
}
echo "Might burned(recovered later ) : ".GetValShortFormat($SumMightBurned)."<br>";

include("db_connection_footer.php");

?>