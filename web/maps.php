<?php
$DisableCaching = 1;
if(!isset($dbi))
	include("db_connection.php");
?>
<table>
	<tr><td><a href="might_<?php echo $k;?>.html" target="IFShowContent">Might distribution on the map</a></td></tr>
	<tr><td><a href="kills_<?php echo $k;?>.html" target="IFShowContent">Killer players distribution on the map</a></td></tr>
	<tr><td><a href="pcount_<?php echo $k;?>.html" target="IFShowContent">Number of players on the map</a></td></tr>
	<tr><td><a href="guildless_<?php echo $k;?>.html" target="IFShowContent">Number of guildless players on the map</a></td></tr>
	<tr><td><a href="guildless_innactive_<?php echo $k;?>.html" target="IFShowContent">Guildless players with X hours passed since burned</a></td></tr>
	<tr><td><a href="castlelevel_<?php echo $k;?>.html" target="IFShowContent">Average castle level in the area</a></td></tr>
	<tr><td><a href="resourcelevel_<?php echo $k;?>.html" target="IFShowContent">Average resource node level in area</a></td></tr>
	<tr><td><a href="resourcefree_<?php echo $k;?>.html" target="IFShowContent">Number of free resources</a></td></tr>
</table>
<?php
include("db_connection_footer.php");
?>