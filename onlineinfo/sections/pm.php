<?php
if (!defined('e107_INIT')) { exit; }

if(e107::isInstalled('pm'))  {  

$plugPref  =  e107::pref('onlineinfo');
$pm_prefs  = e107::getPlugPref('pm');
include_once(e_PLUGIN.'pm/pm_func.php');
$pmManager = new pmbox_manager($pm_prefs);

function onlineinfo_pm_show_popup()
			{
				global $pm_inbox, $pm_prefs;
				$alertdelay = intval($pm_prefs['popup_delay']);
				if($alertdelay == 0) { $alertdalay = 60; }
				setcookie("pm-alert", "ON", time()+$alertdelay);
				$popuptext = "
				<html>
					<head>
						<title>".$pm_inbox['inbox']['new']." ".ONLINEINFO_LAN_PM_109."</title>
						<link rel=stylesheet href=" . THEME . "style.css>
					</head>
					<body style=\'padding-left:2px;padding-right:2px;padding:2px;padding-bottom:2px;margin:0px;text-align:center\' marginheight=0 marginleft=0 topmargin=0 leftmargin=0>
					<table style=\'width:100%;text-align:center;height:99%;padding-bottom:2px\' class=\'bodytable\'>
						<tr>
							<td width=100% >
								<center><b>--- ".ONLINEINFO_LAN_PM." ---</b><br />".$pm_inbox['inbox']['new']." ".ONLINEINFO_LAN_PM_109."<br />".$pm_inbox['inbox']['unread']." ".ONLINEINFO_LAN_PM_37."<br /><br />
								<form>
									<input class=\'button\' type=\'submit\' onclick=\'self.close();\' value = \'".ONLINEINFO_LAN_PM_110."\' />
								</form>
								</center>
							</td>
						</tr>
					</table>
					</body>
				</html> ";
				$popuptext = str_replace("\n", "", $popuptext);
				$popuptext = str_replace("\t", "", $popuptext);
				$text .= "
				<script type='text/javascript'>
				winl=(screen.width-200)/2;
				wint = (screen.height-100)/2;
				winProp = 'width=200,height=100,left='+winl+',top='+wint+',scrollbars=no';
				window.open('javascript:document.write(\"".$popuptext."\");', \"pm_popup\", winProp);
				</script >";
				return $text;
	}


 

	if(check_class($orderclass)){


if ($orderhide == 1)
    {

$text .= "<div id='pm-title' style='cursor:hand; text-align:left; font-size: ".$onlineinfomenufsize."px; vertical-align: middle; width:".$onlineinfomenuwidth."; font-weight:bold;' title='".ONLINEINFO_LOGIN_MENU_L29."'>&nbsp;".ONLINEINFO_LOGIN_MENU_L29." (".$unreadpms.")</div>";
$text .= "<div id='pm' class='switchgroup1' style='display:none'>";
$text .= "<table style='text-align:left; width:".$onlineinfomenuwidth."; margin-left:20px;'><tr><td>";

}else{
$text .= "<div class='smallblacktext' style='text-align:left; font-size: ".$onlineinfomenufsize."px; vertical-align: middle; width:".$onlineinfomenuwidth."; font-weight:bold;' title='".ONLINEINFO_LOGIN_MENU_L29."'>".ONLINEINFO_LOGIN_MENU_L29."</div>";
$text .= "<div>";
$text .= "<table style='text-align:left; width:".$onlineinfomenuwidth."; margin-left:20px;'><tr><td>";
}

 
			pm_getInfo('clear');

			define("OLPM_INBOX_ICON", "<img src='".e_PLUGIN_ABS."onlineinfo/images/mail_get.png' style='height:16px; border:0' alt='".ONLINEINFO_LAN_PM_25."' title='".ONLINEINFO_LAN_PM_25."' />");
			define("OLPM_OUTBOX_ICON", "<img src='".e_PLUGIN_ABS."onlineinfo/images/mail_send.png' style='height:16px; border:0' alt='".ONLINEINFO_LAN_PM_26."' title='".ONLINEINFO_LAN_PM_26."' />");
			define("PM_SEND_LINK", ONLINEINFO_LAN_PM_35);
			define("NEWPM_ANIMATION", "<img src='".e_PLUGIN_ABS."pm/images/newpm.gif' alt='' style='border:0' />");

			$sc_style['PM_SEND_PM_LINK']['pre'] = "	<br />[<a href='";
			$sc_style['PM_SEND_PM_LINK']['post'] = "'>".PM_SEND_LINK."</a>]  ";

			$sc_style['PM_INBOX_FILLED']['pre'] = "[";
			$sc_style['PM_INBOX_FILLED']['post'] = "%]";

			$sc_style['PM_OUTBOX_FILLED']['pre'] = "[";
			$sc_style['PM_OUTBOX_FILLED']['post'] = "%]";

			$sc_style['PM_NEWPM_ANIMATE']['pre'] = "<a href='".e_PLUGIN_ABS."pm/pm.php?inbox'>";
			$sc_style['PM_NEWPM_ANIMATE']['post'] = "</a>";


			if(!defined($pm_menu_template))
			{
				$pm_menu_template = "
				<a href='".e_PLUGIN_ABS."pm/pm.php?inbox'>".OLPM_INBOX_ICON."</a>
				<a href='".e_PLUGIN_ABS."pm/pm.php?inbox'>".ONLINEINFO_LAN_PM_25."</a>
				 {PM_NEWPM_ANIMATE}
				<br />
				 {PM_INBOX_TOTAL} ".ONLINEINFO_LAN_PM_36.", {PM_INBOX_UNREAD} ".ONLINEINFO_LAN_PM_37." {PM_INBOX_FILLED} 
				<br />
				<a href='".e_PLUGIN_ABS."pm/pm.php?outbox'>".OLPM_OUTBOX_ICON."</a>
				<a href='".e_PLUGIN_ABS."pm/pm.php?outbox'>".ONLINEINFO_LAN_PM_26."</a><br />
				{PM_OUTBOX_TOTAL}  ".ONLINEINFO_LAN_PM_36.", {PM_OUTBOX_UNREAD} ".ONLINEINFO_LAN_PM_37."  {PM_OUTBOX_FILLED}
				  {PM_SEND_PM_LINK}  
        	{PM_BLOCKED_SENDERS_MANAGE}
				";
        
			}

			if(check_class($pm_prefs['pm_class']))
			{
					$tp = e107::getParser();
         	$sc = e107::getScBatch('pm',TRUE, 'pm');
				 
        $pm_inbox = $pmManager->pm_getInfo('inbox');
 
				$text .= $tp->parseTemplate($pm_menu_template, TRUE, $sc);
 
				if($pm_inbox['inbox']['new'] > 0 && $pm_prefs['popup'] && strpos(e_REQUEST_URI, "pm.php") === FALSE && $_COOKIE["pm-alert"] != "ON")
				{
					$text .= onlineinfo_pm_show_popup();
					
					if($plugPref['onlineinfo_sound']!="none" || $plugPref['onlineinfo_sound']!=""){
						
							$checkpath = explode("/pm/",e_REQUEST_URI);
	
	if($checkpath[1] != "pm.php"){
				$text.="<embed src=\"".e_PLUGIN_ABS."onlineinfo/sounds/".$plugPref['onlineinfo_sound']."\" autostart=\"true\" loop=\"1\" hidden=\"true\"></embed>";
				}
}
				}

			}


	$text .= "</td></tr></table><br /></div>";

		}

 
}

?>