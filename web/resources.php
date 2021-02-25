<?php
if(!isset($dbi))
	include("db_connection.php");
?>
<link href="css/table.css" rel="stylesheet">
<?php
if(!isset($s_type))
	$s_type="";
if(!isset($s_occupied))
	$s_occupied="";
if(!isset($s_level))
	$s_level="";
?>
<form name="SearchForm" id="SearchForm" action="">
	Mineral Type <select name="s_type"> 
	<option value="" <?php if($s_type=="") echo "selected"; ?>>All</option>
	<option value="1" <?php if($s_type==1) echo "selected"; ?>>Food</option>
	<option value="2" <?php if($s_type==2) echo "selected"; ?>>Rock</option>
	<option value="3" <?php if($s_type==3) echo "selected"; ?>>Ore</option>
	<option value="4" <?php if($s_type==4) echo "selected"; ?>>Wood</option>
	<option value="5" <?php if($s_type==5) echo "selected"; ?>>Gold</option>
	<option value="6" <?php if($s_type==6) echo "selected"; ?>>Gem loads</option>
	</select>

	Occupied <select name="s_occupied"> 
	<option value="0" <?php if($s_occupied==0) echo "selected"; ?>>whatever</option>
	<option value="1" <?php if($s_occupied==1) echo "selected"; ?>>yes</option>
	<option value="2" <?php if($s_occupied==2) echo "selected"; ?>>no</option>
	</select>

	Level minimum<select name="s_level"> 
	<option value="" <?php if($s_level=="") echo "selected"; ?>>All</option>
	<option value="1" <?php if($s_level==1) echo "selected"; ?>>1</option>
	<option value="2" <?php if($s_level==2) echo "selected"; ?>>2</option>
	<option value="3" <?php if($s_level==3) echo "selected"; ?>>3</option>
	<option value="4" <?php if($s_level==4) echo "selected"; ?>>4</option>
	<option value="5" <?php if($s_level==5) echo "selected"; ?>>5</option>
	</select>

	<input type="submit" value="Search">
</form>

<table>
  <thead style="background-color: #60a917">
	<tr>
		<td>x</td>
		<td>y</td>
		<td>level</td>
		<td>player</td>
		<td>Last Updated</td>
	</tr>
  </thead>
  <tbody class="TFtable">
	<?php
	$query1 = "select x,y,level,playername,lastupdated from resource_nodes where 1=1";
	if(isset($s_type) && $s_type != "" )
		$query1 .= " and rtype='".mysql_real_escape_string($s_type)."'";
	if(isset($s_level) && $s_level != "" )
		$query1 .= " and level>='".mysql_real_escape_string($s_level)."'";
	if(isset($s_occupied) && $s_occupied != "" )
	{
		if($s_occupied==1)
			$query1 .= " and not (playername = '' or isnull(playername)) ";
		else if($s_occupied==2)
			$query1 .= " and (playername = '' or isnull(playername)) ";
	}
	if(isset($FN))
		$query1 .= " and playername = '".mysql_real_escape_string($FN)."'";
	$query1 .= " order by lastupdated desc";
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $x,$y,$level,$playername,$lastupdated ) = mysql_fetch_row( $result1 ))
	{	
		$PlayerArchiveLink = "?FN=".urlencode($playername);
		$LastUpdatedAsDiff = GetTimeDiffShortFormat($lastupdated);
?>
<tr>
<td><?php echo $x; ?></td>
<td><?php echo $y; ?></td>
<td><?php echo $level; ?></td>
<td><a href="<?php echo $PlayerArchiveLink; ?>"><?php echo $playername; ?></a></td>
<td><?php echo $LastUpdatedAsDiff; ?></td>
</tr>
<?php
}
?>	
  </tbody>
</table>
<?php
include("db_connection_footer.php");
?>
