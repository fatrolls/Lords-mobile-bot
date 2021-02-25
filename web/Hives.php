<?php
if(!isset($dbi))
	include("db_connection.php");
?>
<link href="css/table.css" rel="stylesheet">
<table>
	<thead style="background-color: #60a917">
		<tr>
			<td>x</td>
			<td>y</td>
			<td>Guild name</td>
			<td>Hive radius</td>
			<td>Player count at hive</td>
			<td>Might at hive</td>
			<td>Player count total</td>
			<td>Might total</td>
	<!--		<td>Max PLevel</td>
			<td>Avg PLevel</td> -->
			<td>Avg CLevel</td>
		</tr>
	</thead>
  <tbody class="TFtable">	
<?php
	$HiddenGuilds = "";
	$query1 = "select name from guilds_hidden where EndStamp > ".time();
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $name ) = mysql_fetch_row( $result1 ) )
		$HiddenGuilds .= "####$name####";

	$query1 = "select x,y,guild,guildfull,radius,HiveCastles,TotalCastles,HiveMight,TotalMight,AvgCastleLevel from guild_hives order by HiveCastles desc";		
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	while( list( $x,$y,$guild,$guildfull,$radius,$HiveCastles,$TotalCastles,$HiveMight,$TotalMight,$AvgCLevel ) = mysql_fetch_row( $result1 ))
	{
		if($guild=="")
			$guildCombo="&nbsp;";
		else
			$guildCombo="[$guild]$guildfull";
		if( $guild != "" && strpos($HiddenGuilds,$guild) != 0 )
			continue;

		?>
		<tr>
			<td><?php echo $x;?></td>
			<td><?php echo $y;?></td>
			<td><a href="players.php?FG=<?php echo $guild; ?>"><?php echo $guildCombo;?></a></td>
			<td><?php echo $radius;?></td>
			<td><?php echo $HiveCastles;?></td>
			<td><?php echo GetValShortFormat($HiveMight);?></td>
			<td><?php echo $TotalCastles;?></td>
			<td><?php echo GetValShortFormat($TotalMight);?></td>
			<td><?php echo $AvgCLevel;?></td>
		</tr>
		<?php
	}
?>	
	</tbody>
</table>
<br>Show list of locations where the number of players from the same guild is high<br>
<?php
include("db_connection_footer.php");
?>