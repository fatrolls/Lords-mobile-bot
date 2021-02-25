<?php
if(!isset($_REQUEST['IHaveTheRights']))
	exit();
if(!isset($dbi))
	include("db_connection.php");
$query1 = "select count(*) from access_logs";
//echo "$query1<br>";
$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
list( $rowcount ) = mysql_fetch_row( $result1 );
echo "Rowcount : $rowcount<br>";
?>
<table border=1>
	<tr>
		<td>URIs</td>
	</tr>
	<?php
		$query1 = "select distinct(URI) from access_logs";
		$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
		while( list( $URI ) = mysql_fetch_row( $result1 ) )
		{
			?>
	<tr>
		<td><?php echo $URI;?></td>
	</tr>
			<?php
		}
	?>
</table>
<br>
Last X rows<br>
<table border=1>
	<tr>
		<td>URIs</td>
		<td>IPs</td>
		<td>Time</td>
		<td>Time diff</td>
	</tr>
	<?php
		$LastRowCount = 50;
		$LastRows = ($rowcount-$LastRowCount);
		$query1 = "select URI,IP,Stamp from access_logs limit $LastRows,$LastRowCount";
		$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
		while( list( $URI,$IPs,$Stamp ) = mysql_fetch_row( $result1 ) )
		{
			$diff = GetTimeDiffShortFormat($Stamp);
			$AtHumanFormat = gmdate("Y-m-d\TH:i:s\Z", $Stamp);
			?>
	<tr>
		<td><?php echo $URI;?></td>
		<td><?php echo $IPs;?></td>
		<td><?php echo $AtHumanFormat;?></td>
		<td><?php echo $diff;?></td>
	</tr>
			<?php
		}
	?>
</table>