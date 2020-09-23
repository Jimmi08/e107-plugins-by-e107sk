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

if (!defined('CM_PUB')) {
    die ("You can't access this file directly...");
}

/******  REPLACE OLD GLOBALS  *************************************************/
$sql = e107::getDB();
/******  REPLACE OLD GLOBALS END  *********************************************/

$view = $_GET['view'];
if(!in_array($view, array("Games", "Teams", "Contact"))) $view = $conf['gamesorteams'];

//Check rank order
$sql1 = new db;
$sql2 = new db;
if(!$conf['rank_per_game']){
$table = ($conf['gamesorteams'] == "Games" ? "gamelink" : "teamlink");

if($rows = $sql->retrieve("clan_members_".$table, "*", '', true)) {  
  	foreach($rows as $row){
  		$member = $row['userid'];
  		$id = $row['id'];		
  		$rid = $sql->retrieve("clan_members_info", "rank", "userid='$member'");
  		e107::getDB()->gen("UPDATE #clan_members_".$table." SET rank='$rid' WHERE id='$id'");
  	}
  }
}
else {
  $table =  "teamlink" ;
  if($rows = $sql->retrieve("clan_members_".$table, "*", '', true)) {  
    	foreach($rows as $row){
    		$member = $row['userid'];     
    		$id = $row['id'];		
    		$rid = $sql->retrieve("clan_members_info", "rank", "userid='$member'");
    		e107::getDB()->gen("UPDATE #clan_members_".$table." SET rank='$rid' WHERE id='$id'");
    	}
    }
  }
 
	
//Check for dubbels in members table	
if($rows = $sql->retrieve("clan_members_gamelink", "*", '', true)) { 
 foreach($rows as $row){
		$member = $row['userid'];
		$gid = $row['gid'];	
		$id = $row['id'];
		$match = e107::getDB()->count("clan_members_info", "(*)", "WHERE userid='$member'");
		if($match == 0){
			e107::getDB()->delete("clan_members_gamelink", "userid='$member'");			
		}else{
			$match = e107::getDB()->count("clan_members_gamelink", "(*)", "WHERE userid='$member' and gid='$gid'");
			if($match > 1){
				e107::getDB()->delete("clan_members_gamelink", "id='$id'");
			}
		}
	}
}


//Check for dubbels in teammlink table	
if($rows = $sql->retrieve("clan_members_teamlink", "*", '', true)) { 
 foreach($rows as $row){
		$member = $row['userid'];
		$tid = $row['tid'];	
		$id = $row['id'];
		$match = e107::getDB()->count("clan_members_info", "(*)", "WHERE userid='$member'");
		if($match == 0){
			e107::getDB()->delete("clan_members_teamlink", "userid='$member'");			
		}else{
			$match = e107::getDB()->count("clan_members_teamlink", "(*)", "WHERE userid='$member' and tid='$tid'");
			if($match > 1){
				e107::getDB()->delete("clan_members_teamlink", "id='$id'");
			}
		}
	}
}
 
//Check activity
if($conf['inactiveafter'] > 0){
	$expiredate = time() - ($conf['inactiveafter'] * 24 * 3600);
 
  $rows=	$sql->retrieve("SELECT i.userid FROM #clan_members_info i, #user u WHERE u.user_id=i.userid AND u.user_lastvisit < $expiredate AND u.user_currentvisit < $expiredate", true);
	foreach($rows as $row){
		$member = $row['userid'];
		e107::getDB()->update("clan_members_info", "active='0' WHERE userid='$member'");
	}
}

//END CHECKS
$conf['gamesorteams'] = $view;
if($conf['gamesorteams'] == "Games"){
	$catstable = "clan_games";
	$linktable = "clan_members_gamelink";
}else{
	$catstable = "clan_teams";
	$linktable = "clan_members_teamlink";
}

$firstarray = unserialize($conf['listorder']);
$secondarray = $firstarray['show'];

$cats = e107::getDB()->count($catstable, "(*)", "WHERE inmembers='1'");

if($conf['show_opened']){
	$showgame = "expanded";
}else{
	$showgame = "collapsed";
}

?>
<link rel="StyleSheet" href="includes/jquery.fancybox.css" type="text/css" media="screen">
<script type="text/javascript" src="includes/jquery.fancybox.js"></script>
<script type="text/javascript" src="includes/jquery.jcollapser.js"></script>
<?php if($cats > 0){?>
<script type="text/javascript">
    clanm_jq(function() {
		<?php for($i=1;$i<=$cats;$i++){?>
        	clanm_jq("#cat<?php echo $i;?>").jcollapser({target: '#jcollapse<?php echo $i;?>', state: '<?php echo $showgame;?>'});
		<?php }?>
    });
</script>
<?php } ?>
<style type="text/css">
.jm-collapse{					
	padding:<?php echo  $conf['padding'];?>px;
	cursor:pointer;
}

.jm-expand{
	display:none;
	padding:<?php echo  $conf['padding'];?>px;
	cursor:pointer;
} 

.listtitle{
	font-weight:bold;
	padding:<?php echo  ($conf['padding']?$conf['padding']:5);?>px;
}

</style>
<?php
$games = e107::getDb()->count("clan_games", "(*)", "WHERE inmembers='1'");
$teams = e107::getDb()->count("clan_teams", "(*)", "WHERE inmembers='1'");

$text = "<div class='text-center'>";
$list = array();
 
if($conf['showview']){
	$text .= _VIEW.": ";
	if($games > 0) $list[] = ($view=="Games"?_INFOGames:"<a href='clanmembers.php?Main&view=Games'>"._INFOGames."</a>");
	if($teams > 0) $list[] = ($view=="Teams"?_INFOTeams:"<a href='clanmembers.php?Main&view=Teams'>"._INFOTeams."</a>");	
	if((USER or $conf['guestviewcontactinfo']) && $conf['showcontactlist']) $list[] = "<a href='clanmembers.php?Contact'>"._CONTACT."</a>";
}elseif($conf['showcontactlist']){
	if((USER or $conf['guestviewcontactinfo'])) $list[] = "<a href='clanmembers.php?Contact'>"._CONTACTLIST."</a>";
}

if(count($list)){
	$show = "";
	foreach($list as $item){
		$show .= $item." | ";
	}
	$text .= substr($show,0,-3);
	$text .= "<br />&nbsp;";
}
$text .= "<table class='table'  id='maintable' ><tr><td>";
$j=0;
$i=0;
$images = 0;

$rows = $sql->retrieve($catstable, "*", "inmembers='1' ORDER BY position ASC", true);

		foreach($rows as $row) {    
			if($conf['gamesorteams'] == "Games"){
				$gtid = $row['gid'];
				$gname = $row['gname'];
				$whereid = "gid";
			}else{
				$gtid = $row['tid'];
				$gname = $row['team_name'];
				$whereid = "tid";
			}
			$banner = $row['banner'];
			$i++;	
			
			$bannerimg = "";
			if($banner !="" && file_exists(e_IMAGE."clan/".strtolower($conf['gamesorteams'])."/$banner")){
				$bannerimg = "<img src='".e_IMAGE."clan/".strtolower($conf['gamesorteams'])."/$banner' border='0' />";
			}
				 
		$text .= "<div id='cat$i' class='fborder'>\n
				<div class='jm-collapse'>\n
				<table class='table gamebanner'>\n
					<tr class='bg-primary'>\n
						<td width='5' align='left'>$bannerimg</td>\n";
						if($conf['show_gname']){$text .= "<td nowrap align='left' valign='middle'>&nbsp;&nbsp;<b>$gname</b>&nbsp;&nbsp;</td>\n";}
					$text .= "</tr>\n
				</table></div>\n
				
				<div class='jm-expand'>\n
				<table class='table gamebanner'>\n
					<tr>\n
						<td width='5' align='left'>$bannerimg</td>\n";
						if($conf['show_gname']){$text .= "<td nowrap align='left' valign='middle'>&nbsp;&nbsp;<b>$gname</b>&nbsp;&nbsp;</td>\n";}
					$text .= "</tr>\n
				</table></div>\n
				
			</div>\n";
		$text .= "<div id='jcollapse".$i."'> 
				<table class='table members'> ";

$gmembers = e107::getDB()->count($linktable, "(*)", "WHERE $whereid='$gtid'");

if(ADMIN or !USER && $conf['guestviewcontactinfo']){
	$showcontact = true;	
}else{
	$showcontact = false;	
}

if($gmembers == 0){
	$text .=  "<tr><td class='forumheader3' style='text-align:center;'><br />"._NOMEMBERS."<br /><br /></td></tr>\n";
}else{

	if($conf['style'] == 0){
		$text .=   "<tr class='forumheader3'>";
		foreach($secondarray as $infoname){
			//See if Wars Module is active
			if(($infoname !="Last War" && $infoname !="Wars Played") or isset($pref['plug_installed']['clanwars'])){
				if(!in_array($infoname,array("Xfire","Steam","MSN","AIM","ICQ","YIM")) or $showcontact){
					$infotitle = $infolang[$infoname];
					$text .=  "<td class='fcaption' style='text-align:".$conf['titlealign'].";'><b>".$infotitle."</b></td>";
				}
			}
		}
		$text .=   "</tr>";
	}
//, u.user_msnm, u.user_icq, u.user_yim, u.user_aim, u.points, u.user_posts
	$orderby = str_replace(array("-","|","Username","Rank","Activity"),array(", "," ","u.user_name","r.rankorder","i.active"), $conf['memberorder']);
	$sql1->db_Select_gen("SELECT l.userid, u.user_name, r.rank, r.rimage, i.birthday, i.joindate, i.avatar, i.xfire, i.steam, i.active,  i.tryout, i.votedate, i.realname, i.gender, i.location, i.country 
		FROM #".$linktable." l
		INNER JOIN #clan_members_info i ON l.userid=i.userid 
		INNER JOIN #user u ON i.userid=u.user_id 
		LEFT JOIN #clan_members_ranks r ON r.rid=".(!$conf['rank_per_game']?"i":"l").".rank 
		WHERE l.$whereid='$gtid' 
		ORDER BY $orderby");
		
	$t = 0;
	while($row2 = $sql1->db_Fetch()){
		$memberid = $row2['userid'];
		$member = $row2['user_name'];
		$joindate = $row2['joindate'];
		$birthday = $row2['birthday'];
		$xfire = $row2['xfire'];
		$steam = $row2['steam'];
		$active = $row2['active'];
		$tryout = $row2['tryout'];
		$votedate = date("j M", $row2['votedate']);
		$realname = $row2['realname'];
		$gender = $row2['gender'];
		$msn = $row2['user_msnm'];
		$icq = $row2['user_icq'];
		$yim = $row2['user_yim'];
		$aim = $row2['user_aim'];
		$points = $row2['points'];
		$from = $row2['location'];
		$country = $row2['country'];
		$posts = $row2['user_posts'];
		$points = $row2['points'];
		$avatar = $row2['avatar'];
		$rank = $row2['rank'];
		$rimage = $row2['rimage'];
		
		if(e107::getDB()->count("clan_members_info", "(*)", "WHERE userid='$memberid'")==0){
			e107::getDB()->delete($linktable, "userid='$memberid'");
		}
		if($country == "" or !file_exists(e_IMAGE."clan/flags/$country.png")){
			$country = "Unknown";
		}
		
		// calculate age
		if($birthday != 1){
			$age = date('Y') - date('Y',$birthday);
			if ((date('m') < date('m',$birthday)) || (date('m') == date('m',$birthday) && date('d') < date('d',$birthday))){ 
				$age--; 
			}
			$birthday = date($conf['birthformat'],$birthday);
		}else{
			$age='';
			$birthday = "";
		}

	$url = "images/UserImages/$avatar";
		
//List Style
if($conf['style'] == 0){
	$text .=   "<tr>";
}else{
//Block Style

$blockwidth = number_format(100 / $conf['membersperrow'], 1);
if($t == 0){
	$text .=   '<tr>
				<td valign="top" align="left" width="'.$blockwidth.'%" class="forumheader3">';
	}else{
		$text .=   '<td valign="top" align="left" width="'.$blockwidth.'%" class="forumheader3">';
	}
	
	$text .=  '<table class="table" id="blockstyle">
			<tr>';
	if(VisibleInfo("User Image")){		
		if(file_exists($url) && $avatar !=""){
			$wihei = "";
			$size = getimagesize($url);
			if($size[0] > $conf['maxwidth']){
				$wihei = "width='".$conf['maxwidth']."'";
				$newh = $conf['maxwidth'] / $size[0] * $size[1];
				if($newh > $conf['maxheight']){
					$wihei = "height='".$conf['maxheight']."'";
				}
			}elseif($size[1] > $conf['maxheight']){
				$wihei = "height='".$conf['maxheight']."'";
			}
		
			$text .=  '<td width="'.$conf['maxwidth'].'" valign="top"><a id="userimage'.$images.'" href="'.$url.'"><img src="'.$url.'" border="0" '.$wihei.'/></a></td>';
			$images++;
		}else{
			$text .=  '<td width="'.$conf['maxwidth'].'" valign="top"><img src="images/Profile/noimage.jpg" border="0" width="100" /></td>';
		}
	}	
	$text .=  "<td align='left' valign='top' style='padding-left:3px;'>";
}


	foreach($secondarray as $infoname){
	
	$newcontent = "";
	$infotitle = $infolang[$infoname];

	switch($infoname){
		case "User Image":
			if($conf['style'] == 0){
				if(file_exists($url) && $avatar !=""){
					$wihei = "";
					$size = getimagesize($url);
					if($size[0] > $conf['maxwidth']){
						$wihei = "width='".$conf['maxwidth']."'";
						$newh = $conf['maxwidth'] / $size[0] * $size[1];
						if($newh > $conf['maxheight']){
							$wihei = "height='".$conf['maxheight']."'";
						}
					}elseif($size[1] > $conf['maxheight']){
						$wihei = "height='".$conf['maxheight']."'";
					}
					$newcontent .= "<a id='userimage$images' href='$url'><img src='$url' border='0' $wihei/></a>";
					$images++;
				}else{
					$newcontent .=  "&nbsp;";
				}
			}
		break;
		case "Username":
			$url = "";
			if($conf['enableprofile'] && (USER or $conf['profiletoguests'])){
				$newcontent = "<a href='clanmembers.php?Profile&memberid=$memberid' title='View Profile'>$member</a>";
			}else{
				$newcontent = "$member";
			}			
		break;
		case "Games":			
			$sql2->db_Select_gen("SELECT g.icon, g.gname, g.abbr FROM #clan_games g, #clan_members_gamelink m WHERE g.inmembers='1' and m.userid='$memberid' AND g.gid=m.gid ORDER BY g.position ASC");
			$games = "";
			while($grow = $sql2->db_Fetch()){
				$icon = $grow['icon'];
				$abbr = $grow['abbr'];
				$gname = $grow['gname'];
				$abbr = ($abbr?$abbr:$gname);
				if($icon !="" && file_exists(e_IMAGE."clan/games/$icon")){
					$games .= "<img src='".e_IMAGE."clan/games/$icon' border='0' title='$gname' alt='$abbr' />&nbsp;";
				}else{
					$games .= "$abbr, ";
				}
			}
			if(substr($games,-2) == ", ") $games = substr($games,0,-2);
			$newcontent =  $games;				
		break;
		case "Teams":			
			$sql2->db_Select_gen("SELECT t.icon, t.team_tag, t.team_name FROM #clan_teams t, #clan_members_teamlink m WHERE t.inmembers='1' and m.userid='$memberid' AND t.tid=m.tid ORDER BY t.position ASC");
			$teams = "";
			while($grow = $sql2->db_Fetch()){
				$icon = $grow['icon'];
				$team_tag = $grow['team_tag'];
				$team_name = $grow['team_name'];
				if($icon !="" && file_exists(e_IMAGE."clan/teams/$icon")){
					$teams .= "<img src='".e_IMAGE."clan/teams/$icon' border='0' title='$team_name' alt='$team_tag' />&nbsp;";
				}else{
					$teams .= "$team_tag, ";
				}
			}
			if(substr($teams,-2) == ", ") $teams = substr($teams,0,-2);
			$newcontent =   $teams;				
		break;
		case "Join Date":
			if($joindate != 1) $newcontent = date($conf['joinformat'],$joindate);
		break;
		case "Rank":
			$newcontent = $rank;			
		break;
		case "Rank Image":
			if($rimage !="" && file_exists("images/Ranks/$rimage"))
			$newcontent = "<img src='images/Ranks/$rimage' border='0' title='$rank' />";
		break;
		case "Realname":
			$newcontent = $realname;			
		break;
		case "Gender":
			if($gender !="")
			$newcontent = "$gender <img src='images/Profile/$gender.png' class='absmiddle'>";
		break;
		case "Age":
			$newcontent = $age;			
		break;
		case "Birthday":
			if($birthday != 1) $newcontent = $birthday;
		break;
		case "Location":
			$newcontent =  $from;			
		break;
		case "Country":
			$newcontent = "<img src='".e_IMAGE."clan/flags/$country.png' border='0' title='$country' class='absmiddle'>";			
		break;
		case "Xfire":
			if($showcontact && $xfire !="")
			$newcontent = $xfire ;			
		break;
		case "Steam ID":
			if($showcontact && $steam !="")
			$newcontent = "<a href='http://vacbanned.com/view/detail/id/$steam' target='_blank'>$steam</a>";			
		break;
		case "MSN":
			if($showcontact && $msn !=""){
				if(!USER && $conf['changeatdot']){
					$msn = str_replace("@","[AT]",$msn);
					$msn = str_replace(".","[DOT]",$msn);
				}
				$newcontent = $msn;
			}
		break;
		case "AIM":
			if($showcontact && $aim !="")
			$newcontent = "<a href='aim:goim?screenname=$aim&message=Hello+Are+you+there?' target='_blank'>$aim</a>";
		break;
		case "ICQ":
			if($showcontact && $icq !="")
			$newcontent = "<a href='http://wwp.icq.com/scripts/search.dll?to=$icq' target='_blank'>$icq</a>";
		break;
		case "Yahoo":
			if($showcontact && $yim !="")
			$newcontent = "<a href='http://edit.yahoo.com/config/send_webmesg?.target=$yim&.src=pg' target='_blank'>$yim</a>";
		break;
		case "Posts":
			$newcontent = $posts;			
		break;
		case "PM":
			$newcontent = "<a href='".e_PLUGIN."pm/pm.php?send.".$memberid."' title='Send Private Message'><img src='".e_PLUGIN."pm/images/pm.png' alt='PM' style='border:0;' /></a>";			
		break;
		case "Activity":			
			if($active == 1){
				$newcontent = "<span style='color:#008000;'>"._ACTIVE."</span>";
			}else{
				$newcontent = "<span style='color:#FF0000;'>"._INACTIVE."</span>";
			}
    break;
		case "Tryout":			
			if($tryout) $newcontent = $votedate;
			//Begin Wars
		break;
		case "Wars Played":
			if(isset($pref['plug_installed']['clanwars'])){
				$wars_played = $sql2->db_Count("clan_wars_lineup", "(*)", "where member='$memberid' AND available='2'");
				$newcontent = $wars_played;
			}
		break;
		case "Last War":
			if(isset($pref['plug_installed']['clanwars'])){
				$sql2->db_Select_gen("SELECT w.opp_tag, w.wid FROM #clan_wars w, #clan_wars_lineup l WHERE w.wid = l.wid AND l.member='$memberid'  AND l.available='2' ORDER BY wardate DESC LIMIT 1");
				$row5 = $sql2->db_Fetch();
				$opp_tag = $row5['opp_tag'];
				$wid = $row5['wid'];
				if($opp_tag !="")
				//$newcontent = "<a href='modulesx.php?name=Wars&op=Details&wid=$wid'>$opp_tag</a>";		
        $newcontent = "<a href='".e_PLUGIN."clanwars/clanwars.php?Details&wid=$wid'>$opp_tag</a>";					
			}
			//End Wars
		break;
	}
	
	if($conf['style'] == 0){
		$text .=  "<td class='forumheader3 nowrap' style='text-align: ".($infoname == "Username"? "left" : "center")."' >$newcontent</td>\n";
	}else{
		if($infoname == "Username"){
			$text .=  "<b>".$newcontent."</b><br />\n";
		}elseif($infoname == "Rank Image" && $newcontent !=""){
			$text .=  $newcontent."<br />\n";
		}elseif($infoname !="" && $newcontent !=""){
			$text .=  $infotitle.": ".$newcontent."<br />\n";
		}
	}
}	
	
	if($conf['style'] == 0){
		$text .=   "</tr>";
	}else{
		$text .=  "</td></tr></table>\n";
		if($t < $conf['membersperrow']-1){
			$text .=   "</td>";	
			$t++;
		}else{
			$text .=  "</td></tr>";
			$t=0;
		}
	}
}
	

	}
	
	if($t > 0 && $conf['style'] == 1){					
		for($x=$t;$t<$conf['membersperrow'];$t++){
			$text .=  "<td bgcolor='$cmcolor2' width='250'>&nbsp;</td>";
		}
		$text .=   "</tr>";
	}
	
	$text .=  "</table></div><br /><br />\n";
}
$text .=  "</td></tr></table></div>\n";

//END NEW

if($images > 0){
?>
<script type="text/javascript">
   clanm_jq(document).ready(function() {
	<?php
	$i = 0;
	for($i=0;$i<$images;$i++){
	?>
		clanm_jq("a#userimage<?php echo $i;?>").fancybox();	
	<?php
		}
	?>
	});
</script>
<?php
}

if(is_clanmember(USERID)){
	if($conf['allowchangeinfo']){
		$text .= "<div class='text-center'><a href='clanmembers.php?editinfo'>"._CHANGEURINFO."</a><br /><br /></div>";
	}elseif(VisibleInfo("User Image") && $conf['allowupimage']){			
		$text .= "<div class='text-center'><a href='clanmembers.php?editinfo'>"._UPLOADURIMAGE."</a><br /><br /></div>";
	}
}

$ns->tablerender($conf['cmtitle'], $text);


?>