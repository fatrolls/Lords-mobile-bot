<?php
set_time_limit(2 * 60 * 60);
	
include("db_connection.php");

if(!isset($x) && !isset($y) )
	ImportFromFile();
else
	ImportFromWebCall();

function ImportFromWebCall()
{
	global $x,$y,$name,$guild,$CLevel,$guildF,$kills,$vip,$GuildRank,$might,$HasPrisoners,$PLevel,$LastUpdated;
	echo "Importing from url<br>";
	$LastUpdated = time();
	if(!isset($k))
		$k=67;
	Insert1Line($k,$x,$y,$name,$guild,$CLevel,$guildF,$kills,$vip,$GuildRank,$might,$HasPrisoners,$PLevel);
	PostImportActions();
	//http://10.50.160.60:8081/ImportPlayerInfoFromNetwork.php?k=67&x=1&y=2&name=Tudi&guild=wib&CLevel=3&kills=4&vip=5&GuildRank6&might=7&HasPrisoners=8&PLevel=9&guildF=sea wolves
}

function ImportFromFile()
{
	global $dbi,$LastUpdated,$SkipMapgen;
	$SkipMapgen=0;
	$LastUpdated = time() - 12 * 60 *60;

	echo "Importing from file<br>";
	//$FilesToParse[0] = "parsed_input_03_16.txt";
	//$FilesToParse[1] = "parsed_input_03_10_2.txt";
	if(!isset($FilesToParse))
		return;
	
	foreach($FilesToParse as $key => $val)
		ParseFile($val);
		
	PostImportActions();
	
	if(count($FilesToParse)) include("PostImportActions.php");
}

function ParseFile($FileName)
{
	global $dbi,$LastUpdated,$SkipMapgen;
	$f = fopen($FileName,"rt");
	if(!$f)
	{
		echo("Could not open file");
		return;
	}

	//need to filter out players that do not get updated in this process.
	//If we scanned other plears near him and he did not get updated there is a chance he moved or got renamed
	// we should process "new" players yet unseen, and players that we lost in a special way. There is a chance they simply namechanged or swapped realm
	$x_ind = 0;
	$y_ind = 1;
	$name_ind = 2;
	$guild_ind = 3;
	$Castle_ind = 4;
	$guild2_ind = 5;
	$GuildRank_ind = 6;
	$kills_ind = 7;
	$might_ind = 8;
	$VIP_ind = 9;

	$LastInd = $VIP_ind;

	$k = 67;

	//$query1 = "delete from players_network";
	//$result1 = mysql_query($query1,$dbi) or die("Error : 20170220023 <br>".$query1." <br> ".mysql_error($dbi));

	$FiveMinutes = 5 * 60;
	while (($line = fgets($f)) !== false) 
		if(strlen($line)>5)
		{
			$line = str_replace("\n","",$line);
			$parts = explode(" \t ",$line);
			
			//to avoid warnings about vector being too small
			for($i=count($parts);$i<=$LastInd;$i++)
				$parts[$i]=0;	
			
			//quick updates
			$x = $parts[$x_ind];
			$y = $parts[$y_ind];
			$name = $parts[$name_ind];
			$guild = $parts[$guild_ind];
			$CLevel = $parts[$Castle_ind];
			// castle click updates
			$guildF = $parts[$guild2_ind];
			$kills = $parts[$kills_ind];
			$vip = $parts[$VIP_ind];
			$GuildRank = $parts[$GuildRank_ind];
			$might = $parts[$might_ind];
			$HasPrisoners = 0;
			$PLevel = 0;
		
			Insert1Line($k,$x,$y,$name,$guild,$CLevel,$guildF,$kills,$vip,$GuildRank,$might,$HasPrisoners,$PLevel);
		}
	/**/
	$query1 = "delete from players where name like 'Dark.nest'";
	$result1 = mysql_query($query1,$dbi) or die("Error : 20170220023 <br>".$query1." <br> ".mysql_error($dbi));
	$query1 = "delete from players_archive where name like 'Dark.nest'";
	$result1 = mysql_query($query1,$dbi) or die("Error : 20170220023 <br>".$query1." <br> ".mysql_error($dbi));
	$query1 = "delete from player_renames where name1 like 'Dark.nest' or name2 like 'Dark.nest'";
	$result1 = mysql_query($query1,$dbi) or die("Error : 20170220023 <br>".$query1." <br> ".mysql_error($dbi));
}

function PostImportActions()
{
	global $dbi,$LastUpdated;

	//delete all from players that is older than 3 days old without update
	if($LastUpdated>0)
	{
		$olderthan = $LastUpdated - 3 * 24 * 60 * 60;
		//move to archive 
		$query1 = "insert into players_archive ( select * from players where LastUpdated < $olderthan)";
		$result1 = mysql_query($query1,$dbi) or die("Error : 20170220022 <br>".$query1." <br> ".mysql_error($dbi));
		//delete from actual
		$query1 = "delete from players where LastUpdated < $olderthan";
		$result1 = mysql_query($query1,$dbi) or die("Error : 20170220023 <br>".$query1." <br> ".mysql_error($dbi));
	}
}

function Insert1Line($k,$x,$y,$name,$guild,$CLevel,$guildF,$kills,$vip,$GuildRank,$might,$HasPrisoners,$PLevel)
{
	global $dbi,$LastUpdated;
	
	if( strpos("#".$name,"Dark.nest") == 1 )
		return;
	
	if(strlen($guild)>0)
	{
		$NewName = "[$guild]$name";
		$NewGuild = "[$guild]$guildF";
	}
	else
	{
		$NewName = "$name";
		$NewGuild = "None";
	}
	
	// load old data for this player
	$query1 = "select rowid,LastUpdated,PLevel,kills,might,vip,guildrank,guild from players where name like '%".mysql_real_escape_string($name)."' limit 0,1";
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
	list( $rowid,$LastUpdated2,$PLevel2,$kills2,$might2,$vip2,$guildrank2,$guild2 ) = mysql_fetch_row( $result1 );
//echo $query1;
	// player got renamed ? Try to search it based on coords
	if( $LastUpdated2 == 0 || $LastUpdated2 == "" )
	{
		$query1 = "select rowid,name,LastUpdated,PLevel,kills,might,vip,guildrank,castlelevel,guild from players where k ='$k' and x='$x' and y='$y' and kills >= '$kills' and vip >= '$vip' and castlelevel>=$CLevel limit 0,1";
		$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
		list( $rowid,$oldname,$LastUpdated2,$PLevel2,$kills2,$might2,$vip2,$guildrank2,$castlelevel2,$guild2 ) = mysql_fetch_row( $result1 );			
		//save the namechange
		if($rowid>0)
		{
			$query1 = "insert into player_renames values('".mysql_real_escape_string($oldname)."','".mysql_real_escape_string($NewName)."',UNIX_TIMESTAMP())";
			$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
		}
	}
	
	//restore non updated from old values
	if($PLevel == 0)
		$PLevel = $PLevel2;
	if($kills == 0)
		$kills = $kills2;
	if($might == 0)
		$might = $might2;
	if($vip == 0)
		$vip = $vip2;
	if($GuildRank == 0)
		$GuildRank = $guildrank2;
	if(strlen($guildF) <= 1 && strlen($guild)>=1)
	{
//echo "Searching for full guild name for $guild<br>";
		if(strlen($guild2)>5)
		{
//echo "Old guild is good enough $guild2 instead $guild<br>";
			$NewGuild = $guild2;
		}
		else
		{
			//try to find the long version for guild name
			$t = mysql_real_escape_string($NewGuild);
			$query1 = "select count(distinct(guild)),guild from players where guild like '$t%' and not guild like '$t'";
			$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
			list( $guildnamevariantscount, $PossibleLongName ) = mysql_fetch_row( $result1 );	
			if($guildnamevariantscount==1)	
				$NewGuild = $PossibleLongName;
//					else echo "Found multiple $guildnamevariantscount variants for $guild. Query : $query1<br>";
		}
	}
	//if we have old records, archive and delete them
	if( $rowid > 0 )
	{
		// keep data that does not get updated
		
		//archive if it comes from an older scan session
//		if($LastUpdated2 < $LastUpdated - 5 * 60 )
		{
			// do we have this row in archive already ?
			$query1 = "select rowid from players_archive where x=$x and y=$y and kills=$kills and might=$might and vip=$vip and guildrank=$GuildRank and PLevel=$PLevel and castlelevel=$CLevel and name like '".mysql_real_escape_string($NewName)."' limit 0,1";
			$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
			list( $rowid2 ) = mysql_fetch_row( $result1 );
			if( $rowid2 == "" || $rowid2 == 0 )
			{
				$query1 = "insert ignore into players_archive (select * from players where rowid=$rowid)";
				$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
			}
		}
		
		//ditch old
		$query1 = "delete from players where rowid=$rowid";
		$result1 = mysql_query($query1,$dbi) or die("Error : 2017022001 <br> ".$query1." <br> ".mysql_error($dbi));
	}
	
	$LastUpdated += 1;
	
//if(strlen($NewGuild)<=5 && $NewGuild!="None")die("not good enough $NewGuild");
	//insert new
	$query1 = "insert into players ( k,x,y,name,guild,kills,might,lastupdated,VIP,GuildRank,PLevel,CastleLevel)values(";
	$query1 .= "'".mysql_real_escape_string($k)."'";
	$query1 .= ",'".mysql_real_escape_string($x)."'";
	$query1 .= ",'".mysql_real_escape_string($y)."'";
	$query1 .= ",'".mysql_real_escape_string($NewName)."'";
	$query1 .= ",'".mysql_real_escape_string($NewGuild)."'";
	$query1 .= ",'".mysql_real_escape_string($kills)."'";
	$query1 .= ",'".mysql_real_escape_string($might)."'";
	$query1 .= ",'".mysql_real_escape_string($LastUpdated)."'";
	$query1 .= ",'".mysql_real_escape_string($vip)."'";
	$query1 .= ",'".mysql_real_escape_string($GuildRank)."'";
	$query1 .= ",'".mysql_real_escape_string($PLevel)."'";
	$query1 .= ",'".mysql_real_escape_string($CLevel)."'";
//		$query1 .= ",'".mysql_real_escape_string($HasPrisoners)."'";
	$query1 .= ")";

//continue;			
//			echo "$query1<br>";
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	
	//delete old ones in the neighbourhood
	$xmin = $x - 10;
	$xmax = $x + 10;
	$ymin = $y - 10;
	$ymax = $y + 10;
	$olderthan = $LastUpdated - 24 * 60 * 60;
	$LastTime = $LastUpdated;
	//move to archive 
	$query1 = "insert into players_archive ( select * from players where x < $xmax and x > $xmin and y < $ymax and y < $ymin and LastUpdated < $olderthan)";
	$result1 = mysql_query($query1,$dbi) or die("Error : 20170220022 <br>".$query1." <br> ".mysql_error($dbi));
	//delete from actual
	$query1 = "delete from players where x < $xmax and x > $xmin and y < $ymax and y < $ymin and LastUpdated < $olderthan";
	$result1 = mysql_query($query1,$dbi) or die("Error : 20170220023 <br>".$query1." <br> ".mysql_error($dbi));	
}
?>