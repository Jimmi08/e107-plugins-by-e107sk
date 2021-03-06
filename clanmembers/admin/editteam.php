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

$tid = intval($_GET['tid']);

$row = $sql->retrieve("clan_teams", "*", "tid='$tid' LIMIT 1");
 
	$tid = intval($row['tid']);
	$team_tag = $row['team_tag'];
	$team_name = $row['team_name'];
	$team_country = $row['team_country'];
	$banner = $row['banner'];
	$icon = $row['icon'];
	$inmembers = $row['inmembers'];
	$inwars = $row['inwars'];
	if($team_country == "") $team_country = "Unknown";
	
	?>
	<script type="text/javascript" src="includes/jquery.jcollapser.js"></script>
	<script type="text/javascript"> 
	clanm_jq(function() {
        clanm_jq("#members").jcollapser({target: '#memberslist', state: 'collapsed'});
    });
	function ChangeFlag(obj) {
		var cflag = document.getElementById("team_flag");
		cflag.src = "<?php echo e_IMAGE;?>clan/flags/"+obj.value+".png"
	}
    </script>
 
    <?php

	$text =  "<form action='admin_old.php?SaveTeam' method='post' enctype='multipart/form-data'>
	<table id='editteamtable' class='table adminform'>
		<tr>
			<td align='left'>"._TAG.":&nbsp;</td>
			<td align='left'><input type='text' id='team_tag' name='team_tag' value='$team_tag'></td>
		</tr>
		<tr>
			<td align='left'>"._NAME.":&nbsp;</td>
			<td align='left'><input type='text' id='team_name' name='team_name' value='$team_name'></td>
		</tr>
		<tr>
			<td>"._INFOCountry.": </td>
			<td>	
			<select name='team_country' onChange='ChangeFlag(this);' id='team_country'>";
		
			$files = array();	
			$TrackDir=opendir(e_IMAGE."clan/flags");
				while ($file = readdir($TrackDir)) {  
					  if ($file == "." || $file == ".." || $file == "Thumbs.db") { 
					  }else{
						  $file = explode(".",$file);
						  if(in_array(strtolower($file[1]),array("gif","jpg","png")))
						  $files[] = $file[0];
					  } 
				 }  
			closedir($TrackDir);
			sort($files);
		
			foreach($files as $file){
				$text .= "<option value='$file' ".(($team_country == $file) ? "selected" : "").">$file</option>";
			}
		
		$text .= "</select> <img src='".e_IMAGE."clan/flags/$team_country.png' id='team_flag'>
		  </td>
		</tr>";
		if($banner !="" && file_exists(e_IMAGE."clan/teams/$banner")){
		$wihei = "";
		$size = getimagesize(e_IMAGE."clan/teams/$banner");
		if($size[0] > 150){
			$wihei = "width='150'";
			$newh = 150 / $size[0] * $size[1];
			if($newh > 30){
				$wihei = "height='30'";
			}
		}elseif($size[1] > 30){
			$wihei = "height='30'";
		}
	
		$text .= "<tr>
				<td align='left' valign='top' style='padding-top:3px;'>"._CURRBANNER.":&nbsp;</td>
				<td align='left' valign='middle'><img src='".e_IMAGE."clan/teams/$banner' border='0' $wihei align='absmiddle' vspace='3'>&nbsp;&nbsp;<label><input type='checkbox' name='delbanner' value='1'>"._DEL."</label></td>
			</tr>
			<tr>	
				<td align='left'>"._UPLNEW.":&nbsp;</td>
				<td align='left'><input type='file' name='teambanner'></td>
			</tr>";
		}else{
		$text .= "<tr>	
				<td align='left'>"._BANNER.":&nbsp;</td>
				<td align='left'><input type='file' name='teambanner'></td>
			</tr>";
		}
		if($icon !="" && file_exists(e_IMAGE."clan/teams/$icon")){

		$text .= "<tr>
				<td align='left' valign='top' style='padding-top:3px;'>"._CURRICON.":&nbsp;</td>
				<td align='left' valign='middle'><img src='".e_IMAGE."clan/teams/$icon' border='0' align='absmiddle' vspace='3'>&nbsp;&nbsp;<label><input type='checkbox' name='delicon' value='1'>"._DEL."</label></td>
			</tr>
			<tr>	
				<td align='left'>"._UPLNEW.":&nbsp;</td>
				<td align='left'><input type='file' name='teamicon'></td>
			</tr>";
		}else{
		$text .= "<tr>	
				<td align='left'>"._ICON.":&nbsp;</td>
				<td align='left'><input type='file' name='teamicon'></td>
			</tr>";
		}
		if(isset($pref['plug_installed']['clanwars'])){
			$text .= "<tr>	
				<td align='left'>"._INMEMBERS.":&nbsp;</td>
				<td align='left'><label><input type='radio' name='inmembers' value='1'".($inmembers?" checked":"")." />"._YES."</label>&nbsp;<label><input type='radio' name='inmembers' value='0'".($inmembers?"":" checked")." />"._NO."</label></td>
			</tr>
			<tr>	
				<td align='left'>"._INWARS.":&nbsp;</td>
				<td align='left'><label><input type='radio' name='inwars' value='1'".($inwars?" checked":"")." />"._YES."</label>&nbsp;<label><input type='radio' name='inwars' value='0'".($inwars?"":" checked")." />"._NO."</label></td>
			</tr>";
		}else{
			$text .= "<input type='hidden' name='inmembers' value='1' />
					<input type='hidden' name='inwars' value='1' />";
		}
		$text .= "<tr>
			<td align='left' colspan='2'>
			
			<br /><br />
			
			<div id='members'>
				<div class='jm-collapse'><b>"._EDITMEMBERS." -</b></div>
				<div class='jm-expand'><b>"._EDITMEMBERS." +</b></div>
			</div>
			<br />				
			<div id='memberslist'>";
		 
			$members = $sql->retrieve("SELECT u.user_name, i.userid FROM #clan_members_info i, #user u WHERE u.user_id=i.userid Order BY u.user_name", true);
      	foreach($members as $row){
					$memberid = $row['userid'];
					$member = $row['user_name'];
					$match = e107::getDB()->count("clan_members_teamlink", "(*)", "WHERE userid='$memberid' and tid='$tid'");
					if($match>0){$chk="checked";}else{$chk="";}
					$text .= "<label><input name='add$memberid' value='1' type='checkbox' $chk>$member</label><br />";					
				}
			$text .= "</div></td>
		</tr>
		<tr>
			<td align='left' colspan='2'><br /><input type='hidden' name='tid' value='$tid'>
			<input type='hidden' name='e-token' value='".e_TOKEN."' />
			<input type='submit' class='button' name='submit' value='"._SAVECHANGES."'></td>
		</tr>
	</table>
	</form>";
	
	$ns->tablerender(_EDITTEAM, $text);
	
?>