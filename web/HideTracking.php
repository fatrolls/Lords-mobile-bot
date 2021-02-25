<?php
$DisableCaching=1;
if(!isset($dbi))
	include("db_connection.php");
if(isset($Type))
{
	if($Type=="Player")
	{
		$table="players_hidden";
		$AntiScoutDuration = 6*60;
	}
	else if($Type=="Guild")
	{
		$table="guilds_hidden";
		$AntiScoutDuration = 1*60;
	}
}
if(!isset($table))
{
	$table="players_hidden";
	$AntiScoutDuration = 6*60;
}
if(!isset($Name))
{
?>
Put in the name to stop showing it to other players. Copy paste exactly as you can see it in the list.<br>
	<form name="HidePlayer" id="HidePlayer" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		Name : <input type="text" name="Name" value=""><br/>
		<input type="hidden" name="Type" value="<?php echo $Type; ?>"><br/>
		<input type="submit" value="Apply anti track"><br/>
	</form>
<?php
}
else
{
	$query1 = "select RequestCount,IPs from $table where name = '".mysql_real_escape_string($Name)."'";		
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	list( $RequestCount,$IPs ) = mysql_fetch_row( $result1 );
	if(strpos($IPs,$ip)==0)
		$IPs.= ",$ip";
	if($RequestCount>0)
	{
		$query1 = "update $table set RequestCount=$RequestCount+1, EndStamp=".(time()+$AntiScoutDuration*60).",ips='$IPs' where name = '".mysql_real_escape_string($Name)."'";				
	}
	else
	{
		$query1 = "insert into $table (Name,RequestCount,EndStamp,ips) values ('".mysql_real_escape_string($Name)."',1,".(time()+$AntiScoutDuration*60).",'$IPs')";						
	}
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	echo "$Type $Name will not be displayed for $AntiScoutDuration minutes";
}
?>
<table>
	<tr>
		<td>Name</td>
		<td>Expires</td>
		<td>Remaining H</td>
	</tr>
	<?php
	echo "${Type}s hidden from tracking :<br>";
	$query1 = "select name,EndStamp from $table where EndStamp>".time()."";
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	while(list( $name,$EndStamp ) = mysql_fetch_row( $result1 ))
	{
		$EndStampHumanFormat = gmdate("Y-m-d\TH:i:s\Z", $EndStamp);
		$HoursRemaining = ($EndStamp-time())/60/60;
		$HoursRemaining = (int)($HoursRemaining*100)/100;
	?>
		<tr>
			<td><?php echo $name;?></td>
			<td><?php echo $EndStampHumanFormat;?></td>
			<td><?php echo $HoursRemaining;?></td>
		</tr>
	<?php
	}
	?>
</table>
