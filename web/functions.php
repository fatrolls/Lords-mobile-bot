<?php

function GetTimeDiffShortFormat($time, $IsDiff = 0)
{
	if($time==0)
		return "";
	if(!isset($IsDiff) || $IsDiff == 0 )
		$diff = time() - $time;
	else
		$diff = $time;
//echo $time." - ".$diff." ; ";	
	if($diff > 356 * 24 * 60 * 60 )
	{
		$diff = ((int)($diff / 356 / 24 / 60 / 6 ) / 10 );
		if( $diff >= "48")
			$diff = "";
		else
			$diff .= " y";
	}
	else if($diff > 31 * 24 * 60 * 60 )
		$diff = ((int)($diff / 31 / 24 / 60 / 6 ) / 10 ) ."m";
	else if($diff > 24 * 60 * 60 )
		$diff = ((int)($diff / 24 / 60 / 6 ) / 10 ) ."d";
	else if($diff > 60 * 60 )
		$diff = ((int)($diff / 60 / 6 ) / 10 )."h";
	else if($diff>60)
		$diff = ( (int)($diff / 6 ) / 10 )."m";
	else
		$diff = $diff."s";
	return $diff;
}

//\s is interpreted as a token when using "like"
function mysql_escape_str_like( $str )
{
	$estr = $str;
	$estr = str_replace("\\s","\\\\s",$estr);
	$estr = str_replace("\\S","\\\\S",$estr);
	$estr = str_replace("\\F","\\\\F",$estr);
	$estr = mysql_real_escape_string($estr);
	return $estr;
}

function GetValShortFormat($val)
{
	$sign = 1;
	if($val < 0)
	{
		$sign = -1;
		$val = -$val;
	}
	if($val > 1000000000 )
		$val = ((int)($val / 100000000 ) / 10 ) ."b";
	else if($val > 1000000 )
		$val = ((int)($val / 100000 ) / 10 ) ."m";
	else if($val > 1000 )
		$val = ((int)($val / 100 ) / 10 )."k";
	
	if($sign == -1)
		$val = "-$val";
	
	return $val;
}

$CurCacheFileName = "";
function CacheStartOrLoadCache($NameParam, $period)
{
	global $CurCacheFileName;
	$ParamsList = hash("adler32",$_SERVER['REQUEST_URI']);
	$file = "cache".$_SERVER['PHP_SELF']."_$NameParam $ParamsList.cache";
	$CurCacheFileName = $file;
//	echo $_SERVER['PHP_SELF']."<br>";
	if(is_file($file))
	{
		$timeDiff = time() - filemtime($file);
		if($timeDiff < $period)
		{
//			echo "Loaded from cache<br>";
			echo file_get_contents($file);
			die();
		}
		unlink($file);
	}
	//gzip if we can. Else simply send plain content
	if (substr_count($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip"))
		ob_start("ob_gzhandler"); 
	else 
		ob_start();
}

function AutoCacheEnd()
{
	global $CurCacheFileName;
	$file = $CurCacheFileName;
	$StaticFileContent = ob_get_contents();
	ob_end_clean();
	file_put_contents($file,$StaticFileContent);
	echo $StaticFileContent;
}

function UpdateUsedMap($x,$y)
{
	global $dbi;
	$xl = (int)($x/31);
	$xs = (int)($x % 31);
	$flag = 1 << $xs;
	$key = $y * 10000 + $xl;

	$query1 = "select mask,1 from used_locations where `key`=$key";
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1." ".mysql_error($dbi));
	list( $mask, $exists ) = mysql_fetch_row( $result1 );
	
	$mask = $mask | $flag;
	
	if($exists==0)
	{
		$query1 = "insert ignore into used_locations values ($key,$mask)";
		mysql_query($query1,$dbi) or die("2017022001".$query1." ".mysql_error($dbi));
	}
	else
	{
		$query1 = "update used_locations set mask=$mask where `key`=$key";
//echo "$query1<br>";return;
		mysql_query($query1,$dbi) or die("2017022001".$query1." ".mysql_error($dbi));
	}
}
?>