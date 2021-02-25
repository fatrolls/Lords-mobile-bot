<?php
function GetYear()
{
	return date("Y", GetCompensatedTime());
}

function GetDayOfYear($TimeMod=0)
{
	return date('z', GetCompensatedTime($TimeMod)) + 1;
}

function getDateFromDay($dayOfYear, $year) 
{
  $date = DateTime::createFromFormat('z Y', strval($dayOfYear) . ' ' . strval($year));
  return $date;
}

function GetCompensatedTime($TimeMod=0)
{
	global $GameServerTimeDifference;
	return time() + $GameServerTimeDifference * 60 + $TimeMod;
}

function GetCompensatedDate()
{
	return date('Y-m-d H:i:s', GetCompensatedTime());
}

function OrderMergedList($l)
{
	$RowsAdded=0;
	do{
		$PN = "";
		$BestScore = -1;
		foreach($l as $key => $Stats)
			if($Stats[0] > $BestScore)
			{
				$BestScore = $Stats[0];
				$PN = $key;
			}
		if($BestScore != -1)
		{
			for($i=-1;$i<6;$i++)
				$ret[$RowsAdded][$i]=$l[$PN][$i];
			$RowsAdded++;
			$l[$PN][0] = -1;
		}
	}while($BestScore != -1);
	if(!isset($ret))
		return NULL;
	return $ret;
}
function CalcNumberOfDaysWorthOfHunts($Stats)
{
	$DaysWorth = 0;
	$Sum = $Stats[1]+$Stats[2]+$Stats[3]+$Stats[4]+$Stats[5];
	do{
/*		if($Stats[1] >= 5)
		{
			$DaysWorth++;
			$Stats[1] -= 5;
		}
		else if($Stats[2] >= 5)
		{
			$DaysWorth++;
			$Stats[2] -= 5;
		}
		else if($Stats[3] >= 5)
		{
			$DaysWorth++;
			$Stats[3] -= 5;
		}
		else if($Stats[4] >= 5)
		{
			$DaysWorth++;
			$Stats[4] -= 5;
		}
		else if($Stats[5] >= 5)
		{
			$DaysWorth++;
			$Stats[5] -= 5;
		}		*/
		if( $Sum >= 5)
		{
			$Sum -= 5;
			$DaysWorth++;
		}
		else
			break;		
		/*
		if($Stats[1] >= 15 && $Stats[2] >= 3)
		{
			$DaysWorth++;
			$Stats[1] -= 15;
			$Stats[2] -= 3;
		}
		else if($Stats[2] >= 7)
		{
			$DaysWorth++;
			$Stats[2] -= 7;
		}
		else if($Stats[3] >= 2)
		{
			$DaysWorth++;
			$Stats[3] -= 2;
		}
		else if($Stats[4] >= 1)
		{
			$DaysWorth++;
			$Stats[4] -= 1;
		}
		else if($Stats[5] >= 1)
		{
			$DaysWorth++;
			$Stats[5] -= 1;
		}		
		else
			break;
		*/
	}while(1);
	return $DaysWorth;
}
function SafeToExecuteOnMysql($val)
{
	$len = strlen($val);
	$isSafe = 0;
	for($i=0;$i<$len;$i++)
	{
		if($val[$i]=='\\' || $val[$i]=='\`' || $val[$i]=='\'' || $val[$i]=='\"' || $val[$i]==';' || $val[$i]=='\n' || $val[$i]=='\r' || $val[$i]==0x1a)
		{
//			echo "'".$val[$i]."'";
			return 0;
		}
		if($val[$i] >= 'a' && $val[$i] <= 'z')
			$isSafe++;
		if($val[$i] >= 'A' && $val[$i] <= 'Z')
			$isSafe++;
		if($val[$i] >= '0' && $val[$i] <= '9')
			$isSafe++;
		if($val[$i] == ' ')
			$isSafe++;
	}
//	echo "$isSafe==$len";
	return $isSafe==$len;
}

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

function mysql_real_escape_string_($inp)
{
    if(is_array($inp))
        return array_map(__METHOD__, $inp);

    if(!empty($inp) && is_string($inp)) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
    }

    return $inp; 
}

function ValueShortFormat($val)
{
	if(abs($val)>1000000000)
		return (int)($val/1000000000)."B";
	if(abs($val)>1000000)
		return (int)($val/1000000)."M";
	if(abs($val)>1000)
		return (int)($val/1000)."K";
	return $val;
}
?>