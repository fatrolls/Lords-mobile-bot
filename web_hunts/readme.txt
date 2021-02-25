- the web part is mostly to view the DB content. You can ditch that and slap a simple "show table data" interface
- edit "db_connection.php" and set : $servername, $username, $password, $database
- edit "UploadData.php" and make sure that code blocks that forward data to remote server are "removed" : 
		if(isset($k) && $ForwardObjectType==112)
		.....
		if( isset($k) && $ForwardObjectType==109)
		.....
- import db "DB.sql" for the website to show something
- it's essential to have "monstertypes" table up to date or new monster hunts will not register
- if DB gets too large, you may run from time to time "CompressDB.php"
- run the bot to start uploading data to the website
