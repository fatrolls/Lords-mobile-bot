<?php
if(!isset($dbi))
	include("db_connection.php");

$SimpleView = 1;
	
$Filter = "";
if(isset($FN))
	$Filter .= " and name = '".mysql_real_escape_string($FN)."'";
if(isset($FNL))
{
	$t = str_replace("\\s","\\\\s",$FNL);
	$t = str_replace("\\S","\\\\S",$t);
	$t = str_replace("\\F","\\\\F",$t);
	$Filter .= " and name in $t";
}
if(isset($FNS))
	$Filter .= " and name like '%".mysql_escape_str_like($FNS)."%'";
if(isset($FG))
	$Filter .= " and guild = '".mysql_real_escape_string($FG)."' ";
if(isset($FGS))
	$Filter .= " and guild like '%".mysql_escape_str_like($FGS)."%' ";

if(isset($FGR))
{
	if($FGR==6)
		$Filter .= " and guildrank=0 and (isnull(guild) or guild='')";
	else
		$Filter .= " and guildrank='".mysql_real_escape_string($FGR)."' ";
}
if(isset($FI))
		$Filter .= " and ( statusflags &0x01000000 ) <> 0";
if(isset($FT))
	$Filter .= " and title='".mysql_real_escape_string($FT)."'";
	
if($Filter!="")	
	$SimpleView = 0;
else
	echo "To minimize page size only player coordinates are shown. If you wish to get more info, click on player name<br>";
if(!isset($PlayersPhpIncluded))
	echo "Hidden players are not shown!<br>";
?>
<link href="css/table.css" rel="stylesheet">
<table>
  <thead style="background-color: #60a917">
	<tr>
		<td>x</td>
		<td>y</td>
		<td>Name</td>
		<td>Guild</td>
		<?php if( $SimpleView == 0 )
		{
			?>
		<td>Guild Full</td>
		<td>Might</td>
		<td>Kills</td>
		<td>Guild rank</td>
		<td>VIP Level</td>
		<td>Castle Level</td>
		<td>Status changers</td>
		<td>Title</td>
		<td>Mined nodes</td>
			<?php
		}
		?>
		<td>Last Updated</td>
<!--		<td>Last Burned at</td>
		<td>Player Level</td>
		<td>Last seen with prisoners</td>
		<td>Innactive</td>
		<td>Last Burned at might</td>
		<td>Aprox troops available</td>
		<td>Nodes gathering from</td>
		<td>Castle lvl</td>
		<td>Bounty</td>
		<td>Distance to hive</td>
		<td>Active at X hours</td>
		<td>Active Y hours a day</td>
		<td>First seen ever(age)</td> -->
	</tr>
  </thead>
  <tbody class="TFtable">
	<?php
	// do not show hidden players
	$HiddenNames = "";
	$query1 = "select name from players_hidden where EndStamp > ".time();
//echo "$query1<br>";
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $name ) = mysql_fetch_row( $result1 ) )
		$HiddenNames .= "####$name####";

	$HiddenGuilds = "";
	$Order = " lastupdated desc ";
	$query1 = "select name from guilds_hidden where EndStamp > ".time();
//echo "$query1<br>";
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $name ) = mysql_fetch_row( $result1 ) )
		$HiddenGuilds .= "####$name####";
		
	$query1 = "select x,y,name,guild,guildfull,might,kills,lastupdated,statusflags,title,VIP,GuildRank,castlelevel,MiningNodes from ";
	if(isset($FN) || isset($FNL))
		$query1 .= "players_archive ";
	else
		$query1 .= "players ";

	if($Filter)
		$query1 .= " where 1=1 $Filter ";
	if($Order)
		$query1 .= " order by $Order ";
	
	if( isset($FN) || isset($FNL))
	{
		$query1 = str_replace(" order by $Order ","", $query1);
		$q2 = str_replace("players_archive","players", $query1);
		$query1 = "($query1)union($q2) order by $Order";
	}
//echo "$FN-$FNL-$FNS-$FG-$FGS:$query1";
	
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $x,$y,$name,$guild,$guildfull,$might,$kills,$lastupdated,$statusflags,$title,$VIP,$GuildRank,$castlelevel,$MiningNodes ) = mysql_fetch_row( $result1 ))
	{	
		if( strpos($HiddenNames,"#".$name."#") != 0 )
			continue;
		if( strpos($HiddenGuilds,"#".$guild."#") != 0 )
			continue;
		
		$LastUpdatedHumanFormat = gmdate("Y-m-d\TH:i:s\Z", $lastupdated);
		//$innactiveHumanFormat = gmdate("Y-m-d\TH:i:s\Z", $innactive);
		$PlayerArchiveLink = "?FN=".urlencode($name);
		$GuildFilterLink = "?FG=".urlencode($guild);
		$LastUpdatedAsDiff = GetTimeDiffShortFormat($lastupdated);
		$StatusFlagsString = StatusFlagsToString( $statusflags );
		$TitleAsString = TitleIdToString( $title );
		if($guild=="")
			$guild="&nbsp;";
/*			<td><?php echo $HasPrisonersAsDiff; ?></td>
			<td><?php echo $innactive; ?></td> 
			<td><?php echo $Plevel; ?></td>
			*/
?>
<tr>
<td><?php echo $x; ?></td>
<td><?php echo $y; ?></td>
<td><a href="<?php echo $PlayerArchiveLink; ?>"><?php echo $name; ?></a></td>
<td><a href="<?php echo $GuildFilterLink; ?>"><?php echo $guild; ?></a></td>
<?php if( $SimpleView == 0 )
{
?>
<td><?php echo $guildfull; ?></td>
<td><?php echo GetValShortFormat($might); ?></td>
<td><?php echo GetValShortFormat($kills); ?></td>
<td><?php echo $GuildRank; ?></td>
<td><?php echo $VIP; ?></td>
<td><?php echo $castlelevel; ?></td>
<td><?php echo $StatusFlagsString; ?></td>
<td><?php echo $TitleAsString; ?></td>
<td><a href="resources.php?FN=<?php echo urlencode($name); ?>"><?php echo $MiningNodes; ?></a></td>
<?php
}
?>
<td><?php echo $LastUpdatedAsDiff; ?></td>
</tr>
<?php
}
?>	
  </tbody>
</table>
<?php
include("db_connection_footer.php");

function TitleIdToString( $t )
{
//echo "sf is $sf ".($sf & 0x04);
	$t = (int)$t;
	if($t == 0)
		return "";
	if($t == 1)
		return "Overlord";	
	if($t == 2)
		return "Queen";	
	if($t == 3)
		return "General";	
	if($t == 4)
		return "Premier";	
	if($t == 5)
		return "Chief";	
	if($t == 6)
		return "Warden";	
	if($t == 7)
		return "Priest";	
	if($t == 8)
		return "Quartermaster";	
	if($t == 9)
		return "Engeneer";	
	if($t == 10)
		return "Scholar";	
	if($t == 11)
		return "Coward";	
	if($t == 12)
		return "Scoundrel";	
	if($t == 13)
		return "Clown";	
	if($t == 14)
		return "Thrall";	
	if($t == 15)
		return "Traitor";	
	if($t == 16)
		return "Felon";	
	if($t == 19)
		return "Fool";	
	$ret = "";
	return $ret;
}
function StatusFlagsToString( $sf )
{
//echo "sf is $sf ".($sf & 0x04);
	$sf = (int)$sf;
	$ret = "";
	if($sf & 0x01000000)
		$ret .= "Innactive ";
	if($sf & 0x02)
		$ret .= "Burning ";
	if($sf & 0x04)
		$ret .= "Shielded ";
	if($sf & 0x08)
		$ret .= "HasPrisoners ";
	return $ret;
}
?>
