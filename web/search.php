<?php
session_start();
foreach($_REQUEST as $foreachname=>$foreachvalue)
	$$foreachname = $foreachvalue;

//click on a player name, next we will show archive info about him with the option to search other things
if(isset($FN))
{
	$s_player=$FN;
	$ExactPlayerName=1;
}
if(isset($FG))
{
	$s_guild=$FG;
	$ExactGuildName=1;
}

if(!isset($s_player))
	$s_player="";
if(isset($_REQUEST['ExactPlayerName']))
	$ExactPlayerName=$_REQUEST['ExactPlayerName'];
if(!isset($s_guild))
	$s_guild="";
if(isset($_REQUEST['ExactGuildName']))
	$ExactGuildName=$_REQUEST['ExactGuildName'];
if(!isset($s_grank))
	$s_grank="";
if(isset($_REQUEST['s_innactive']))
	$s_innactive=$_REQUEST['s_innactive'];
if(!isset($s_title))
	$s_title="";
?>
<form name="SearchForm" id="SearchForm" action="">
	<table>
		<tr>
			<td>Player</td>
			<td><input type="text" name="s_player" value="<?php echo $s_player; ?>"></td>
			<td>Exact match<input type="checkbox" name="ExactPlayerName" <?php if(isset($ExactPlayerName)) echo "checked";?>></td>
		</tr>
		<tr>
			<td>Guild</td>
			<td><input type="text" name="s_guild" value="<?php echo $s_guild; ?>"></td>
			<td>Exact match<input type="checkbox" name="ExactGuildName" <?php if(isset($ExactGuildName)) echo "checked";?>></td>
		</tr>
		<tr>
			<td>Innactive<input type="checkbox" name="s_innactive" <?php if(isset($s_innactive)) echo "checked";?>></td>
			<td>Rank<select name="s_grank">
					<option value="">Any</option>
					<option value="6" <?php if($s_grank==6) echo "selected";?> >Guildless</option>
					<option value="1" <?php if($s_grank==1) echo "selected";?> >R1</option>
					<option value="2" <?php if($s_grank==2) echo "selected";?> >R2</option>
					<option value="3" <?php if($s_grank==3) echo "selected";?> >R3</option>
					<option value="4" <?php if($s_grank==4) echo "selected";?> >R4</option>
					<option value="5" <?php if($s_grank==5) echo "selected";?> >Owner</option>
				</select>
			</td>
			<td>Title<select name="s_title">
					<option value="">Any</option>
					<option value="1" <?php if($s_title==1) echo "selected";?> >Overlord</option>
					<option value="2" <?php if($s_title==2) echo "selected";?> >Queen</option>
					<option value="3" <?php if($s_title==3) echo "selected";?> >General</option>
					<option value="4" <?php if($s_title==4) echo "selected";?> >Premier</option>
					<option value="5" <?php if($s_title==5) echo "selected";?> >Chief</option>
					<option value="6" <?php if($s_title==6) echo "selected";?> >Warden</option>
					<option value="7" <?php if($s_title==7) echo "selected";?> >Priest</option>
					<option value="8" <?php if($s_title==8) echo "selected";?> >Quartermaster</option>
					<option value="9" <?php if($s_title==9) echo "selected";?> >Engeneer</option>
					<option value="10" <?php if($s_title==10) echo "selected";?> >Scholar</option>
					<option value="11" <?php if($s_title==11) echo "selected";?> >Coward</option>
					<option value="12" <?php if($s_title==12) echo "selected";?> >Scoundrel</option>
					<option value="13" <?php if($s_title==13) echo "selected";?> >Clown</option>
					<option value="14" <?php if($s_title==14) echo "selected";?> >Thrall</option>
					<option value="15" <?php if($s_title==15) echo "selected";?> >Traitor</option>
					<option value="16" <?php if($s_title==16) echo "selected";?> >Felon</option>
					<option value="19" <?php if($s_title==19) echo "selected";?> >Fool</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><input type="submit" value="Search"></td>
		</tr>
	</table>
</form>
<?php
$ShowResults = "1";
if(strlen($s_player)>0)
{
	if(isset($ExactPlayerName))
	{
		$FN=$s_player;
		$ShowResults .= "&FN=".urlencode($s_player);
	}
	else
	{
		$FNS=$s_player;
		$ShowResults .= "&FNS=".urlencode($s_player);
	}
}
if(strlen($s_guild)>0)
{
	if(isset($ExactGuildName))
	{
		$FG = $s_guild;
		$ShowResults .= "&FG=".urlencode($s_guild);
	}
	else
	{
		$FGS = $s_guild;
		$ShowResults .= "&FGS=".urlencode($s_guild);
	}
}
if(isset($s_grank) && $s_grank != "" && $s_grank<6)
{
	$FGR = $s_grank;
	$ShowResults .= "&FGR=".urlencode($s_grank);
}
if(isset($s_innactive) && $s_innactive != "" )
{
	$FI=1;
	$ShowResults .= "&FI=1";
}
if(isset($s_title) && $s_title != "" )
{
	$FT=$s_title;
	$ShowResults .= "&FT=".urlencode($s_title);
}
if($ShowResults!="1")
{
//	echo "ShowResults=$ShowResults";
	$PlayersPhpIncluded = 1;
	include("players.php");
}
?>
