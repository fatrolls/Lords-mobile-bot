<?php
if(isset($dbi) && !isset($PlayersPhpIncluded))
	mysql_close($dbi);

if(isset($CurCacheFileName) && $CurCacheFileName != "" )
	AutoCacheEnd();
?>