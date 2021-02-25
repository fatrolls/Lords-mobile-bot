<?php
foreach($_REQUEST as $foreachname=>$foreachvalue)
{
	$$foreachname = urlencode($foreachvalue);
//	echo $foreachname."=$foreachvalue,";
}
$f = fopen("http://5.79.67.171/ImportPlayerInfoFromNetwork.php?k=$k&x=$x&y=$y&name=$name&guild=$guild&CLevel=$CLevel&kills=$kills&vip=$vip&GuildRank=$GuildRank&might=$might&HasPrisoners=$HasPrisoners&PLevel=$PLevel&guildF=$guildF","rt");
if($f)
	fclose($f);

?>