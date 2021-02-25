<?php
require_once("db_connection.php");
require_once("functions.php");

$year = GetYear();
$day = GetDayOfYear();

$OldData = array();
$NewData = array();
$query1 = "select distinct(PlayerName) from PlayerMights where day>$day-7 order by PlayerMight desc";
$result2 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
while(list($PName) = mysqli_fetch_row($result2))
{
	//what is the oldest data we have less than 1 month ?
	$query1 = "select day from PlayerMights where day>=($day-31) and year=$year and PlayerName='$PName' order by day asc limit 0,1";
	$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	list($OldDay) = mysqli_fetch_row($result1);

	$query1 = "select day from PlayerMights where day<=$day and year=$year and PlayerName='$PName' order by day desc limit 0,1";
	$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	list($NewDay) = mysqli_fetch_row($result1);

//	echo "Comparing day $OldDay with day $NewDay data<br>";
	$query1 = "select day,PlayerName,PlayerMight,PlayerKills from PlayerMights where day in($OldDay,$NewDay) and PlayerName='$PName' order by PlayerMight desc";
	$result1 = mysqli_query($dbi, $query1) or die("Error : 2017022002 <br> ".$query1." <br> ".mysqli_error($dbi));
	while(list($tday,$PlayerName,$PlayerMight,$PlayerKills) = mysqli_fetch_row($result1))
	{
		if($tday==$NewDay)
		{
			$NewData[$PlayerName]['m']=$PlayerMight;
			$NewData[$PlayerName]['k']=$PlayerKills;
			$NewData[$PlayerName]['d']=$NewDay;
		}
		else
		{
			$OldData[$PlayerName]['m']=$PlayerMight;
			$OldData[$PlayerName]['k']=$PlayerKills;
			$OldData[$PlayerName]['d']=$OldDay;
		}
		//echo "$PlayerName,$PlayerMight,$PlayerKills<br>";
	}
}
?>
<table border='1'>
	<tr>
		<td>Name</td>
		<td>Old Might</td>
		<td>Might gained</td>
		<td>Old Kills</td>
		<td>Kills gained</td>
		<td>History days old</td>
	</tr>
	<?php
	foreach($NewData as $PlayerName => $val)
	{
		@$diffm=$NewData[$PlayerName]['m']-$OldData[$PlayerName]['m'];
		@$diffk=$NewData[$PlayerName]['k']-$OldData[$PlayerName]['k'];
		@$diffd=$NewData[$PlayerName]['d']-$OldData[$PlayerName]['d'];
		?>
	<tr>
		<td><?php echo $PlayerName;?></td>
		<td><?php echo @ValueShortFormat($OldData[$PlayerName]['m']);?></td>
		<td><?php echo ValueShortFormat($diffm);?></td>
		<td><?php echo @ValueShortFormat($OldData[$PlayerName]['k']);?></td>
		<td><?php echo ValueShortFormat($diffk);?></td>
		<td><?php echo $diffd;?></td>
	</tr>
		<?php
	}
	?>
</table>