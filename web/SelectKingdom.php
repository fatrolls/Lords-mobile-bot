<?php
session_start();
if(isset($_REQUEST['Kingdom']))
{
	$_SESSION['k'] = $_REQUEST['Kingdom'];
//	echo "setting session to ".$_SESSION['k'];
}
//else	echo "session is ".$_SESSION['k'];

?>
<form name="KingdomSelect" id="KingdomSelect" action="">
	<table>
		<tr>
			<td>Kingdom</td>
			<td><select name="Kingdom"><option value="67">#67</option><option value="82">#82</option><option value="99" selected>#99</option></select></td>
			<td><input type="submit" value="Select"></td>
		</tr>
	</table>
</form>
