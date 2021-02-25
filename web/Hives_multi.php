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
		<td>Might</td>
		<td>kills</td>
<!--		<td>Avg PLevel</td> -->
		<td>Avg CLevel</td>
		<td>Player count</td>
	</tr>
	</thead>
  <tbody class="TFtable">	
<?php
	$HiddenGuilds = "";
	$query1 = "select name from guilds_hidden where EndStamp > ".time();
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $name ) = mysql_fetch_row( $result1 ) )
		$HiddenGuilds .= "####$name####";

	$query1 = "select x,y,guild,guildfull,might,kills,clevel,pcount from guild_hives_multi order by pcount desc,guild,guildfull";		
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	while( list( $x,$y,$guild,$guildfull,$might,$kills,$clevel,$pcount ) = mysql_fetch_row( $result1 ))
	{
		if($guild=="")
			$guildCombo="&nbsp;";
		else
			$guildCombo="[$guild]$guildfull";
		if( $HiddenGuilds != "" && strpos($HiddenGuilds,$guild) != 0 )
			continue;
		?>
		<tr>
			<td><?php echo $x;?></td>
			<td><?php echo $y;?></td>
			<td><a href="players.php?FG=<?php echo $guild; ?>"><?php echo $guildCombo;?></a></td>
			<td><?php echo GetValShortFormat($might);?></td>
			<td><?php echo GetValShortFormat($kills);?></td>
			<td><?php echo $clevel;?></td>
			<td><?php echo $pcount;?></td>
		</tr>
		<?php
	}
?>	
	</tbody>
</table>
<?php
include("db_connection_footer.php");
?>
