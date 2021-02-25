<?php
set_time_limit(1 * 60);
$DisableCaching=1;	
include("db_connection.php");

if(!isset($z) || $z != -1 || !isset($queries))
	die("a");

//if(strpos($queries,"from players") <= 0 && strpos($queries,"into players") <= 0)
//	die("b");

//file_put_contents("t.txt",$queries);
//file_put_contents("online_queries.txt", trim($queries)."\n\r", FILE_APPEND);

$MultiQueries = explode(";NEXT_QUERY;",$queries);

foreach($MultiQueries as $key => $val)
if(strlen($val)>10)
{
	$query1 = $val;
//	$result1 = mysql_query($query1,$dbi);	
	$result1 = mysql_query($query1,$dbi) or file_put_contents("failed_online_queries.txt", $query1."\n\r", FILE_APPEND);;	
//	$result1 .= " ". mysql_query($query1,$dbi) or die("Error : 20170220022 <br>".$query1." <br> ".mysql_error($dbi));	
}

//file_put_contents("t1.txt",$result1);
?>