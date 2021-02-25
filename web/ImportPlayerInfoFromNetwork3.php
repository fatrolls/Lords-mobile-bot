<?php
//set_time_limit(2 * 60 * 60);
set_time_limit(5);
$DisableCaching=1;
include("db_connection.php");

$MergedQueries = "";
if(!isset($x) && !isset($y) )
	ImportFromFile();
else
	ImportFromWebCall();

//now run all the queries we stacked up during this execution
//RemoteRunQuery2($MergedQueries);

function ImportFromWebCall()
{
	global $k,$x,$y,$name,$guild,$CLevel,$guildF,$kills,$vip,$GuildRank,$might,$LastUpdated,$objtype,$StatusFlags,$title,$monstertype,$MaxAmtNow;
	echo "Importing from url<br>";
	$LastUpdated = time();
	UpdateUsedMap($x,$y);
//echo "we are importing type '$objtype' $x $y";
	//guess it's a player
	if(!isset($objtype) || $objtype == 8)
	{
		Insert1Line($k,$x,$y,$name,$guild,$CLevel,$guildF,$kills,$vip,$GuildRank,$might,$StatusFlags,$title);
		PostImportActions();
	}
	else if($objtype==10)
		Insert1LineMonster($x,$y,$CLevel,$monstertype);
	else if($objtype>=1 && $objtype<=6)
		Insert1LineMineral($objtype,$x,$y,$name,$CLevel,$MaxAmtNow);
	//http://10.50.160.60:8081/ImportPlayerInfoFromNetwork.php?k=67&x=1&y=2&name=Tudi&guild=wib&CLevel=3&kills=4&vip=5&GuildRank6&might=7&HasPrisoners=8&PLevel=9&guildF=sea wolves
	//http://127.0.0.1:8081/ImportPlayerInfoFromNetwork3.php?k=$k&x=$x&y=$y&name=$name&guild=$guild&CLevel=$CLevel&kills=$kills&vip=$vip&GuildRank=$GuildRank&might=$might&HasPrisoners=$HasPrisoners&PLevel=$PLevel&guildF=$guildF
}

function PostImportActions()
{
	global $dbi,$LastUpdated;

	//delete all from players that is older than 3 days old without update
	if($LastUpdated>0)
	{
		$olderthan = $LastUpdated - 3 * 24 * 60 * 60;
		ArchivePlayerIfNotArtchived( "LastUpdated < $olderthan" );
	}
}

function Insert1Line($k,$x,$y,$name,$guild,$CLevel,$guildF,$kills,$vip,$GuildRank,$might,$StatusFlags,$title)
{
	global $dbi,$LastUpdated;
	
	if( strpos("#".$name,"Dark.nest") == 1 )
		return;

	//we will always receive these 3 values : $name,$guild,$CLevel

	//do we have missing data ? Can we load it from previous version ?
	// load old data for this player
	$query1 = "select rowid,kills,might,vip,guildrank,GuildFull,StatusFlags,title from players where name = '".mysql_real_escape_string($name)."' limit 0,1";
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
	list( $rowid,$kills2,$might2,$vip2,$guildrank2,$GuildFull2,$StatusFlags2,$title2 ) = mysql_fetch_row( $result1 );	
//echo $query1;
	// player got renamed ? Try to search it based on coords
	if( $rowid==0 || $rowid == "" )
	{
		if($kills>0 && $vip>0)
			$query1 = "select rowid,kills,might,vip,guildrank,GuildFull,StatusFlags,title,name from players where x='$x' and y='$y' and kills<='$kills' and vip<='$vip' and castlelevel='$CLevel' and guild = '".mysql_real_escape_string($guild)."' limit 0,1";
		else
			$query1 = "select rowid,kills,might,vip,guildrank,GuildFull,StatusFlags,title,name from players where x='$x' and y='$y' and castlelevel='$CLevel' and guild = '".mysql_real_escape_string($guild)."' limit 0,1";
		$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
		list( $rowid2,$kills2,$might2,$vip2,$guildrank2,$GuildFull2,$StatusFlags2,$title2,$oldname ) = mysql_fetch_row( $result1 );			
		//save the namechange
		if($rowid2>0 && $name!=$oldname) //wtf bug sometimes it can not find the name but it has the same name ? Happened 10 times in 1 day
		{
			//check already exists
			$query1 = "select count(*) from player_renames where name1 = '".mysql_real_escape_string($oldname)."' and name2 = '".mysql_real_escape_string($name)."' limit 0,1";
			$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
			list( $IsInserted ) = mysql_fetch_row( $result1 );	
			if($IsInserted=="" ||$IsInserted<=0)
			{
				$query1 = "insert into player_renames values('".mysql_real_escape_string($oldname)."','".mysql_real_escape_string($name)."','".mysql_real_escape_string($guild)."',UNIX_TIMESTAMP())";
				RunSyncQuery($query1);
			}
		}
	}
	//if we have old records, archive and delete them
	if( $rowid > 0 )
		ArchivePlayerIfNotArtchived( "rowid=$rowid", 1 );
	
	//restore non updated from old values
	if(!isset($kills) || $kills == 0)
		$kills = $kills2;
	if(!isset($might) || $might == 0)
		$might = $might2;
	if(!isset($vip) || $vip == 0)
		$vip = $vip2;
	if(!isset($GuildRank))
		$GuildRank = $guildrank2;
	if(!isset($StatusFlags) || $StatusFlags == 0 )
		$StatusFlags = $StatusFlags2;
	if(!isset($title) || $title == 0 )
		$title = $title2;
	if(!isset($guildF) || $guildF == "")
		$guildF = $GuildFull2;
	if(!isset($guildF) || $guildF == "")
	{
		//try to get it from players archive using same player name
		$query1 = "select GuildFull from players_archive where guild = '".mysql_real_escape_string($guild)."' and name = '".mysql_real_escape_string($name)."' order by rowid desc limit 0,1";
		$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
		list( $PossibleLongName ) = mysql_fetch_row( $result1 );	
		if( $PossibleLongName != "" )
			$guildF = $PossibleLongName;
	}
	
	//there can be only 1 at this coordinate. Everything older than us has to go
	$query1 = "delete from players where x='$x' and y='$y'";
	//$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
	RunSyncQuery($query1);
	
	$LastUpdated += 1;
	
//if(strlen($NewGuild)<=5 && $NewGuild!="None")die("not good enough $NewGuild");
	//insert new
	$query1 = "insert into players ( x,y,name,guild,guildfull,kills,might,lastupdated,VIP,GuildRank,CastleLevel,StatusFlags,title)values(";
	$query1 .= "'".mysql_real_escape_string($x)."'";
	$query1 .= ",'".mysql_real_escape_string($y)."'";
	$query1 .= ",'".mysql_real_escape_string($name)."'";
	$query1 .= ",'".mysql_real_escape_string($guild)."'";
	$query1 .= ",'".mysql_real_escape_string($guildF)."'";
	$query1 .= ",'".mysql_real_escape_string($kills)."'";
	$query1 .= ",'".mysql_real_escape_string($might)."'";
	$query1 .= ",'".mysql_real_escape_string($LastUpdated)."'";
	$query1 .= ",'".mysql_real_escape_string($vip)."'";
	$query1 .= ",'".mysql_real_escape_string($GuildRank)."'";
	$query1 .= ",'".mysql_real_escape_string($CLevel)."'";
	$query1 .= ",'".mysql_real_escape_string($StatusFlags)."'";
	$query1 .= ",'".mysql_real_escape_string($title)."'";
	$query1 .= ")";

//continue;			
//			echo "$query1<br>";
	//$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	RunSyncQuery($query1);
	
	//delete old ones in the neighbourhood
	$xmin = $x - 10;
	$xmax = $x + 10;
	$ymin = $y - 10;
	$ymax = $y + 10;
	$olderthan = $LastUpdated - 24 * 60 * 60;
	//move to archive without double insert
	ArchivePlayerIfNotArtchived( "x < $xmax and x > $xmin and y < $ymax and y < $ymin and LastUpdated < $olderthan" );
}

function ArchivePlayerIfNotArtchived( $where )
{	
	global $dbi;
	$query1 = "select rowid,x,y,name,guild,kills,might,statusflags,vip,guildrank,castlelevel,title from players where $where";
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
	while( list( $rowid,$x,$y,$name,$guild,$kills,$might,$statusflags,$vip,$guildrank,$clevel,$title ) = mysql_fetch_row( $result1 ))
	{
		$query1 = "select rowid from players_archive where x='$x' and y='$y' and kills='$kills' and might='$might' and vip='$vip' and guildrank='$guildrank' and castlelevel='$clevel' and StatusFlags='$statusflags' and title='$title' and name = '".mysql_real_escape_string($name)."' and guild = '".mysql_real_escape_string($guild)."' limit 0,1";
		$result2 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
		list( $rowid2 ) = mysql_fetch_row( $result2 );
		if( $rowid2 == 0 || $rowid2 == "" || !isset($rowid2) )
		{
			$query1 = "insert ignore into players_archive (select * from players where rowid=$rowid)";
//file_put_contents("t.txt",$query1);
			//$result1 = mysql_query($query1,$dbi) or die("Error : 20170220022 <br>".$query1." <br> ".mysql_error($dbi));
			RunSyncQuery($query1);
		}
		//delete from actual
		$query1 = "delete from players where rowid=$rowid";
		//$result1 = mysql_query($query1,$dbi) or die("Error : 20170220023 <br>".$query1." <br> ".mysql_error($dbi));	
		RunSyncQuery($query1);
	}
}

function RunSyncQuery($query1)
{
//echo $query1;
//return;
	global $dbi, $MergedQueries;
	//run it in our DB
	$result1 = mysql_query($query1,$dbi) or die("Error : 20170220022 <br>".$query1." <br> ".mysql_error($dbi));	
	//run it on the remote machine
	$MergedQueries .= $query1.";NEXT_QUERY;";
//echo "MergedQueries now : $MergedQueries<br>";
}

function RemoteRunQuery2($query1)
{
	global $k;
	if( strlen($query1) < 10 )
		return;
//	file_put_contents("online_queries.txt", trim($query1)."\n\r", FILE_APPEND);
//return;
//	echo "MergedQueries : $query1<br>"; die();
//	$url = 'http://127.0.0.1:8081/RunQueries.php';
	$url = 'http://5.79.67.171/RunQueries.php';
//	$url = 'http://lordsmobile.online/RunQueries.php';
	$data = array('z' => '-1', 'k' => $k, 'queries' => $query1);
	$options = array(
			'http' => array(
			'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
			'method'  => 'POST',
			'content' => http_build_query($data),
		)
	);

	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);	
	echo "$result";
}
/*
function RemoteRunQuery($query1)
{
	if( strlen($query1) < 10 )
		return;
	
	$data = array('z' => '-1', 'queries' => $query1);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL,"http://5.79.67.171/RunQueries.php");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec ($ch);

	curl_close ($ch);	
	echo "Remote query reply :";
	var_dump( $server_output );
}
*/
function Insert1LineMonster($x,$y,$CLevel,$monstertype)
{
	global $dbi,$LastUpdated;	
	//replace or update existing
	$query1 = "replace into monsters (rowid,x,y,mtype,level,lastupdated)values(";
	$query1 .= "'".mysql_real_escape_string($x * 65536 + $y)."'";
	$query1 .= ",'".mysql_real_escape_string($x)."'";
	$query1 .= ",'".mysql_real_escape_string($y)."'";
	$query1 .= ",'".mysql_real_escape_string($monstertype)."'";
	$query1 .= ",'".mysql_real_escape_string($CLevel)."'";
	$query1 .= ",'".mysql_real_escape_string($LastUpdated)."'";
	$query1 .= ")";
	RunSyncQuery($query1);
	
	//delete from actual
	$olderthan = $LastUpdated - 3 * 24 * 60 * 60;
	$query1 = "delete from monsters where LastUpdated < $olderthan";
	RunSyncQuery($query1);
}

function Insert1LineMineral($type,$x,$y,$name,$CLevel,$MaxAmtNow)
{
	global $dbi,$LastUpdated;	
	//replace or update existing
	$query1 = "replace into resource_nodes (rowid,x,y,rtype,level,max_now,playername,lastupdated)values(";
	$query1 .= "'".mysql_real_escape_string($x * 65536 + $y)."'";
	$query1 .= ",'".mysql_real_escape_string($x)."'";
	$query1 .= ",'".mysql_real_escape_string($y)."'";
	$query1 .= ",'".mysql_real_escape_string($type)."'";
	$query1 .= ",'".mysql_real_escape_string($CLevel)."'";
	$query1 .= ",'".mysql_real_escape_string($MaxAmtNow)."'";
	$query1 .= ",'".mysql_real_escape_string($name)."'";
	$query1 .= ",'".mysql_real_escape_string($LastUpdated)."'";
	$query1 .= ")";
	RunSyncQuery($query1);
	
	//delete from actual
	$olderthan = $LastUpdated - 3 * 24 * 60 * 60;
	$query1 = "delete from resource_nodes where LastUpdated < $olderthan";
	RunSyncQuery($query1);
}
?>