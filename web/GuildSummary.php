<?php
if(!isset($dbi))
	include("db_connection.php");

//remove warnings
if(!isset($s_guild))
	$s_guild = "";
if(!isset($s_guild_t))
	$s_guild_t = "";

$query1 = "select distinct(guild) from players";		
$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
$itr=1;
$SelectList = "";
$FoundSelected = 0;
while( list( $guild ) = mysql_fetch_row( $result1 ))
{
	$SelectList .= "<option value=$itr ";
	if($s_guild == $itr)
	{
		$SelectList .= "selected";
		$FoundSelected = 1;
	}
	$SelectList .= ">$guild</option>";
	$GuildList[$itr++] = $guild;
}
if($FoundSelected==0)
	$SelectList = "<option value='-1' selected></option>".$SelectList;
else
	$SelectList = "<option value='-1'></option>".$SelectList;
?>
generate up to 31 day summary for guild : 
<form name="SearchForm" id="SearchForm" action="">
	<select name="s_guild"><?php echo $SelectList; ?></select> or type here <input type="text" name="s_guild_t" value="<?php echo $s_guild_t; ?>">
	<input type="submit" value="Generate">
</form>
<?php
//generate the raport if we selected a guild
if($s_guild!="")
{
	$SumMightOldest = 0;
	$SumKillsOldest = 0;
	$SumMightNewest = 0;
	$SumKillsNewest = 0;
	$toOutput = "";
	//select players 1 month ago, or at least oldest possible if 1 month data is not available
	if($s_guild <= 0 && $s_guild_t != "" )
		$sel_guild = $s_guild_t;
	else
		$sel_guild = $GuildList[$s_guild];
	$OneMonthAgo = time() - 60 * 60 * 24 * 31;
	$query1 = "select name,might,kills,lastupdated from players where guild = '".mysql_real_escape_string($sel_guild)."'";
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	while( list( $name,$cur_might,$cur_kills,$cur_lastupdated ) = mysql_fetch_row( $result1 ))
	{
		$past_might = 0;
		if($cur_might>0)
		{
			//select oldest version but older than a month
			$query1 = "select might,kills from players_archive where might<>0 and name = '".mysql_real_escape_string($name)."' and lastupdated < $OneMonthAgo limit 0,1";
			$result2 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
			list( $past_might, $past_kills, $past_updated ) = mysql_fetch_row( $result2 );
			// in case we did not collect enough data for 31 days
			if($past_might==0)
			{
				$query1 = "select might,kills,lastupdated from players_archive where might<>0 and name = '".mysql_real_escape_string($name)."' order by lastupdated asc limit 0,1";
				$result2 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
				list( $past_might, $past_kills, $past_updated ) = mysql_fetch_row( $result2 );			
			}
		}
		if($cur_might>0 && $past_might>0)
		{
			$TimeDiff = $cur_lastupdated - $past_updated;
			$KillsDiff = $cur_kills - $past_kills;
			$MightDiff = $cur_might - $past_might;
			
			$SumMightOldest += $past_might;
			$SumKillsOldest += $past_kills;
			$SumMightNewest += $cur_might;
			$SumKillsNewest += $cur_kills;			
		}
		else
		{
			$TimeDiff = 0;
			$KillsDiff = 0;
			$MightDiff = 0;			
		}
		$nameURL = "players.php?FN=".urlencode($name);
		$toOutput .= "
		<tr>
			<td><a href='$nameURL'>$name</a></td>
			<td>".GetValShortFormat($MightDiff)."</td>
			<td>".GetValShortFormat($KillsDiff)."</td>
			<td>".GetTimeDiffShortFormat($TimeDiff,1)."</td>
		</tr>";
	}
	echo "Total might summed from oldest used date : ".GetValShortFormat($SumMightOldest)." compared to new ".GetValShortFormat($SumMightNewest).". There is a ".((int)($SumMightNewest*100*100/($SumMightOldest+1) - 10000)/100)."% increase<br>";
	echo "Total kills summed from oldest used date : ".GetValShortFormat($SumKillsOldest)." compared to new ".GetValShortFormat($SumKillsNewest).". There is a ".((int)($SumKillsNewest*100*100/($SumKillsOldest+1) - 10000)/100)."% increase<br>";
	?>
	<link href="css/table.css" rel="stylesheet">
	<table>
		<thead style="background-color: #60a917">
			<tr>
				<td>Player</td>
				<td>Might increase</td>
				<td>Kills increase</td>
				<td>Days for data</td>
			</tr>
		</thead>
		<tbody class="TFtable">
		<?php echo $toOutput; ?>
		</tbody>
	</table>
	<?php
}
include("db_connection_footer.php");
?>