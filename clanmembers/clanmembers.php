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

if(!defined("e107_INIT")) 
{
	require_once("../../class2.php");
}
if (!isset($pref['plug_installed']['clanmembers']))
{
	header('Location: '.e_BASE.'index.php');
	exit;
}

/******  REPLACE OLD GLOBALS  *************************************************/
$sql = e107::getDB();
$tp = e107::getParser();
/******  REPLACE OLD GLOBALS END  *********************************************/


include_lan(e_PLUGIN.'clanmembers/languages/'.e_LANGUAGE.'/clanmembers.php');
include_lan(e_PLUGIN.'clanmembers/languages/'.e_LANGUAGE.'/clanmembers_common.php');

if (strstr(e_QUERY, "untrack"))
{
	$tmp1 = explode(".", e_QUERY);
	$tmp = str_replace("-".$tmp1[1]."-", "", USERREALM);
	e107::getDB()->update("user", "user_realm='".$tp -> toDB($tmp, true)."' WHERE user_id='".USERID."' ");
	header("location:".e_SELF."?track");
	exit;
}

require_once(HEADERF);
echo '<script type="text/javascript">var clanm_jq = jQuery;</script>'."\n";

$conf = $sql -> retrieve("clan_members_config", "*", 'LIMIT 1' );
 
define('CM_PUB', true);

include("includes/infolang.php");
include("includes/functions.php");

$action = e_QUERY;
if($action == "") $action = "main";
	$dot = explode("&", $action);
	$action = $dot[0];
	if(file_exists("public/".strtolower($action).".php")){
		include("public/".strtolower($action).".php");
	}
require_once(FOOTERF);
exit;
?>