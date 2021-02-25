<?php
ini_set('memory_limit','640M');
set_time_limit(15 * 60);

if(!isset($dbi))
	include("db_connection.php");


// old count = 20885
// new count = 20722
$query1 = "select rowid,x,y,lastupdated from players order by lastupdated desc";		
$result1 = mysql_query($query1,$dbi) or die("Error : 20170220041 <br>".$query1." <br> ".mysql_error($dbi));
while( list( $rowid,$x,$y,$lastupdated ) = mysql_fetch_row( $result1 ))
{
	//delete all other rows that have same coordinate but are older than us
	$query1 = "delete from players where rowid<>$rowid and x=$x and y=$y and lastupdated<$lastupdated";
	$result2 = mysql_query($query1,$dbi) or die("Error : 20170220041 <br>".$query1." <br> ".mysql_error($dbi));
}
/**/

// old count = 575470
// new count = 494036
//purge archives also
$query1 = "select * from players_archive order by lastupdated desc";		
$result1 = mysql_query($query1,$dbi) or die("Error : 20170220041 <br>".$query1." <br> ".mysql_error($dbi));
while( $row = mysql_fetch_assoc( $result1 ) )
{
	//aaaa, what?!?!?!
	if($row['rowid']<=0)
		continue;
	//delete all other rows that have same the same data except row id
	$query1 = "delete from players_archive where rowid<>".$row['rowid'];
	foreach($row as $key => $val )
	{
		if($key!="rowid" && $key!="LastUpdated" && $key!="k" )
			$query1 .= " and ( $key='".mysql_real_escape_string($val)."' or $key='' or isnull($key))";
	}
//	echo $query1;
//	die();
	$result2 = mysql_query($query1,$dbi) or die("Error : 20170220041 <br>".$query1." <br> ".mysql_error($dbi));
	unset( $query1 );
	unset( $result2 );
	unset( $row );
	unset( $key );
	unset( $val );
}
/**/
?>