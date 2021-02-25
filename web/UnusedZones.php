<?php
ini_set('memory_limit','640M');
set_time_limit( 2* 60 * 60 );
$DisableCaching=1;
if(!isset($dbi))
	include("db_connection.php");

//ParseTable("players");
//ParseTable("monsters");
//ParseTable("resource_nodes");
ParseTable("players_archive");

function ParseTable($table)
{
	global $dbi;
	$query1 = "select x,y from $table";		
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	while( list( $x,$y ) = mysql_fetch_row( $result1 ))
		UpdateUsedMap($x,$y);
}
?>