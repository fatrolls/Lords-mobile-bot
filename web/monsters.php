<?php
if(!isset($dbi))
	include("db_connection.php");
?>
<link href="css/table.css" rel="stylesheet">
<?php
if(!isset($s_type))
{
	if(isset($_REQUEST['s_type']))
		$s_type=$_REQUEST['s_type'];
	else
		$s_type="";
}
if(!isset($s_occupied))
{
	if(isset($_REQUEST['s_occupied']))
		$s_occupied=$_REQUEST['s_occupied'];
	else
		$s_occupied="";
}
if(!isset($s_level))
{
	if(isset($_REQUEST['s_level']))
		$s_level=$_REQUEST['s_level'];
	else
		$s_level="";
}
?>
<form name="SearchForm" id="SearchForm" action="">
	Monster Type <select name="s_type"> 
	<option value="" <?php if($s_type=="") echo "selected"; ?>>All</option>
	<option value="10066" <?php if($s_type==10066 || $s_type=="rare") echo "selected"; ?>>Rare</option>
	<option value="3" <?php if($s_type==3) echo "selected"; ?>>Gargantua</option>
	<option value="7" <?php if($s_type==7) echo "selected"; ?>>Mega Maggot</option>
	<option value="15" <?php if($s_type==15) echo "selected"; ?>>Tidal titan</option>
	<option value="16" <?php if($s_type==16) echo "selected"; ?>>Bon apeti</option>
	<option value="18" <?php if($s_type==18) echo "selected"; ?>>Blackwing</option>
	<option value="19" <?php if($s_type==19) echo "selected"; ?>>Mecha trojan</option>
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
		<td>type</td>
		<td>Last Updated</td>
	</tr>
  </thead>
  <tbody class="TFtable">
	<?php
	$query1 = "select x,y,level,mtype,lastupdated from monsters where lastupdated>'".(time()-4*60*60)."'";
	
	if($s_type==10066 || $s_type=="rare")
		$query1 .= " and mtype not in (3,7,15,16,18,19)";
	else if($s_type != "")
		$query1 .= " and mtype='".mysql_real_escape_string($s_type)."'";
	
	if($s_level>0 && $s_level<6)
		$query1 .= " and level>='".mysql_real_escape_string($s_level)."'";
	
//echo "$query1";
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $x,$y,$level,$mtype,$lastupdated ) = mysql_fetch_row( $result1 ))
	{	
?>
<tr>
<td><?php echo $x; ?></td>
<td><?php echo $y; ?></td>
<td><?php echo $level; ?></td>
<td><?php echo MonsterTypeToName($mtype); ?></td>
<td><?php echo GetTimeDiffShortFormat($lastupdated); ?></td>
</tr>
<?php
}
?>	
  </tbody>
</table>
<?php
include("db_connection_footer.php");
function MonsterTypeToName($type)
{
	return $type;
	$ret = "";
	return $ret;
}
?>