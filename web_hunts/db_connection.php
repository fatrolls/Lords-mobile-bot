<?php
$GameServerTimeDifference = 0; // given in minutes
if(!isset($db_connection_php))
{
	$db_connection_php = 1;
	
	require_once("functions.php");
	//load cookies
	if (session_status() == PHP_SESSION_NONE || session_id() == '')
		session_start();

	//transform post/get into local variables
	foreach($_REQUEST as $foreachname=>$foreachvalue)
	{
		$$foreachname = $foreachvalue;
	//	echo $foreachname."=$foreachvalue,";
	}

	//connect to DB
	$servername = "localhost";
	$username = "root";
	$password = "";
	$database = "RUM_LM";
	//phpinfo();

	//create global DB connection. Should not forget to close it :P
	$conn = mysqlconnect($servername, $username, $password, $database) or die("Connection failed: " . mysqli_connect_error());
	$dbi = $conn;
	
	require_once("AccesLogs.php");
}

function mysqlconnect($servername, $username, $password, $database)
{
	return mysqli_connect($servername, $username, $password, $database);
/*	$con = mysql_connect($servername, $username, $password, true);
	$ret = mysql_select_db($database, $con) or die("Q 201602091125");
	return $con;*/
}
?>