<?php
set_time_limit(2 * 60 * 60);
$DisableCaching=1;
//do not run this in parallel
if(file_exists( "ImportLock" ))
{
	$LastAccessed = fileatime("ImportLock");
	if(time() - $LastAccessed < 1 * 60 * 60)
		die("locked");
}
file_put_contents("ImportLock","locked");

if(!isset($dbi))
	include("db_connection.php");

$StatusFlagInnactive = 0x01000000;

//update innactivity column
$query1 = "update players set StatusFlags=StatusFlags & ~($StatusFlagInnactive) order by rowid";
$result1 = mysql_query($query1,$dbi) or die("Error : 20170220027 <br>".$query1." <br> ".mysql_error($dbi));
// a player is innactive if he did not change coordinate and he's might did not change in the past X days
$query1 = "select rowid,x,y,name,might,lastupdated,vip,castlelevel from players";
$result1 = mysql_query($query1,$dbi) or die("Error : 20170220024 <br>".$query1." <br> ".mysql_error($dbi));
while( list( $rowid,$x,$y,$name,$might,$lastupdated,$vip,$castlelevel ) = mysql_fetch_row( $result1 ))
{
	// check he's might yesterday or anywhere before last seen him here. 
	// Kill count might go up when he is defending because of traps
	// should cgeck reource mined
	// should check if he had prisoners recently
	// even if might does not change. Troops healed or troops trained might have changed
	$query2 = "select might,lastupdated from players_archive where x=$x and y=$y and lastupdated<".($lastupdated-60*60*24*3)." and name = '".mysql_real_escape_string($name)."' and vip=$vip and castlelevel=$castlelevel limit 0,1";
//echo "$query2<br>";
	$result2 = mysql_query($query2,$dbi) or die("Error : 20170220025 <br>".$query2." <br> ".mysql_error($dbi));
	$MightChanged = -1;
	$SameMightSince = 1;
	while( list( $MightOld,$lastupdated ) = mysql_fetch_row( $result2 ) )
	{
		if( $MightOld != $might )
		{
			$MightChanged = 1;
			break;
		}
		else
		{
			if(	$SameMightSince > $lastupdated )
				$SameMightSince = $lastupdated;
			if( $MightChanged == -1 )
			{
				//maybe he gathered some resources recently ?
				$query2 = "select count(*) from resource_nodes where playername = '".mysql_real_escape_string($name)."'";
				$result2 = mysql_query($query2,$dbi) or die("Error : 20170220024 <br>".$query2." <br> ".mysql_error($dbi));
				list( $NodesGatheredFrom ) = mysql_fetch_row( $result2 );
				if($NodesGatheredFrom<=0)
					$MightChanged = 0;
			}
		}
//echo "Found archive for player $name<br>";
	}
	if($MightChanged==0)
	{
		$query2 = "update players set StatusFlags=StatusFlags | ($StatusFlagInnactive) where rowid=$rowid";
//echo "$query2<br>";
		$result2 = mysql_query($query2,$dbi) or die("Error : 20170220028 <br>".$query2." <br> ".mysql_error($dbi));
//		echo "Player's $name might did not change<br>";
	}
}
//count number of resource nodes player is mining from
$query1 = "update players set MiningNodes=0 order by rowid";
$result1 = mysql_query($query1,$dbi) or die("Error : 20170220027 <br>".$query1." <br> ".mysql_error($dbi));
// a player is innactive if he did not change coordinate and he's might did not change in the past X days
$query1 = "select count(*),playername from resource_nodes where playername!='' and lastupdated>UNIX_TIMESTAMP()-4*60*60 group by playername";
$result1 = mysql_query($query1,$dbi) or die("Error : 20170220024 <br>".$query1." <br> ".mysql_error($dbi));
while( list( $count,$name ) = mysql_fetch_row( $result1 ))
{
	$query2 = "update players set MiningNodes='$count' where name = '".mysql_real_escape_string($name)."'";
	$result2 = mysql_query($query2,$dbi) or die("Error : 20170220024 <br>".$query2." <br> ".mysql_error($dbi));
}

//generate new hives
include("gen_hives.php");
include("gen_hives_small.php");
/**/
//generate static minimaps
$TrackWhat = "might";
include("map_generic.php");
$TrackWhat = "kills";
include("map_generic.php");
$TrackWhat = "pcount";
include("map_generic.php");
$TrackWhat = "guildless";
include("map_generic.php");
$TrackWhat = "guildless_innactive";
include("map_generic.php");
$TrackWhat = "castlelevel";
include("map_generic.php");
$TrackWhat = "resourcelevel";
include("map_generic.php");
$TrackWhat = "resourcefree";
include("map_generic.php");

unlink("ImportLock");
?>