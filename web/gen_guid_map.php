<?php
if(!isset($dbi))
	include("db_connection.php");

ob_start();

$ShowAvgVal = 0;
if(!isset($k))
	$k = 67;
if(!isset($YStep))
	$YStep = 1;
if(!isset($XStep))
	$XStep = 1;
$ExtraFilter="";
if(!isset($TrackWhat))
	$TrackWhat = "tguid_r4";
$MaxX = 510;
$MaxY = 1030;

$query1 = "select x from players_network where x>=0 and y>=0 order by x desc limit 0,1";		
$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
list( $MaxX ) = mysql_fetch_row( $result1 );

$query1 = "select y from players_network where x>=0 and y>=0 order by y desc limit 0,1";		
$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
list( $MaxY ) = mysql_fetch_row( $result1 );

?>
<table border=1>
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
		
	//prepare data
//	$query1 = "select tguid_r4,x,y from players_network where x>=0 and y>=0";		
	$query1 = "select hguid,x,y from players_network where x>=0 and y>=0";		
//	$query1 = "select hguid,x,y from players_network where x1!=x or y1!=y";		
	$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
	while( list( $tguid_r4,$x,$y ) = mysql_fetch_row( $result1 ))
		$guid[$x][$y] = $tguid_r4;

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
			?>
			<td><?php if(isset($guid[$x][$y]))echo $guid[$x][$y]; ?></td>
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