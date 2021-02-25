<?php
set_time_limit( 2 * 60 );
$DisableCaching=1;
if(!isset($dbi))
	include("db_connection.php");

ob_start();

$ShowAvgVal = 0;
if(!isset($k))
	$k = 67;
if(!isset($YStep))
	$YStep = 40;
if(!isset($XStep))
	$XStep = 20;
$ExtraFilter="";
$FromWhere="players";
if(!isset($TrackWhat))
	$TrackWhat = "might";
if($TrackWhat == "might")
	$SelectWhat = "might";
if($TrackWhat == "kills")
	$SelectWhat = "kills";
if($TrackWhat == "pcount")
	$SelectWhat = "count(*)";
if($TrackWhat == "castlelevel")
{
	$SelectWhat = "castlelevel";
	$ShowAvgVal = 1;
}
if($TrackWhat == "guildless")
{
	$SelectWhat = "count(*)";
	$ExtraFilter = " and (guild='' or isnull(guild))";
}
if($TrackWhat == "guildless_innactive")
{
	echo "Players who's might did not change in the past X days<br>. Only works if it has recent data updated. Check manually !";
	$SelectWhat = "count(*)";
	$ExtraFilter = " and (guild='' or isnull(guild)) and ( statusflags & 0x01000000) <> 0";
}
if($TrackWhat == "resourcelevel")
{
	$FromWhere="resource_nodes";
	$SelectWhat = "level";
	$ExtraFilter = "";
	$ShowAvgVal = 1;
}
if($TrackWhat == "resourcefree")
{
	$FromWhere="resource_nodes";
	$SelectWhat = "1";
	$ExtraFilter = " and (isnull(playername) or playername='')";
}
if($TrackWhat == "rss3") // number of free lvl 3 rss and minimal player count
{
	$FromWhere="resource_nodes";
	$SelectWhat = "count(*)";
	$ExtraFilter = " and level>=3 limit 0,1";
	$FromWhere2="players";
	$SelectWhat2 = "count(*)";
	$ExtraFilter2 = " limit 0,1";
	$YStep = 10;	$XStep = 10;
}
if($TrackWhat == "rss3might") // number of free lvl 3 rss and minimal player count
{
	$FromWhere="resource_nodes";
	$SelectWhat = "count(*)";
	$ExtraFilter = " and level>=3 limit 0,1";
	$FromWhere2="players";
	$SelectWhat2 = "count(*)*sum(might)";
	$ExtraFilter2 = " limit 0,1";
	$YStep = 10;	$XStep = 10;
}
$MaxX = 510;
$MaxY = 1030;
?>
Black is strongest value. Light red is weakest value. When showing avg values you should also check color !<br>
<table>
	<tr>
		<td></td>
<?php
	for( $x=0;$x<$MaxX;$x += $XStep)
	{
		$from = ($x);
		if($from<0)
			$from=0;
		?>
		<td><?php echo $from."-".($x+$XStep); ?></td>
		<?php
	}
	?>
	</tr>
	<?php
		
	$MaxMight = 0;
	//prepare data
	for( $y=0;$y<$MaxY;$y+=$YStep)
		for( $x=0;$x<$MaxX;$x += $XStep)
		{
			//fetch players in this cell
			$MightSum[$x][$y] = 0;
			$MightCount[$x][$y] = 0;
			$query1 = "select $SelectWhat from $FromWhere where x>=".($x)." and x<=".($x+$XStep)." and y>=".($y)." and y<=".($y+$YStep)."$ExtraFilter";		
			$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
			while( list( $might ) = mysql_fetch_row( $result1 ))
			{
				$score = $might;
				$val = $might;
				$MightSum[$x][$y] += $val;
				$MightCount[$x][$y]++;
				if(isset($SelectWhat2))
				{
					$query2 = "select $SelectWhat2 from $FromWhere2 where x>=".($x)." and x<=".($x+$XStep)." and y>=".($y)." and y<=".($y+$YStep)."$ExtraFilter2";		
					$result2 = mysql_query($query2,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
					list( $might2 ) = mysql_fetch_row( $result2 );
					if($TrackWhat == "rss3")
					{
						$Label[$x][$y] = "${might}(rss) - ${might2}(p)";
						$score = $might / (1+$might2);
						$MightCount[$x][$y] = $score;
						if( $score > $MaxMight)
							$MaxMight = $score;
					}
					if($TrackWhat == "rss3might")
					{
						$Label[$x][$y] = "${might}(rss) - ".GetValShortFormat($might2);
						$score = $might / (1+$might2);
						$MightCount[$x][$y] = $score;
						if( $score > $MaxMight)
							$MaxMight = $score;
					}
				}
			}
			if( $MightSum[$x][$y] > $MaxMight)
				$MaxMight = $MightSum[$x][$y];
		}

	for( $y=0;$y<$MaxY;$y+=$YStep)
	{
		$from = ($y);
		if($from<0)
			$from=0;
		?>
		<tr>
			<td><?php echo $from."-".($y+$YStep); ?></td>
		<?php
		for( $x=0;$x<$MaxX;$x += $XStep)
		{
			//fetch players in this cell
			if($MaxMight>0)
				$ColorPCT = 255 - (int)( 255 * $MightSum[$x][$y] / $MaxMight );
			else
				$ColorPCT = 255;
			$val = $MightSum[$x][$y];
			if($ShowAvgVal == 1 && $MightCount[$x][$y] > 0 )
				$val = (int)( $val / $MightCount[$x][$y] * 10 ) / 10;
			else if( isset($Label[$x][$y]) )
				$val = $Label[$x][$y];
			?>
			<td style="background-color:rgb(<?php echo $ColorPCT; ?>,0,0)"><?php echo GetValShortFormat($val); ?></td>
			<?php
		}
		?>
		</tr>
		<?php
	}
?>
</table>
<?php
$StaticFileContent = ob_get_contents();
ob_end_clean();
//dump page content to file
$f = fopen("${TrackWhat}_$k.html","wt");
if($f)
{
	fwrite($f,$StaticFileContent);
	fclose($f);
	$f = 0;
}
else
	echo "$StaticFileContent";
?>