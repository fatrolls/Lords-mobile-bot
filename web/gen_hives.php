<?php
$DisableCaching=1;
if(!isset($dbi))
	include("db_connection.php");

// ditch old data 
$query1 = "delete from guild_hives";		
$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));

	$query1 = "select distinct(guild) from players";		
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	$itr=0;
	while( list( $guild ) = mysql_fetch_row( $result1 ))
		$GuildList[$itr++] = $guild;
	
	//get the hive for each guild
	foreach( $GuildList as $key => $guild)
	{
		$escapped_guild = mysql_real_escape_string($guild);
		//get all players for this guild
		if($guild=="")
			$query1 = "select x,y,might,CastleLevel,guildfull from players where isnull(guild) or guild = ''";		
		else
			$query1 = "select x,y,might,CastleLevel,guildfull from players where guild = '".$escapped_guild."'";		
//echo $query1;	
		$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
		unset($Guildx);
		unset($Guildy);
		unset($Guildmight);
		unset($guildfull);
		// initial central location
		$xavg = 0;
		$yavg = 0;
		$playercount = 0;
		$TotalMight = 0;
		while( list( $x,$y,$might,$CastleLevel,$tguildfull ) = mysql_fetch_row( $result1 ))
		{
			if( $tguildfull != "" && (!isset($guildfull) || $guildfull=="") )
				$guildfull = $tguildfull;
			$Guildx[$playercount] = $x;
			$Guildy[$playercount] = $y;
			$Guildmight[$playercount] = $might;
			$GuildCastleLevel[$playercount] = $CastleLevel;
			$TotalMight += $might;
			$xavg += $x;
			$yavg += $y;
			$playercount++;
		}
		if($playercount==0)		
			echo "Mistical bug found, check guild '$guild' = ".mysql_real_escape_string($guild)." = $escapped_guild and query : $query1<br>";
		$cordcount = $playercount;
		$xavg_prev = $xavg / $cordcount;
		$yavg_prev = $yavg / $cordcount;
		$MaxDistAllowed = 1000 * 1000 + 500 * 500;
		// what is the coordinate to minimize sum of distances ?
		// get avg central location
		// get the avg distance to this location
		// get a new avg location counting only points that are below the avg distance
		//refine central location
		for($PrecisionIncrease=0;$PrecisionIncrease<7;$PrecisionIncrease++)
		{
			$xavg = 0;
			$yavg = 0;
			$cordcount = 0;
			$DistSum = 0;
			$mightsum = 0;
			$CLevelSum = 0;
			$CLevelCount = 0;
			for($i=0;$i<count($Guildx);$i++)
			{
				$x = $Guildx[ $i ];
				$y = $Guildy[ $i ];
				$distx = ($x-$xavg_prev);
				$disty = ($y-$yavg_prev);
				$dist = $distx * $distx + $disty * $disty;
//				if( $dist * $dist < $MaxDistAllowed * $MaxDistAllowed) // make long paths a punishment
				if( $dist< $MaxDistAllowed ) // make long paths a punishment
				{
					$xavg += $x;
					$yavg += $y;
					$DistSum += $dist;
					$cordcount++;
					$mightsum += $Guildmight[ $i ];
					if( $GuildCastleLevel[$i] > 0 )
					{
						$CLevelSum += $GuildCastleLevel[$i];
						$CLevelCount++;
					}
				}
			}
			if($cordcount>0)
			{
				$xavg_prev = ($xavg / $cordcount);
				$yavg_prev = ($yavg / $cordcount);
				$MaxDistAllowed = $DistSum / $cordcount;
//echo "Refine $xavg_prev $yavg_prev $MaxDistAllowed<br>";
			}
			else
				break;
			// if the space is almost filled with castles than it's good enough central location
			if( $MaxDistAllowed <= $playercount)
				break;
			$DistSumPrev = (int)($DistSum / $cordcount);
		}
		if( $CLevelCount > 0 )
			$CLevelAvg = (int)($CLevelSum / $CLevelCount);
		else
			$CLevelAvg = 0;
		$MaxDist = (int)sqrt( $DistSumPrev );
		$xavg_prev = (int)($xavg_prev);
		$yavg_prev = (int)($yavg_prev);
		if(!isset($guildfull))
			$guildfull="";
//echo "Guild '$guild' central location is at $xavg_prev $yavg_prev with radius $MaxDist and castles $cordcount. Total castle count $playercount<br>";
//exit();
		$query1 = "insert into guild_hives (x,y,guild,guildfull,radius,HiveCastles,TotalCastles,HiveMight,TotalMight,AvgCastleLevel)values($xavg_prev,$yavg_prev,'".mysql_real_escape_string($guild)."','".mysql_real_escape_string($guildfull)."',$MaxDist,$cordcount,$playercount,$mightsum,$TotalMight,$CLevelAvg)";		
		$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	}	
//	die();
?>