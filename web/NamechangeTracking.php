<?php
if(!isset($dbi))
	include("db_connection.php");
if(!isset($F))
{
?>
<br>Find a player based on name. Or maybe an older name. Data is only estimated!, based on position change, castle level, VIP, kills<br>
<table border=1>
	<tr>
		<td>Player Name</td>
		<td>Old names</td>
		<td>Time since</td>
	</tr>
<?php
	$query1 = "select Name1,Name2,NewNameSeenAt from player_renames order by NewNameSeenAt desc";
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $Name1,$Name2,$Stamp ) = mysql_fetch_row( $result1 ))
	{
		$Player1ArchiveLink = "NamechangeTracking.php?F=".urlencode($Name1);
		$Player2ArchiveLink = "NamechangeTracking.php?F=".urlencode($Name2);
		$when = GetTimeDiffShortFormat($Stamp);
		?>
	<tr>
		<td><a href="<?php echo $Player1ArchiveLink; ?>"><?php echo $Name1; ?></a></td>
		<td><a href="<?php echo $Player2ArchiveLink; ?>"><?php echo $Name2; ?></a></td>
		<td><?php echo $when; ?></td>
	</tr>
	<?php
	}
?>	
</table>
<?php
}
else
{
	//track a starting name for consecutivenamechanges
	//gather all the names seen in a chain 
	$FNL = "(";
	//first go backwards. Probably 0 steps ...
	$Name2 = $F;
	$Stamp = time();
	do{
//		echo "Player $Name1 records<br>";
		$FNL .= "'".mysql_real_escape_string($Name2)."',";
		//check for next name of this player
		$query1 = "select Name1,NewNameSeenAt from player_renames where name2 = '".mysql_real_escape_string($Name2)."' and NewNameSeenAt<$Stamp order by NewNameSeenAt desc limit 0,1";
		$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
		list( $Name1,$Stamp ) = mysql_fetch_row( $result1 );
		$Name2 = $Name1;
	}while($Name2 != "");
	//now go forward
	$Name1 = $F;
	$Stamp = time();
	do{
//		echo "Player $Name1 records<br>";
		$FNL .= "'".mysql_real_escape_string($Name1)."',";
		//check for next name of this player
		$query1 = "select Name2,NewNameSeenAt from player_renames where name1 = '".mysql_real_escape_string($Name1)."' and NewNameSeenAt<$Stamp order by NewNameSeenAt desc limit 0,1";
		$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
		list( $Name2,$Stamp ) = mysql_fetch_row( $result1 );
		$Name1 = $Name2;
	}while($Name1 != "");
	$FNL = substr($FNL,0,-1).")";
	$PlayersPhpIncluded = 1;
	include("players.php");
}
include("db_connection_footer.php");
?>
