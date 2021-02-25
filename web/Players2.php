<?php
ini_set('memory_limit','160M');
include("db_connection.php");

//compress the page
if (substr_count($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip"))
	ob_start("ob_gzhandler"); 
else 
	ob_start();

if(!isset($FilterK))
	$FilterK = 67;

$SimpleView = 1;
if(isset($FN) || isset($FG) )
	$SimpleView = 0;
else
	echo "To minimize page size only player coordinates are shown. If you wish to get more info, click on player name<br>";

?>
<style>
.TFtable{
	width:100%; 
	border-collapse:collapse; 
}
.TFtable td{ 
	padding:7px; border:#4e95f4 1px solid;
}
.TFtable tr{
	background: #b8d1f3;
}
.TFtable tr:nth-child(odd){ 
	background: #b8d1f3;
}
.TFtable tr:nth-child(even){
	background: #dae5f4;
}
</style>
Hidden players are not shown!<br>
Selected kingdom is <?php echo $FilterK;?><br>
<table>
  <thead style="background-color: #60a917">
	<tr>
		<td>x</td>
		<td>y</td>
		<td>Name</td>
		<td>Guild</td>
		<?php if( $SimpleView == 0 )
		{
			?>
		<td>Might</td>
		<td>Kills</td>
		<td>Guild rank</td>
		<td>VIP Level</td>
		<td>Castle Level</td>
			<?php
		}
		?>
		<td>Last Updated</td>
<!--		<td>Last Burned at</td>
		<td>Player Level</td>
		<td>Last seen with prisoners</td>
		<td>Innactive</td>
		<td>Last Burned at might</td>
		<td>Aprox troops available</td>
		<td>Nodes gathering from</td>
		<td>Castle lvl</td>
		<td>Bounty</td>
		<td>Distance to hive</td>
		<td>Active at X hours</td>
		<td>Active Y hours a day</td>
		<td>First seen ever(age)</td> -->
	</tr>
  </thead>
  <tbody id="InsertTableRowsHere" class="TFtable">
  </tbody>
</table>
<script>
	<?php
	// do not show hidden players
	$HiddenNames = "";
	$query1 = "select name from players_hidden where EndStamp > ".time();
//echo "$query1<br>";
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $name ) = mysql_fetch_row( $result1 ) )
		$HiddenNames .= "####$name####";

	$HiddenGuilds = "";
	$Filter = "";
	$Order = " lastupdated desc ";
	$query1 = "select name from guilds_hidden where EndStamp > ".time();
//echo "$query1<br>";
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $name ) = mysql_fetch_row( $result1 ) )
		$HiddenGuilds .= "####$name####";
		
	$query1 = "select x,y,name,guild,might,kills,lastupdated,innactive,HasPrisoners,VIP,GuildRank,PLevel,castlelevel from ";
	if(isset($FN))
		$query1 .= "players_archive ";
	else
		$query1 .= "players ";
	
	if(isset($FN))
	{
		//remove "guild" from player name
		$namename = substr($FN,strpos($FN,']'));
		$Filter .= " and name like '%]".mysql_real_escape_string($namename)."' ";
	}
	if(isset($FG))
		$Filter .= " and guild like '".mysql_real_escape_string($FG)."' ";
	
	if($Filter)
		$query1 .= " where 1=1 $Filter ";
	if($Order)
		$query1 .= " order by $Order ";
	
	if( isset($FN) )
	{
		//remove ordering
		$query1 = str_replace(" order by $Order ","", $query1);
		$q2 = str_replace("players_archive","players", $query1);
		$query1 = "($q2)union($query1) order by $Order";
	}
//echo $query1."\n<br>";
	
	$TableData = "";
	$result1 = mysql_query($query1,$dbi) or die("2017022001".$query1);
	while( list( $x,$y,$name,$guild,$might,$kills,$lastupdated,$innactive,$HasPrisoners,$VIP,$GuildRank,$Plevel,$castlelevel ) = mysql_fetch_row( $result1 ))
	{
		$namename = substr($name,strpos($name,']')+1);
		
		if( strpos($HiddenNames,"#".$name."#") != 0 )
			continue;
		if( strpos($HiddenGuilds,"#".$guild."#") != 0 )
			continue;
		
		$LastUpdatedHumanFormat = gmdate("Y-m-d\TH:i:s\Z", $lastupdated);
		//$innactiveHumanFormat = gmdate("Y-m-d\TH:i:s\Z", $innactive);
		$PlayerArchiveLink = "?FN=".urlencode($namename);
		$GuildFilterLink = "?FG=".urlencode($guild);
		$HasPrisonersHumanFormat = gmdate("Y-m-d\TH:i:s\Z", $HasPrisoners);	
		$LastUpdatedAsDiff = GetTimeDiffShortFormat($lastupdated);
		$HasPrisonersAsDiff = GetTimeDiffShortFormat($HasPrisoners);
		if($HasPrisonersAsDiff=="48.4 y")
			$HasPrisonersAsDiff="";
		if($guild=="")
			$guild="&nbsp;";
/*			<td><?php echo $HasPrisonersAsDiff; ?></td>
			<td><?php echo $innactive; ?></td> 
			<td><?php echo $Plevel; ?></td>
			*/
		AddStringCol($TableData,$x);
		AddStringCol($TableData,$y);			
		AddStringCol($TableData,$namename);			
		AddStringCol($TableData,$guild);			
		if( $SimpleView == 0 )
		{
			AddStringCol($TableData,GetValShortFormat($might));
			AddStringCol($TableData,GetValShortFormat($kills));
			AddStringCol($TableData,$GuildRank);
			AddStringCol($TableData,$VIP);			
			AddStringCol($TableData,$castlelevel);			
		}
		AddStringCol($TableData,$LastUpdatedAsDiff);			
		$TableData = substr($TableData,0,-1);
		$TableData .= "\t\t";
//		break;
	}
	$TableData = substr($TableData,0,-2);
	$TableData .= "\"";

	echo "var TableDataStr = \"".$TableData.";\n";
	
function AddStringCol( &$TableData, $col )
{
//	$TableData .= "\"$col\",";
	$col = str_replace("\"","\\\"", $col);
	if($col == NULL || $col == "" )
		$col = ' ';
	$TableData .= "$col\t";
}
?>
var TableData = TableDataStr.split("\t\t", -1);
var textToAppend= createTableFromData(TableData);
var oldTable = document.getElementById('InsertTableRowsHere');
oldTable.innerHTML = textToAppend;

function createTableFromData(data)
{
    var tableHtml = '';
    var currentRowHtml;
	var RowLen = data.length;
	var ColLen = data[0].length;
    for (var i = 0; i < RowLen; i++)
	{
		var bg;
/*		if(i%2==1)
			bg="style=\"background: #b8d1f3\"";
		else
			bg="style=\"background: #dae5f4\""; */
		
		currentRowHtml = '<tr>';
//		currentRowHtml = '<tr ' + bg + ' >';
		var Row = data[i];
		var Columns = Row.split('\t', -1);
		for(var j = 0; j < Columns.length; j++)
		{		
			if(j==2)
				currentRowHtml += '<td><a href=\"?FN=' + encodeURIComponent(Columns[j])+ '\">' + Columns[j] + '</td>';
			else if(j==3)
				currentRowHtml += '<td><a href=\"?FG=' + encodeURIComponent(Columns[j])+ '\">' + Columns[j] + '</td>';				
			else
				currentRowHtml += '<td>' + Columns[j] + '</td>';
		}
		currentRowHtml += '</tr>';
		tableHtml += currentRowHtml;
    }  
    return tableHtml;    
}
</script>