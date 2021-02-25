<?php
if(!isset($_REQUEST['IHaveTheRights']))
	exit();
if(!isset($dbi))
	include("db_connection.php");
$query1 = "select count(*) from access_logs";
//echo "$query1<br>";
$result1 = mysqli_query($dbi,$query1) or die("2017022001".$query1);
list( $rowcount ) = mysqli_fetch_row( $result1 );
echo "Rowcount : $rowcount<br>";
?>
<table border=1>
	<tr>
		<td>URIs</td>
	</tr>
	<?php
		$query1 = "select distinct(URI) from access_logs";
		$result1 = mysqli_query($dbi,$query1) or die("2017022001".$query1);
		while( list( $URI ) = mysqli_fetch_row( $result1 ) )
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
		$LastRowCount = 200;
		$LastRows = ($rowcount-$LastRowCount);
		if($LastRows<0)
			$LastRows=0;
		$query1 = "select URI,IP,Stamp from access_logs limit $LastRows,$LastRowCount";
		$result1 = mysqli_query($dbi,$query1) or die("2017022001".$query1);
		$SkipShowIPs = array();
		$SkipShowIPs_str = "1";
		$row=array();
		while( list( $URI,$IPs,$Stamp ) = mysqli_fetch_row( $result1 ) )
			if(strpos($URI,"AccessStats.php"))
			{
				$SkipShowIPs[$IPs] = 1;
				$SkipShowIPs_str .= ",'$IPs'";
			}
			else
				$row[count($row)]=array($URI,$IPs,$Stamp);

		$query1 = "select URI,IP,Stamp from access_logs where IP not in ($SkipShowIPs_str) limit $LastRows,$LastRowCount";
		$result1 = mysqli_query($dbi,$query1) or die("2017022001".$query1);

		$DistinctIPs=array();
//		foreach($row as $key => $val)
		while( list( $URI,$IPs,$Stamp ) = mysqli_fetch_row( $result1 ) )
		{
//			list( $URI,$IPs,$Stamp ) = $val;
			if($SkipShowIPs[$IPs])
				continue;
			$DistinctIPs[$IPs] = 1;
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
<?php
echo "Distinct IP count : ".count($DistinctIPs)."<br>";