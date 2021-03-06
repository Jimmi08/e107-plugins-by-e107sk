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

if (!defined('WARS_ADMIN') or !preg_match("/admin_old\.php\?AddTeam/i", $_SERVER['REQUEST_URI'])) {
	die ("Access Denied");
}

$tp = e107::getParser();
$team_tag = $tp->toDB($_POST['team_tag']);
$team_name = $tp->toDB($_POST['team_name']);
$team_country = $tp->toDB($_POST['team_country']);
$inmembers = intval($_POST['inmembers']);
$inwars = intval($_POST['inwars']);
if($team_country == "") $team_country = "Unknown";

$text = "";
//Check if theres a file selected
if(isset($_FILES['teambanner'])) {	
	//check is there a new name given
	$filename = $_FILES['teambanner']['name']; 
	if($filename !=""){
	
		$filename = explode(".", $filename);
		$ext = strtolower($filename[count($filename) -1]);
		$banner = "Banner_".preg_replace("/[^a-zA-Z0-9\s]/", "", $team_name)."-".rand(100, 999).".".$ext;
		$banner = str_replace(" ", "_", $banner);
		if($ext != "jpg" && $ext != "jpeg" && $ext != "gif" && $ext != "png"){
			$text = "<div class='text-center'><br />"._ONLYIMGSALLOWED."<br /><br /></div>";
		}else{		
			//upload the file 
			move_uploaded_file($_FILES['teambanner']['tmp_name'], e_IMAGE."clan/teams/$banner"); 
			chmod(e_IMAGE."clan/teams/$banner", 0777);			
		}
	}
}

//Check if theres a file selected
if(isset($_FILES['teamicon'])) {	
	//check is there a new name given
	$filename = $_FILES['teamicon']['name']; 
	if($filename !=""){
	
		$filename = explode(".", $filename);
		$ext = strtolower($filename[count($filename) -1]);
		$icon = "Icon_".preg_replace("/[^a-zA-Z0-9\s]/", "", $team_name)."-".rand(100, 999).".".$ext;
		$icon = str_replace(" ", "_", $icon);
		
		if($ext != "jpg" && $ext != "jpeg" && $ext != "gif" && $ext != "png"){
			$text = "<div class='text-center'><br />"._ONLYIMGSALLOWED."<br /><br /></div>";
		}else{		
			//upload the file 
			move_uploaded_file($_FILES['teamicon']['tmp_name'], e_IMAGE."clan/teams/$icon"); 
			chmod(e_IMAGE."clan/teams/$icon", 0777); 			
		}
	}
}

$row['position'] = e107::getDb()->retrieve("clan_games", "position", "ORDER BY position DESC LIMIT 1" );
$position = $row['position'] + 1;

if($team_tag !="" && $team_name !=""){
	$result = e107::getDb()->insert("clan_teams", array("team_tag" => $team_tag, "team_name" => $team_name, "team_country" => $team_country, "banner" => $banner, "icon" => $icon, "inmembers" => $inmembers, "inwars" => $inwars, "position" => $position));
	if($result){
		$text .= "<div class='text-center'><br />"._TEAMADDED."<br /><br /></div>";
		header("Refresh:1;url=admin_old.php?Teams");
	}else{
		$text .= "<div class='text-center'><br />".ERRORUPDATINGDB."<br /><br /></div>";
		header("Refresh:2;url=admin_old.php?Teams");
	}
}else{
	$text .= "<div class='text-center'><br />"._TEAMNAMEANDTAGEMPTY."<br /><br /></div>";
	header("Refresh:2;url=admin_old.php?Teams");
}

$ns->tablerender(_CLANWARS, $text);
			
?>