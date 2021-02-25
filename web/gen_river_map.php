<?php
ini_set('memory_limit','640M');
$DisableCache=1;
$k=99;
if(!isset($dbi))
	include("db_connection.php");

$ShowAvgVal = 0;
$MaxX = 510;
$MaxY = 1030;
/*
$query1 = "select x from used_locations order by x desc limit 0,1";		
$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
list( $MaxX ) = mysql_fetch_row( $result1 );

$query1 = "select y from used_locations order by y desc limit 0,1";		
$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
list( $MaxY ) = mysql_fetch_row( $result1 );
*/
//prepare data
$query1 = "select `key`,`mask` from used_locations";		
$result1 = mysql_query($query1,$dbi) or die("Error : 2017022004 <br>".$query1." <br> ".mysql_error($dbi));
while( list( $key,$mask ) = mysql_fetch_row( $result1 ))
{
	$x = 31 * ( $key % 10000 );
	$y = (int)($key / 10000);
	for( $i=0;$i<32;$i++)
		if($mask & (1<<$i))
		{
			$loc[$x+$i][$y] = 1;  // seen something on this location
/*			if(!isset($RowLocations[$y]))
				$RowLocations[$y]=0;
			else
				$RowLocations[$y]++;
			if(!isset($ColLocations[$x+$i]))
				$ColLocations[$x+$i]=0;
			else
				$ColLocations[$x+$i]++; */
		}
}

OutputAsImage("test.png");
//let's erode rivers. Will spill used locations 1 pixel
for( $y=2;$y<$MaxY-1;$y++)
	for( $x=(2+$y%2);$x<$MaxX;$x+=2)
//		if(!( isset($loc[$x][$y]) && (!isset($loc[$x+1][$y-1]) || !isset($loc[$x+1][$y+1]))))
//		if(!( isset($loc[$x][$y]) && (isset($loc[$x+1][$y-1]) || isset($loc[$x+1][$y+1]) || isset($loc[$x-2][$y]) || isset($loc[$x+2][$y]))))
		if(isset($loc[$x][$y]) || isset($loc[$x+1][$y-1]) || isset($loc[$x+1][$y+1]) || isset($loc[$x-2][$y]) || isset($loc[$x+2][$y]))
//			unset($loc[$x][$y]);
			$loc2[$x][$y] = 1;
$loc = $loc2;			
OutputAsImage("test2.png");		

function OutputAsImage($name="")
{
	global $MaxY,$MaxX,$loc;
	$gd = imagecreatetruecolor($MaxX, $MaxY);
	$blue = imagecolorallocate($gd, 0, 0, 255); 
	for( $y=0;$y<$MaxY;$y++)
		for( $x=($y%2);$x<$MaxX;$x+=2)
			if(!isset($loc[$x][$y]))
			{
				imagesetpixel($gd, $x, $y, $blue);
				imagesetpixel($gd, $x+1, $y, $blue);
			}
	header('Content-Type: image/png');
	imagepng($gd);
	if($name!="")
		imagepng($gd,$name);
	imagedestroy($gd);
}

function OutputAsTable()
{
	ob_start();
	?>
	<table cellspacing="0" cellpadding="0" border=0 style="padding-left: 0px;padding-bottom:0px; font-size: 3px;">
		<tr>
			<td></td>
	<?php
		for( $x=0;$x<$MaxX;$x += 2)
		{
	//		if(!isset($ColLocations[$x]) || $ColLocations[$x] <= $MaxX / 2)
			{
				?>
				<td><?php echo $x; ?></td>
				<?php
			}
		}
		?>
		</tr>
		<?php

		for( $y=0;$y<$MaxY;$y+=1)
		{
	//		if(!isset($RowLocations[$y]) || $RowLocations[$y]<=$MaxY/2)
			{
				?>
				<tr>
					<td><?php echo $y; ?></td>
				<?php
				for( $x=($y%2);$x<$MaxX;$x+=2)
				{
					if(isset($loc[$x][$y]))
					{
					?>
					<td></td>
					<?php
					}
					else
					{
					?>
					<td style="background-color:rgb(0,0,255)">.</td>
					<?php					
					}
				}
				?>
				</tr>
				<?php
			}
		}
	?>
	</table>
	<?php
	$StaticFileContent = ob_get_contents();
	ob_end_clean();
	//dump page content to file
	$f = fopen("rivers.html","wt");
	if($f)
	{
		fwrite($f,$StaticFileContent);
		fclose($f);
		$f = 0;
	}
	else
		echo "$StaticFileContent";
}
?>