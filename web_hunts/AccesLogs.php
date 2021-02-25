<?php
if( strpos($_SERVER['SCRIPT_FILENAME'],"UploadData.php")==0 )
{
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	$query1 = "insert into access_logs (script,URI,IP,stamp)values('".mysql_real_escape_string_($_SERVER['SCRIPT_FILENAME'])."','".mysql_real_escape_string_($_SERVER['REQUEST_URI'])."','$ip','".mysql_real_escape_string_($_SERVER['REQUEST_TIME'])."')";		
	$result1 = mysqli_query($dbi,$query1) or die("Error : 20170220041630 <br>".$query1." <br> ".mysqli_error($dbi));
}

/*
CREATE TABLE IF NOT EXISTS `access_logs` (
  `Script` varchar(250) DEFAULT NULL,
  `URI` varchar(250) DEFAULT NULL,
  `IP` varchar(250) DEFAULT NULL,
  `Stamp` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
*/
?>