<?php
/*
+ -----------------------------------------------------------------+
| e107: Clan Members 1.0                                           |
| ===========================                                      |
|                                                                  |
| Copyright (c) 2011 Untergang                                     |
| http://www.udesigns.be/                                          |
|                                                                  |
| This file may not be redistributed in whole or significant part. |
+------------------------------------------------------------------+
*/

if (!defined('CM_ADMIN')) {
	die ("Access Denied");
}

/******  REPLACE OLD GLOBALS  *************************************************/
$sql = e107::getDB();
$tp = e107::getParser();
/******  REPLACE OLD GLOBALS END  *********************************************/

$rid = intval($_GET['rid']);
	?>
	<script type="text/javascript">
	function CheckForm(){
		if(document.getElementById('rank').value !=""){
			return true;
		}else{
			alert("<?php $text .= _FILLINTITLE;?>");
			return false;
		}
	}	
	</script>	
	<?php

$row = $sql->retrieve("clan_members_ranks", "*", "WHERE rid='$rid'");
 
$rank = $row['rank'];
$rimage = $row['rimage'];

$text = "<center><form method='post' action='admin_old.php?SaveRank' enctype='multipart/form-data' onSubmit='return CheckForm();'>
	<table border='0' class='table adminform'>
		<tr>
			<td align='left'>"._INFORank.": </td>
			<td align='left'><input type='text' id='rank' name='rank' value='$rank' size='15' maxlength='100'></td>
		</tr>
		<tr>
			<td align='left'>"._IMG.": </td>
			<td align='left'><input type='file' name='rankimage' size='15' ></td>
		</tr>
		<tr>
			<td align='left' colspan='2'><span style='font-size: 9px;'>"._LEAVEEMPTYNOTCHANGE."</span></td>
		</tr>
		<tr>
			<td align='left' colspan='2'><input type='hidden' name='rid' value='$rid'>
			<br><input type='submit' class='button' value='"._SAVECHANGES."'></td>
		</tr>
	</table>						
	<input type='hidden' name='e-token' value='".e_TOKEN."' />
</form>";

$ns->tablerender(_EDITRANK, $text);

?>