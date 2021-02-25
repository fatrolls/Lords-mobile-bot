<?php
set_time_limit(1 * 60 * 60);
include("db_connection.php");

$query1 = "select name,guild,guildF,kills,might,VIP,GuildRank,CastleLvl,x,y from players_network";
$result1 = mysql_query($query1,$dbi) or die("Error : 20170220023 <br>".$query1." <br> ".mysql_error($dbi));
while(list($name,$guild,$guildF,$kills,$might,$VIP,$GuildRank,$CastleLvl,$x1,$y1)=mysql_fetch_row( $result1 ))
{
	if(strlen($guild)>0)
	{
		$NewName = "[$guild]$name";
		$NewGuild = "[$guild]$guildF";
	}
	else
	{
		$NewName = "$name";
		$NewGuild = "";
	}

	$query3 = "update players set ";
	$query3 .= " name='".mysql_real_escape_string($NewName)."'";
	$query3 .= ",guild='".mysql_real_escape_string($NewGuild)."'";
	$query3 .= ",VIP='".mysql_real_escape_string($VIP)."'";
	$query3 .= ",GuildRank='".mysql_real_escape_string($GuildRank)."'";
	$query3 .= ",CastleLevel='".mysql_real_escape_string($CastleLvl)."'";
	
	$where = "";
	
	//does a row in real player table exist ?
	// should use this query ONLY for same day scans. No older scan should be used
	$RowCount=0;
	if($RowCount==0 || $RowCount>1)
	{
		$where = "name like '%".mysql_real_escape_string($name)."' and kills>=$kills and might>=$might";
		$query2 = "select count(*) from players where $where";
		$result2 = mysql_query($query2,$dbi) or die("Error : 20170220023 <br>".$query2." <br> ".mysql_error($dbi));
		list($RowCount)=mysql_fetch_row( $result2 );
	}
	if($RowCount==0 || $RowCount>1)
	{
		$where = "name like '%".mysql_real_escape_string($name)."'";	// there is always a high chance OCR will read the name incorrectly
		$query2 = "select count(*) from players where $where";
		$result2 = mysql_query($query2,$dbi) or die("Error : 20170220023 <br>".$query2." <br> ".mysql_error($dbi));
		list($RowCount)=mysql_fetch_row( $result2 );
	}
	if($RowCount==0 || $RowCount>1)
	{
		$where = "might='$might' AND kills='$kills' and vip=$VIP and guildrank=$GuildRank";
		$query2 = "select count(*) from players where $where";
		$result2 = mysql_query($query2,$dbi) or die("Error : 20170220023 <br>".$query2." <br> ".mysql_error($dbi));
		list($RowCount)=mysql_fetch_row( $result2 );
	}
	if($RowCount==0 || $RowCount>1)
	{
		$where = "might='$might' AND kills='$kills' and vip>=$VIP and guildrank>=$GuildRank";
		$query2 = "select count(*) from players where $where";
		$result2 = mysql_query($query2,$dbi) or die("Error : 20170220023 <br>".$query2." <br> ".mysql_error($dbi));
		list($RowCount)=mysql_fetch_row( $result2 );
	}
	if($RowCount==0 || $RowCount>1)
	{
		$where = "might='$might' or kills='$kills' and (vip=$VIP and guildrank=$GuildRank)";
		$query2 = "select count(*) from players where $where";
		$result2 = mysql_query($query2,$dbi) or die("Error : 20170220023 <br>".$query2." <br> ".mysql_error($dbi));
		list($RowCount)=mysql_fetch_row( $result2 );
	}
	if($RowCount==0 || $RowCount>1)
	{
		$where = "might='$might' or kills='$kills' and (vip>=$VIP and guildrank>=$GuildRank)";
		$query2 = "select count(*) from players where $where";
		$result2 = mysql_query($query2,$dbi) or die("Error : 20170220023 <br>".$query2." <br> ".mysql_error($dbi));
		list($RowCount)=mysql_fetch_row( $result2 );
	}
	
	if($RowCount==1)
	{
		$SuccessCount++;
		$query3 .= " where $where";
		$result3 = mysql_query($query3,$dbi) or die("Error : 20170220023 <br>".$query3." <br> ".mysql_error($dbi));
//		echo "$query3<br><br>";
	}
	else
	{
		$FailCount++;
		echo "$FailCount / $SuccessCount)Could not update player $NewName <br><br>";
	}
}

include("PostImportActions.php");
?>