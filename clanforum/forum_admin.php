<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.7/e107_plugins/forumx/forumx_admin.php $
|     $Revision: 13011 $
|     $Id: forumx_admin.php 13011 2012-10-28 16:26:00Z e107steved $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/
$eplug_admin = true;
if(!empty($_POST) && !isset($_POST['e-token']))
{
	// set e-token so it can be processed by class2
	$_POST['e-token'] = ''; // TODO - regenerate token value just after access denied?
}
require_once("../../class2.php");

 
include_lan(CLAN_FORUM_LANGUAGES.'lan_forum_admin.php');

if (!e107::isInstalled(CLAN_FORUM_FOLDER) || !getperms("P"))
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = CLAN_FORUM_FOLDER;

$clanforum = new clanforum;

if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0]; //needed by auth.php
	$sub_action = varset($tmp[1]);
	$id = intval(varset($tmp[2], 0));
	unset($tmp);
}

require_once(e_ADMIN.'auth.php');
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
require_once(e_HANDLER."ren_help.php");
require_once(CLAN_FORUM_FORUMCLASS);

$rs = new form;
$clanfor = new e107clanforum;
$plugPref =  e107::pref(CLAN_FORUM_FOLDER);

define("IMAGE_new", "<img src='".img_path('new.png')."' alt='' style='border:0' />");
define("IMAGE_sub", "<img src='".CLAN_FORUM_APP."images/forums_16.png' alt='".FORLAN_145."' title='".FORLAN_145."' style='border:0' />");
define("IMAGE_nosub", "<img src='".CLAN_FORUM_APP."images/sub_forums_16.png' alt='".FORLAN_145."' title='".FORLAN_145."' style='border:0' />");

$deltest = array_flip($_POST);


if(isset($_POST['delete']))
{
	$tmp = array_pop(array_flip($_POST['delete']));
	list($delete, $del_id) = explode("_", $tmp);
	$del_id = intval($del_id);
}

if(isset($_POST['setMods']))
{
	foreach($_POST['mods'] as $fid => $modid)
	{
		$fid = intval($fid); $modid = intval($modid);
		e107::getDb()->update(CLAN_FORUM_TABLE_NOPREFIX,"forum_moderators = '{$modid}' WHERE forum_id = {$fid}");
	}
	$clanforum->show_message(FORLAN_144);
}

if(isset($_POST['tools']))
{
	$msg = "";
	if(isset($_POST['forum_all']))
	{
		$fList[]='all';
	}
	else
	{
		foreach(array_keys($_POST['forumlist']) as $k)
		{
			$fList[] = $k;
		}
	}
	foreach($fList as $fid)
	{
		if(isset($_POST['counts']))
		{
			$clanfor->forum_update_counts($fid, $_POST['counts_threads']);
			$msg .= FORLAN_167.": $fid <br />";
		}
		if(isset($_POST['lastpost']))
		{
			$with_threads = (isset($_POST['lastpost_nothread'])) ? FALSE : TRUE;
			$clanfor->update_lastpost(CLAN_FORUM_TABLE_NOPREFIX, $fid, $with_threads);
			$msg .= FORLAN_168.": $fid <br />";
		}
	}
	$clanfor->update_subparent_lp('all');

	if(isset($_POST['userpostcounts']))
	{
		$list = $clanfor->get_user_counts();
		foreach($list as $uid => $cnt)
		{
			e107::getDb()->update("user","user_forums = '{$cnt}' WHERE user_id = '{$uid}'");
		}
		$msg .= FORLAN_169." <br />";
	}

	$clanforum->show_message($msg);
}

if(isset($_POST['create_sub']))
{
	$fid = intval($sub_action);
	$_name  = $tp->toDB($_POST['subname_new']);
	$_desc  = $tp->toDB($_POST['subdesc_new']);
	$_order = intval($_POST['suborder_new']);
	if($_name != "" && $row = e107::getDb()->retrieve(CLAN_FORUM_TABLE_NOPREFIX, '*', "forum_id = {$fid}"))
	{ 
		if(e107::getDb()->insert(CLAN_FORUM_TABLE_NOPREFIX, "0, '{$_name}', '{$_desc}', '{$row['forum_parent']}', '{$fid}', '".time()."', '{$row['forum_moderators']}', 0, 0, '', '', '{$row['forum_class']}', '{$_order}', '{$row['forum_postclass']}'"))
		{
			$clanforum->show_message(LAN_CREATED);
		}
		else
		{
			$clanforum->show_message(LAN_CREATED_FAILED);
		}
	}
}

if(isset($_POST['update_subs']))
{
	$msg = "";
	foreach(array_keys($_POST['subname']) as $id)
	{
		if($_POST['subname'][$id] == "")
		{
			if (e107::getDb()->delete(CLAN_FORUM_TABLE_NOPREFIX, "forum_id='$id' "))
			{
				$msg .= FORLAN_150." ".$id." ".LAN_DELETED."<br />";
				$cnt = e107::getDb()->delete(CLAN_FORUM_THREAD_NOPREFIX, "thread_forum_id = {$id}");
				$msg .= $cnt." ".FORLAN_152." ".LAN_DELETED."<br />";
			}
		}
		else
		{
			$_name  = $tp->toDB($_POST['subname'][$id]);
			$_desc  = $tp->toDB($_POST['subdesc'][$id]);
			$_order = intval($_POST['suborder'][$id]);
			if(e107::getDb()->update(CLAN_FORUM_TABLE_NOPREFIX, "forum_name='{$_name}', forum_description='{$_desc}', forum_order='{$_order}' WHERE forum_id = {$id}"))
			{
				$msg .= FORLAN_150." ".$id." ".LAN_UPDATED."<br />";
			}
		}
	}
	if($msg)
	{
		$clanforum->show_message($msg);
	}
}

if(isset($_POST['submit_parent']))
{
	$_POST['forum_name'] = $tp->toDB($_POST['forum_name']);
	e107::getDb()->insert(CLAN_FORUM_TABLE_NOPREFIX, "0, '".$_POST['forum_name']."', '', '0', '0', '".time()."', '', '0', '0', '', '', '".$_POST['forum_class']."', '0', '{$_POST['forum_postclass']}'");
	$clanforum->show_message(FORLAN_13);
}

if(isset($_POST['update_parent']))
{
	$_POST['forum_name'] = $tp->toDB($_POST['forum_name']);
	e107::getDb()->update(CLAN_FORUM_TABLE_NOPREFIX, "forum_name='".$_POST['forum_name']."', forum_class='".$_POST['forum_class']."', forum_postclass='{$_POST['forum_postclass']}'  WHERE forum_id=$id");
	$clanforum->show_message(FORLAN_14);
	$action = "main";
}

if(isset($_POST['submit_forum']))
{
	$mods = $_POST['forum_moderators'];
	$_POST['forum_name'] = $tp->toDB($_POST['forum_name']);
	$_POST['forum_description'] = $tp->toDB($_POST['forum_description']);
	e107::getDb()->insert(CLAN_FORUM_TABLE_NOPREFIX, "0, '".$_POST['forum_name']."', '".$_POST['forum_description']."', '".$_POST['forum_parent']."', '0', '".time()."', '".$mods."', 0, 0, '', '', '".$_POST['forum_class']."', 0, '{$_POST['forum_postclass']}'");
	$clanforum->show_message(FORLAN_11);
}

if(isset($_POST['update_forum']))
{
	$mods = $_POST['forum_moderators'];
	$_POST['forum_name'] = $tp->toDB($_POST['forum_name']);
	$_POST['forum_description'] = $tp->toDB($_POST['forum_description']);
	$forum_parent = $row['forum_id'];
	e107::getDb()->update(CLAN_FORUM_TABLE_NOPREFIX, "forum_name='".$_POST['forum_name']."', forum_description='".$_POST['forum_description']."', forum_parent='".$_POST['forum_parent']."', forum_moderators='".$mods."', forum_class='".$_POST['forum_class']."', forum_postclass='{$_POST['forum_postclass']}' WHERE forum_id=$id");
	e107::getDb()->update(CLAN_FORUM_TABLE_NOPREFIX, "forum_moderators='".$mods."', forum_class='".$_POST['forum_class']."', forum_postclass='{$_POST['forum_postclass']}' WHERE forum_sub=$id");
	$clanforum->show_message(FORLAN_12);
	$action = "main";
}

if (isset($_POST['update_order']))
{
	extract($_POST);
	while (list($key, $id) = each($forum_order))
	{
		$tmp = explode(".", $id);
		e107::getDb()->update(CLAN_FORUM_TABLE_NOPREFIX, "forum_order=".intval($tmp[1])." WHERE forum_id=".intval($tmp[0]));
	}
	$clanforum->show_message(FORLAN_73);
}

if (isset($_POST['updateoptions']))
{
	
  $plugPref['email_notify'] = $_POST['email_notify'];
	$plugPref['email_notify_on'] = $_POST['email_notify_on'];
	$plugPref['forum_poll'] = $_POST['forum_poll'];
	$plugPref['forum_popular'] = $_POST['forum_popular'];
	$plugPref['forum_track'] = $_POST['forum_track'];
	$plugPref['forum_eprefix'] = $_POST['forum_eprefix'];
	$plugPref['forum_enclose'] = $_POST['forum_enclose'];
	$plugPref['forum_title'] = $_POST['forum_title'];
	$plugPref['forum_postspage'] = $_POST['forum_postspage'];
	$plugPref['html_post'] = $_POST['html_post'];
	$plugPref['forum_attach'] = $_POST['forum_attach'];
	$plugPref['forum_redirect'] = $_POST['forum_redirect'];
	$plugPref['forum_user_customtitle'] = $_POST['forum_user_customtitle'];
	$plugPref['reported_post_email'] = $_POST['reported_post_email'];
	$plugPref['forum_tooltip'] = $_POST['forum_tooltip'];
	$plugPref['forum_tiplength'] = $_POST['forum_tiplength'];
	$plugPref['forum_hilightsticky'] = $_POST['forum_hilightsticky'];
	$plugPref['forum_maxwidth'] = $_POST['forum_maxwidth'];
	$plugPref['forum_linkimg'] = $_POST['forum_linkimg'];
	$plugPref['forum_posts_sig'] = $_POST['forum_posts_sig'];
	$plugPref['forum_class_sig'] = $_POST['forum_class_sig'];
	e107::getPlugConfig(CLAN_FORUM_FOLDER)->setPref($plugPref)->save();
	$clanforum->show_message(FORLAN_10);
}

if (isset($_POST['do_prune']))
{
	$msg = $clanfor->forum_prune($_POST['prune_type'], $_POST['prune_days'], $_POST['pruneForum']);
	$clanforum->show_message($msg);
	$action = "main";
}

if (isset($_POST['set_ranks']))
{
	extract($_POST);
	for($a = 0; $a <= 9; $a++)
	{
		$r_names .= $tp->toDB($rank_names[$a]).",";
		$r_thresholds .= $tp->toDB($rank_thresholds[$a]).",";
		$r_images .= $tp->toDB($rank_images[$a]).",";
	}
	$plugPref['rank_main_admin'] = $_POST['rank_main_admin'];
	$plugPref['rank_main_admin_image'] = $_POST['rank_main_admin_image'];
	$plugPref['rank_admin'] = $_POST['rank_admin'];
	$plugPref['rank_admin_image'] = $_POST['rank_admin_image'];
	$plugPref['rank_moderator'] = $_POST['rank_moderator'];
	$plugPref['rank_moderator_image'] = $_POST['rank_moderator_image'];
	$plugPref['forum_levels'] = $r_names;
	$plugPref['forum_thresholds'] = $r_thresholds;
	$plugPref['forum_images'] = $r_images;
	e107::getPlugConfig(CLAN_FORUM_FOLDER)->setPref($plugPref)->save();
	$clanforum->show_message(FORLAN_95);
}

if (isset($_POST['frsubmit']))
{
	$guestrules = $tp->toDB($_POST['guestrules']);
	$memberrules = $tp->toDB($_POST['memberrules']);
	$adminrules = $tp->toDB($_POST['adminrules']);
	if(!e107::getDb()->update("generic", "gen_chardata ='$guestrules', gen_intdata='".$_POST['guest_active']."' WHERE gen_type='forum_rules_guest' "))
	{
		e107::getDb() -> insert("generic", "0, 'forum_rules_guest', '".time()."', 0, '', '".$_POST['guest_active']."', '$guestrules' ");
	}
	if(!e107::getDb()->update("generic", "gen_chardata ='$memberrules', gen_intdata='".$_POST['member_active']."' WHERE gen_type='forum_rules_member' "))
	{
		e107::getDb()-> insert("generic", "0, 'forum_rules_member', '".time()."', 0, '', '".$_POST['member_active']."', '$memberrules' ");
	}
	if(!e107::getDb()->update("generic", "gen_chardata ='$adminrules', gen_intdata='".$_POST['admin_active']."' WHERE gen_type='forum_rules_admin' "))
	{
		e107::getDb() -> insert("generic", "0, 'forum_rules_admin', '".time()."', 0, '', '".$_POST['admin_active']."', '$adminrules' ");
	}
}


if ($delete == 'main') {
	if (e107::getDb()->delete(CLAN_FORUM_TABLE_NOPREFIX, "forum_id='$del_id' ")) {
		$clanforum->show_message(FORLAN_96);
	}
}

if ($action == "create")
{
	if (e107::getDb()->select(CLAN_FORUM_TABLE_NOPREFIX, "*", "forum_parent='0' "))
	{
		$clanforum->create_forums($sub_action, $id);
	}
	else
	{
		header("location:".e_ADMIN."forum.php");
		exit;
	}
}

if ($delete == 'cat')
{
	if (e107::getDb()->delete(CLAN_FORUM_TABLE_NOPREFIX, "forum_id='$del_id' "))
	{
		e107::getDb()->delete(CLAN_FORUM_TABLE_NOPREFIX, "forum_parent='$del_id' ");
		$clanforum->show_message(FORLAN_97);
		$action = "main";
	}
}

if($action == "delete")
{
	$clanforum->delete_item(intval($sub_action));
}

if ($action == "cat") {
	$clanforum->create_parents($sub_action, $id);
}

if ($action == "order") {
	$clanforum->show_existing_forums($sub_action, $id, TRUE);
}

if ($action == "opt")
{
	$clanforum->show_prefs();
}

if ($action == "mods")
{
	$clanforum->show_mods();
}

if ($action == "tools")
{
	$clanforum->show_tools();
}

if ($action == "prune")
{
	$clanforum->show_prune();
}

if ($action == "rank")
{
	$clanforum->show_levels();
}

if ($action == "rules")
{
	$clanforum->show_rules();
}

if($action == 'subs')
{
	$clanforum->show_subs($sub_action);
}

if ($delete == 'reported')
{
	e107::getDb()->delete("generic", "gen_id='$del_id' ");
	$clanforum->show_message(FORLAN_118);
}


if ($action == "sr")
{
	$clanforum->show_reported($sub_action);
}

if (!e_QUERY || $action == "main")
{
	$clanforum->show_existing_forums($sub_action, $id);
}

//$clanforum->show_options($action);
require_once(e_ADMIN."footer.php");
function headerjs()
{
	global $tp;
	// These functions need to be removed and replaced with the generic jsconfirm() function.
	$headerjs = "<script type=\"text/javascript\">
	function confirm_(mode, forum_id, forum_name) {
		if (mode == 'sr') {
			return confirm(\"".$tp->toJS(FORLAN_117)."\");
		} else if(mode == 'parent') {
			return confirm(\"".$tp->toJS(FORLAN_81)." [ID: \" + forum_name + \"]\");
		} else {
			return confirm(\"".$tp->toJS(FORLAN_82)." [ID: \" + forum_name + \"]\");
		}
	}
	</script>";
	return $headerjs;
}

class clanforum
{

	function show_options($action)
	{
		global $sql;
		if ($action == "")
		{
			$action = "main";
		}
		// ##### Display options ---------------------------------------------------------------------------------------------------------
		$var['main']['text'] = FORLAN_76;
		$var['main']['link'] = e_SELF;
		$var['cat']['text'] = FORLAN_83;
		$var['cat']['link'] = e_SELF."?cat";
		if (e107::getDb()->select(CLAN_FORUM_TABLE_NOPREFIX, "*", "forum_parent='0' "))
		{
			$var['create']['text'] = FORLAN_77;
			$var['create']['link'] = e_SELF."?create";
		}
		$var['order']['text'] = FORLAN_78;
		$var['order']['link'] = e_SELF."?order";
		$var['opt']['text'] = FORLAN_79;
		$var['opt']['link'] = e_SELF."?opt";
		$var['prune']['text'] = FORLAN_59;
		$var['prune']['link'] = e_SELF."?prune";
		$var['rank']['text'] = FORLAN_63;
		$var['rank']['link'] = e_SELF."?rank";
		$var['rules']['text'] = FORLAN_123;
		$var['rules']['link'] = e_SELF."?rules";
		$var['sr']['text'] = FORLAN_116;
		$var['sr']['link'] = e_SELF."?sr";
		$var['mods']['text'] = FORLAN_33;
		$var['mods']['link'] = e_SELF."?mods";
		$var['tools']['text'] = FORLAN_153;
		$var['tools']['link'] = e_SELF."?tools";

		show_admin_menu(FORLAN_7, $action, $var);
	}

	function delete_item($id)
	{
		global $sql;
		$id = intval($id);
		$confirm = isset($_POST['confirm']) ? TRUE : FALSE;

		if(e107::getDb()->select(CLAN_FORUM_TABLE_NOPREFIX, '*', "forum_id = {$id}"))
		{
			$txt = "";
			$row = e107::getDb()->fetch();
			if($row['forum_parent'] == 0)
			{
				$txt .= $this->delete_parent($id, $confirm);
			}
			elseif($row['forum_sub'] > 0)
			{
				$txt .= $this->delete_sub($id, $confirm);
			}
			else
			{
				$txt .= $this->delete_forum($id, $confirm);
			}
			if($confirm)
			{
				$this->show_message($txt);
			}
			else
			{
				$this->delete_show_confirm($txt);
			}
		}
	}

	function delete_parent($id, $confirm = FALSE)
	{
		//$sql = e107::getDb();
		$ret = "";
		if($fList = e107::getDb()->retrieve(CLAN_FORUM_TABLE_NOPREFIX, "forum_id", "forum_parent = {$id} AND forum_sub = 0", true))
		{
			//$fList = $sql->db_getList();
			foreach($fList as $f)
			{
				$ret .= $this->delete_forum($f['forum_id'], $confirm);
			}
		}
		if($confirm)
		{
			if(e107::getDb()->delete(CLAN_FORUM_TABLE_NOPREFIX, "forum_id = {$id}"))
			{
				$ret .= "Forum parent successfully deleted";
			}
			else
			{
				$ret .= "Forum parent could not be deleted";
			}
			return $ret;
		}
		return "The forum parent has the following info: <br />".$ret;

	}

	function delete_forum($id, $confirm = FALSE)
	{
		global $sql, $tp;
		$ret = "";
		if($fList = e107::getDb()->retrieve(CLAN_FORUM_TABLE_NOPREFIX, "forum_id", "forum_sub = {$id}", true))
		{
			//$fList = $sql->db_getList();
			foreach($fList as $f)
			{
				$ret .= $this->delete_sub($f['forum_id'], $confirm);
			}
		}
		if($confirm)
		{
			$cnt = e107::getDb()->delete(CLAN_FORUM_THREAD_NOPREFIX,"thread_forum_id = {$id}");
			$ret .= $cnt." forum {$id} thread(s) deleted <br />";
			if(e107::getDb()->delete(CLAN_FORUM_TABLE_NOPREFIX, "forum_id = {$id}"))
			{
				$ret .= "Forum {$id} successfully deleted";
			}
			else
			{
				$ret .= "Forum {$id} could not be deleted";
			}
			return $ret;
		}

		e107::getDb()->select(CLAN_FORUM_TABLE_NOPREFIX, "*", "forum_id = {$id}");
		$row = e107::getDb()->fetch();
		return "Forum {$id} [".$tp->toHTML($row['forum_name'])."] has {$row['forum_threads']} threads, {$row['forum_replies']} replies. <br />".$ret;
	}

	function delete_sub($id, $confirm = FALSE)
	{
		global $sql, $tp;
		if($confirm)
		{
			$cnt = e107::getDb()->delete(CLAN_FORUM_THREAD_NOPREFIX,"thread_forum_id = {$id}");
			$ret .= $cnt." Sub-forum {$id} thread(s) deleted <br />";
			if(e107::getDb()->delete(CLAN_FORUM_TABLE_NOPREFIX, "forum_id = {$id}"))
			{
				$ret .= "Sub-forum {$id} successfully deleted";
			}
			else
			{
				$ret .= "Sub-forum {$id} could not be deleted";
			}
			return $ret;
		}

		$row =  e107::getDb()->retrieve(CLAN_FORUM_TABLE_NOPREFIX, "*", "forum_id = {$id}", true);
		//$row = $sql->fetch();
		return "Sub-forum {$id} [".$tp->toHTML($row['forum_name'])."] has {$row['forum_threads']} threads, {$row['forum_replies']} replies. <br />".$ret;
	}

	function delete_show_confirm($txt)
	{
		global $ns;
		$this->show_message($txt);
		$txt = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<div style='text-align:center'>".FORLAN_180."<br /><br />
		<input type='submit' class='button' name='confirm' value='".FORLAN_181."' />
		<input type='hidden' name='e-token' value='".e_TOKEN."' />
		</div>
		</form>
		";
		$ns->tablerender(FORLAN_181, $txt);
	}

	function show_subs($id)
	{
		global $sql, $tp, $ns;
		$txt = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table style='width:100%' id='show_subs'>
		<tr>
		<td class='fcaption'>".FORLAN_151."</td>
		<td class='fcaption'>".FORLAN_31."</td>
		<td class='fcaption'>".FORLAN_32."</td>
		<td class='fcaption'>".FORLAN_37."</td>
		<td class='fcaption'>".FORLAN_20."</td>
		</tr>
		";
 
		if($subList = e107::getDb()->retrieve(CLAN_FORUM_TABLE_NOPREFIX, 'forum_id, forum_name, forum_description, forum_order', "forum_sub = {$id} ORDER by forum_order ASC", true))
		{
		  
			//$subList = $sql->db_getList();
			foreach($subList as $sub)
			{
				$txt .= "
				<tr>
				<td class='forumheader2' style='vertical-align:top'>{$sub['forum_id']}</td>
				<td class='forumheader2' style='vertical-align:top'><input class='tbox' type='text' name='subname[{$sub['forum_id']}]' value='{$sub['forum_name']}' size='30' maxlength='255' /></td>
				<td class='forumheader2' style='vertical-align:top'><textarea cols='60' rows='2' class='tbox' name='subdesc[{$sub['forum_id']}]'>{$sub['forum_description']}</textarea></td>
				<td class='forumheader2' style='vertical-align:top'><input class='tbox' type='text' name='suborder[{$sub['forum_id']}]' value='{$sub['forum_order']}' size='3' maxlength='4' /></td>
				<td class='forumheader2' style='vertical-align:top; text-align:center'>
				<a href='".e_SELF."?delete.{$sub['forum_id']}'>".ADMIN_DELETE_ICON."</a>
				</td>
				</tr>
				";
			}
			$txt .= "
			<tr>
			<td class='forumheader3' colspan='5' style='text-align:center'><input type='submit' class='button' name='update_subs' value='".FORLAN_147."' /></td>
			</tr>
			<tr>
			<td colspan='5' style='text-align:center'>&nbsp;</td>
			</tr>
			";

		}
		else
		{
			$txt .= "<tr><td colspan='5' class='forumheader3' style='text-align:center'>".FORLAN_146."</td>";
		}
		// e-token hidden added - protects both create and update subs
		$txt .= "
		<tr>
		<td class='fcaption'>".FORLAN_151."</td>
		<td class='fcaption'>".FORLAN_31."</td>
		<td class='fcaption'>".FORLAN_32."</td>
		<td class='fcaption'>".FORLAN_37."</td>
		<td class='fcaption'>&nbsp;</td>
		</tr>
		<tr>
		<td class='forumheader2' style='vertical-align:top'>&nbsp;</td>
		<td class='forumheader2'><input class='tbox' type='text' name='subname_new' value='' size='30' maxlength='255' /></td>
		<td class='forumheader2'><textarea cols='60' rows='2' class='tbox' name='subdesc_new'></textarea></td>
		<td class='forumheader2'><input class='tbox' type='text' name='suborder_new' value='' size='3' maxlength='4' /></td>
		<td class='forumheader2'>&nbsp;</td>
		</tr>
		<tr>
		<td class='forumheader3' colspan='5' style='text-align:center'><input type='submit' class='button' name='create_sub' value='".FORLAN_148."' />
		<input type='hidden' name='e-token' value='".e_TOKEN."' /></td>
		</tr>
		</table>
		</form>
		";
		$ns->tablerender(FORLAN_149, $txt);
	}

	function show_existing_forums($sub_action, $id, $mode = FALSE)
	{
		global $rs, $ns,  $tp, $clanfor;

    $sql = e107::getDb();
 
    // get subforum list  not tested
		$subList = $clanfor->forum_getsubs();
 
		if (!$mode)
		{
			$text = "<div style='padding : 1px; ".ADMIN_WIDTH."; margin-left: auto; margin-right: auto; text-align: center;'>";
		} else {
			$text = "<form method='post' action='".e_SELF."?".e_QUERY."'>";
		}
		$text .= "
		<table style='".ADMIN_WIDTH."' class='fborder' id='show_existing_forums'>
		<tr>
		<td colspan='2' style='width:70%; text-align:center' class='fcaption'>".FORLAN_28."</td>
		<td style='width:30%; text-align:center' class='fcaption'>".FORLAN_80."</td>
		</tr>";

		if (!$parent_amount = $sql->retrieve(CLAN_FORUM_TABLE_NOPREFIX, "*", " WHERE forum_parent='0' ORDER BY forum_order ASC",  true))
		{
			$text .= "<tr><td class='forumheader3' style='text-align:center' colspan='3'>".FORLAN_29."</td></tr>";
		}
		else
		{ 
		  foreach($parent_amount AS $row)
			{
				extract($row);
				$parent_id = $forum_id;
				$text .= "
				<tr>
				<td colspan='2' class='forumheader'>".$forum_name."
				<br /><b>".FORLAN_140.":</b> ".r_userclass_name($forum_class)."&nbsp;&nbsp;<b>".FORLAN_141.":</b> ".r_userclass_name($forum_postclass)."
				</td>";

				$text .= "<td class='forumheader' style='text-align:center'>";

				if ($mode)
				{
					$text .= "<select name='forum_order[]' class='tbox'>\n";
					for($a = 1; $a <= $parent_amount; $a++)
					{
						$text .= ($forum_order == $a ? "<option value='$forum_id.$a' selected='selected'>$a</option>\n" : "<option value='$forum_id.$a'>$a</option>\n");
					}
					$text .= "</select>";
				}
				else
				{
					$forum_heading = str_replace("&#39;", "\'", $forum_name);
					$text .= "
					<div style='text-align:left; padding-left: 30px'>
					<a href='".e_SELF."?cat.edit.{$forum_id}'>".ADMIN_EDIT_ICON."</a>
					<a href='".e_SELF."?delete.{$forum_id}'>".ADMIN_DELETE_ICON."</a>
					</div>
					";
				}
				$text .= "</td></tr>";

				$forums = $sql->retrieve(CLAN_FORUM_TABLE_NOPREFIX, "*", "forum_parent='".$forum_id."' AND forum_sub = 0 ORDER BY forum_order ASC", true);
				if (!$forums)
				{
					$text .= "<td colspan='4' style='text-align:center' class='forumheader3'>".FORLAN_29."</td>";
				}
				else
				{
					foreach($forums as $row)
					{
						extract($row);

						$text .= "
						<tr>
						<td style='width:5%; text-align:center' class='forumheader3'>".IMAGE_new."</td>\n<td style='width:55%' class='forumheader3'><a href='".CLAN_FORUM_APP."forum_viewforum.php?".$forum_id."'>".$forum_name."</a>" ;

						$text .= "
						<br /><span class='smallblacktext'>".$forum_description."&nbsp;</span>
						<br /><b>".FORLAN_140.":</b> ".r_userclass_name($forum_class)."&nbsp;&nbsp;<b>".FORLAN_141.":</b> ".r_userclass_name($forum_postclass)."

						</td>

						<td colspan='2' class='forumheader3' style='text-align:center'>";

						if ($mode)
						{
							$text .= "<select name='forum_order[]' class='tbox'>\n";
							for($a = 1; $a <= $forums; $a++)
							{
								$text .= ($forum_order == $a ? "<option value='$forum_id.$a' selected='selected'>$a</option>\n" : "<option value='$forum_id.$a'>$a</option>\n");
							}
							$text .= "</select>";
						}
						else
						{
							$forum_heading = str_replace("&#39;", "\'", $forum_name);
							$sub_img = count($subList[$parent_id][$forum_id]) ? IMAGE_sub : IMAGE_nosub;
							$text .= "
							<div style='text-align:left; padding-left: 30px'>
							<a href='".e_SELF."?create.edit.{$forum_id}'>".ADMIN_EDIT_ICON."</a>
							<a href='".e_SELF."?delete.{$forum_id}'>".ADMIN_DELETE_ICON."</a>
							&nbsp;&nbsp;<a href='".e_SELF."?subs.{$forum_id}'>".$sub_img."</a>
							</div>
							";
						}
						$text .= "</td>\n</tr>";
					}
				}
			}
		}

		if (!$mode)
		{
			$text .= "</table></div>";
			$ns->tablerender(FORLAN_30, $text);
		}
		else
		{
			$text .= "<tr>\n<td colspan='4' style='text-align:center' class='forumheader'>\n<input type='hidden' name='e-token' value='".e_TOKEN."' />\n<input class='button' type='submit' name='update_order' value='".FORLAN_72."' />\n</td>\n</tr>\n</table>\n</form>";
			$ns->tablerender(FORLAN_37, $text);
		}

	}

	function create_parents($sub_action, $id)
	{
		global $sql, $ns;

		if ($sub_action == "edit" && !$_POST['update_parent'])
		{
			if ($sql->select(CLAN_FORUM_TABLE_NOPREFIX, "*", "forum_id=$id"))
			{
				$row = $sql->fetch();
				extract($row);
			}
		}
       
    $forum_postclass = varset($forum_postclass, '253');
 
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='".ADMIN_WIDTH."' class='fborder' id='create_parents'>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_31.":</td>
		<td style='width:60%' class='forumheader3'>
		<input class='tbox' type='text' name='forum_name' size='60' value='$forum_name' maxlength='250' />
		</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_23.":<br /><span class='smalltext'>(".FORLAN_24.")</span></td>
		<td style='width:60%' class='forumheader3'>".r_userclass("forum_class", $forum_class, 'off', 'nobody,public,member,admin,classes')."</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_142.":<br /><span class='smalltext'>(".FORLAN_143.")</span></td>
		<td style='width:60%' class='forumheader3'>".r_userclass("forum_postclass", $forum_postclass, 'off', 'nobody,public,member,admin,classes')."</td>
		</tr>

		<tr style='vertical-align:top'>
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input type='hidden' name='e-token' value='".e_TOKEN."' />";

		if ($sub_action == "edit")
		{
			$text .= "<input class='button' type='submit' name='update_parent' value='".FORLAN_25."' />";
		}
		else
		{
			$text .= "<input class='button' type='submit' name='submit_parent' value='".FORLAN_26."' />";
		}

		$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns->tablerender(FORLAN_75, $text);
	}

	function create_forums($sub_action, $id)
	{
		global $sql, $ns;

		if ($sub_action == "edit" && !$_POST['update_forum'])
		{
			if ($row = e107::getDb()->retrieve(CLAN_FORUM_TABLE_NOPREFIX, "*", "forum_id=$id" ))
			{
				//$row = $sql->dbx_Fetch();
				extract($row);
			}
		}
    $forum_postclass = varset($forum_postclass, '253');
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='".ADMIN_WIDTH."' class='fborder' id='create_forums'>
		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_22.":</td>
		<td style='width:60%' class='forumheader3'>";

		$rows = e107::getDb()->retrieve(CLAN_FORUM_TABLE_NOPREFIX, "*", "forum_parent=0", true);
		$text .= "<select name='forum_parent' class='tbox'>\n";
		foreach($rows as $row) {
			$forum_id_  = $row['forum_id'];
			$forum_name_  = $row['forum_name'];
			if ($forum_id_ == $forum_parent)
			{
				$text .= "<option value='$forum_id_' selected='selected'>".$forum_name_."</option>\n";
			}
			else
			{
				$text .= "<option value='$forum_id_'>".$forum_name_."</option>\n";
			}
		}
		$text .= "</select>
		</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_31.":
		<div class='smalltext'>".FORLAN_179."</div>
		</td>
		<td style='width:60%' class='forumheader3'>
		<input class='tbox' type='text' name='forum_name' size='60' value='$forum_name' maxlength='250' />
		</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_32.": </td>
		<td style='width:60%' class='forumheader3'>
		<textarea class='tbox' name='forum_description' cols='50' rows='5'>$forum_description</textarea>
		</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_33.":<br /><span class='smalltext'>(".FORLAN_34.")</span></td>
		<td style='width:60%' class='forumheader3'>";
		$text .= r_userclass("forum_moderators", $forum_moderators, 'off', 'admin,main,classes');

		//		$admin_no = $sql->db_Select("user", "*", "user_admin='1' AND user_perms REGEXP('A.') OR user_perms='0' ");
		//		while ($row = $sql->dbx_Fetch())
		//		{
		//			extract($row);
		//			$text .= "<input type='checkbox' name='mod[]' value='".$user_name ."'";
		//			if (preg_match('/'.preg_quote($user_name).'/', $forum_moderators))
		//			{
		//				$text .= " checked";
		//			}
		//			$text .= "/> ".$user_name ."<br />";
		//		}

		$text .= "</td>
		</tr>
		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_23.":<br /><span class='smalltext'>(".FORLAN_24.")</span></td>
		<td style='width:60%' class='forumheader3'>".r_userclass("forum_class", $forum_class, 'off', 'nobody,public,member,admin,main,classes')."</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_142.":<br /><span class='smalltext'>(".FORLAN_143.")</span></td>
		<td style='width:60%' class='forumheader3'>".r_userclass("forum_postclass", $forum_postclass, 'off', 'nobody,public,member,admin,main,classes')."</td>
		</tr>

		<tr style='vertical-align:top'>
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input type='hidden' name='e-token' value='".e_TOKEN."' />";
		if ($sub_action == "edit")
		{
			$text .= "<input class='button' type='submit' name='update_forum' value='".FORLAN_35."' />";
		}
		else
		{
			$text .= "<input class='button' type='submit' name='submit_forum' value='".FORLAN_36."' />";
		}
		$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";
		$ns->tablerender(FORLAN_28, $text);
	}

	function show_message($message)
	{
		global $ns;
		$ns->tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function show_tools()
	{
		global $sql, $ns, $tp;
		$txt = "
		<form method='post' action='".e_SELF."?".e_QUERY."'>
		<table style='width:".ADMIN_WIDTH."' id='show_tools'>
		<tr style='width:100%'>
		<td class='fcaption'>".FORLAN_156."</td>
		</tr>
		<tr>
		<td class='forumheader3'>
		<input type='hidden' name='e-token' value='".e_TOKEN."' />
		";
		if($sql->db_Select(CLAN_FORUM_TABLE_NOPREFIX, "*", "1 ORDER BY forum_order"))
		{
			$fList = $sql->db_getList();
			foreach($fList as $f)
			{
				$txt .= "<input type='checkbox' name='forumlist[{$f['forum_id']}]' value='1' /> ".$tp->toHTML($f['forum_name'])."<br />";
			}
			$txt .= "<input type='checkbox' name='forum_all' value='1' /> <strong>".FORLAN_157."</strong>";
		}
		$txt .= "
		</td>
		</tr>
		<tr>
		<td class='fcaption'>".FORLAN_158."</td>
		</tr>
		<tr>
		<td class='forumheader3'>
		<input type='checkbox' name='lastpost' value='1' /> ".FORLAN_159." <br />&nbsp;&nbsp;&nbsp;&nbsp;
		<input type='checkbox' name='lastpost_nothread' value='1' checked='checked' /> ".FORLAN_160."
		</td>
		</tr>
		<tr>
		<td class='fcaption'>".FORLAN_161."</td>
		</tr>
		<tr>
		<td class='forumheader3'>
			<input type='checkbox' name='counts' value='1' /> ".FORLAN_162."<br />
			&nbsp;&nbsp;&nbsp;&nbsp;<input type='checkbox' name='counts_threads' value='1' /><span style='text-align: center'> ".FORLAN_182."<br />".FORLAN_183."</span><br />
		</td>
		</tr>
		<tr>
		<td class='fcaption'>".FORLAN_163."</td>
		</tr>
		<tr>
		<td class='forumheader3'>
		<input type='checkbox' name='userpostcounts' value='1' /> ".FORLAN_164."<br />
		</td>
		</tr>
		<tr>
		<td class='forumheader3' style='text-align:center'>
		<input class='button' type='submit' name='tools' value='".FORLAN_165."' />
		</td>
		</tr>
		</table>
		</form>
		";
		$ns->tablerender(FORLAN_166, $txt);
	}

	function show_prefs()
	{
		global  $ns, $sql;
    $plugPref =  e107::pref(CLAN_FORUM_FOLDER);
    
	//	if($sql->db_Count('plugin','(*)', "where plugin_path = 'poll' AND plugin_installflag = 1"))
    if(e107::isInstalled('poll'))
		{
			$poll_installed = true;    
		}
		else
		{
			$poll_installed = false;
			if($plugPref['forum_poll'] == 1)
			{
				$plugPref['forum_poll'] = 0;
        e107::getPlugConfig(CLAN_FORUM_FOLDER)->setPref($plugPref)->save();
			}
		}

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='".ADMIN_WIDTH."' class='fborder id='show_prefs'>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_44."<br /><span class='smalltext'>".FORLAN_45."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['forum_enclose'] ? "<input type='checkbox' name='forum_enclose' value='1' checked='checked' />" : "<input type='checkbox' name='forum_enclose' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_65."<br /><span class='smalltext'>".FORLAN_46."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_title' size='15' value='".$plugPref['forum_title']."' maxlength='100' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_47."<br /><span class='smalltext'>".FORLAN_48."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['email_notify'] ? "<input type='checkbox' name='email_notify' value='1' checked='checked' />" : "<input type='checkbox' name='email_notify' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_177."<br /><span class='smalltext'>".FORLAN_178."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['email_notify_on'] ? "<input type='checkbox' name='email_notify_on' value='1' checked='checked' />" : "<input type='checkbox' name='email_notify_on' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_49."<br /><span class='smalltext'>".FORLAN_50."</span></td>";
		if($poll_installed)
		{
			$text .= "<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['forum_poll'] ? "<input type='checkbox' name='forum_poll' value='1' checked='checked' />" : "<input type='checkbox' name='forum_poll' value='1' />")."</td>";
		}
		else
		{
			$text .= "<td style='width:25%;text-align:center' class='forumheader3' >".FORLAN_66."</td>";
		}
		$text .= "
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_70."<br /><span class='smalltext'>".FORLAN_71." <a href='".e_ADMIN."upload.php'>".FORLAN_130."</a> ". FORLAN_131."</span>";

		if(!$plugPref['image_post'])
		{
			$text .= "<br /><b>".FORLAN_139."</b>";
		}

		$text .= "</td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['forum_attach'] ? "<input type='checkbox' name='forum_attach' value='1' checked='checked' />" : "<input type='checkbox' name='forum_attach' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_134."<br /><span class='smalltext'>".FORLAN_135."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' size='3' maxlength='5' name='forum_maxwidth' value='{$plugPref['forum_maxwidth']}' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_136."<br /><span class='smalltext'>".FORLAN_137."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['forum_linkimg'] ? "<input type='checkbox' name='forum_linkimg' value='1' checked='checked' />" : "<input type='checkbox' name='forum_linkimg' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_51."<br /><span class='smalltext'>".FORLAN_52."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['forum_track'] ? "<input type='checkbox' name='forum_track' value='1' checked='checked' />" : "<input type='checkbox' name='forum_track' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_112."<br /><span class='smalltext'>".FORLAN_113."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['forum_redirect'] ? "<input type='checkbox' name='forum_redirect' value='1' checked='checked' />" : "<input type='checkbox' name='forum_redirect' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_114."<br /><span class='smalltext'>".FORLAN_115."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['forum_user_customtitle'] ? "<input type='checkbox' name='forum_user_customtitle' value='1' checked='checked' />" : "<input type='checkbox' name='forum_user_customtitle' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_116."<br /><span class='smalltext'>".FORLAN_122."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['reported_post_email'] ? "<input type='checkbox' name='reported_post_email' value='1' checked='checked' />" : "<input type='checkbox' name='reported_post_email' value='1' />")."</td>
		</tr>


		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_126."<br /><span class='smalltext'>".FORLAN_127."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['forum_tooltip'] ? "<input type='checkbox' name='forum_tooltip' value='1' checked='checked' />" : "<input type='checkbox' name='forum_tooltip' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_128."<br /><span class='smalltext'>".FORLAN_129."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_tiplength' size='15' value='".$plugPref['forum_tiplength']."' maxlength='20' /></td>
		</tr>


		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_53."<br /><span class='smalltext'>".FORLAN_54."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_eprefix' size='15' value='".$plugPref['forum_eprefix']."' maxlength='20' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_55."<br /><span class='smalltext'>".FORLAN_56."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_popular' size='3' value='".$plugPref['forum_popular']."' maxlength='3' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_57."<br /><span class='smalltext'>".FORLAN_58."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_postspage' size='3' value='".$plugPref['forum_postspage']."' maxlength='3' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_132."<br /><span class='smalltext'>".FORLAN_133."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' >".($plugPref['forum_hilightsticky'] ? "<input type='checkbox' name='forum_hilightsticky' value='1' checked='checked' />" : "<input type='checkbox' name='forum_hilightsticky' value='1' />")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_67."<br /><span class='smalltext'>".FORLAN_68."</span></td>
		<td style='width:25%;text-align:center' class='forumheader3' ><input class='tbox' type='text' name='forum_posts_sig' size='3' value='".$plugPref['forum_posts_sig']."' maxlength='3' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_69."<br /></td>
		<td style='width:25%;text-align:center' class='forumheader3' >" . r_userclass("forum_class_sig", $plugPref['forum_class_sig'], 'off', 'nobody,member,admin,classes') . "</td>
		</tr>

		<tr>
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input type='hidden' name='e-token' value='".e_TOKEN."' />
		<input class='button' type='submit' name='updateoptions' value='".FORLAN_61."' />
		</td>
		</tr>
		</table>
		</form>
		</div>";
		$ns->tablerender(FORLAN_62, $text);
	}

	function show_reported ($sub_action, $id)
	{
		global  $rs, $ns, $tp;
    $sql = e107::getDb();
		if ($sub_action) {
			$sql -> select("generic", "*", "gen_id='".$sub_action."'");
			$row = $sql -> fetch();
			$sql -> select("user", "*", "user_id='". $row['gen_user_id']."'");
			$user = $sql -> fetch();
			$con = new convert;
			$text = "<div style='text-align: center'>
			<table class='fborder' style='".ADMIN_WIDTH."' id='show_reported' ><tr>
			<td style='width:40%' class='forumheader3'>
			".FORLAN_171.":
			</td>
			<td style='width:60%' class='forumheader3'>
			<a href='".CLAN_FORUM_APP."forum_viewtopic.php?".$row['gen_intdata'].".post' rel='external'>#".$row['gen_intdata']."</a>
			</td>
			</tr>
			<tr>
			<td style='width:40%' class='forumheader3'>
			".FORLAN_173.":
			</td>
			<td style='width:60%' class='forumheader3'>
			".$row['gen_ip']."
			</td>
			</tr>
			<tr>
			<td style='width:40%' class='forumheader3'>
			".FORLAN_174.":
			</td>
			<td style='width:60%' class='forumheader3'>
			<a href='".e_BASE."user.php?id.".$user['user_id']."'>".$user['user_name']."</a>
			</td>
			</tr>
			<tr>
			<td style='width:40%' class='forumheader3'>
			".FORLAN_175.":
			</td>
			<td style='width:60%' class='forumheader3'>
			".$con -> convert_date($row['gen_datestamp'], "long")."
			</td>
			</tr>
			<tr>
			<td style='width:40%' class='forumheader3'>
			".FORLAN_176.":
			</td>
			<td style='width:60%' class='forumheader3'>
			".$row['gen_chardata']."
			</td>
			</tr>
			<tr>
			<td style='text-align:center' class='forumheader' colspan='2'>
			".$rs->form_open("post", e_SELF."?sr", "", "", "", " onsubmit=\"return confirm_('sr',".$row['gen_datestamp'].")\"")."
			<input type='hidden' name='e-token' value='".e_TOKEN."' />
			".$rs->form_button("submit", "delete[reported_{$row['gen_id']}]", FORLAN_172)."
			".$rs->form_close()."
			</td>
			</tr>\n";
			$text .= "</table>";
			$text .= "</div>";
			$ns -> tablerender(FORLAN_116, $text);
		} else {
			$text = "<div style='text-align: center'>";
			if ($reported_total = $sql->select("generic", "*", "gen_type='reported_post' OR gen_type='Reported Forum Post'"))
			{
				$text .= "<table class='fborder' style='".ADMIN_WIDTH."' id='show_reported_02'>
				<tr>
				<td style='width:80%' class='fcaption'>".FORLAN_170."</td>
				<td style='width:20%; text-align:center' class='fcaption'>".FORLAN_80."</td>
				</tr>";
				while ($row = $sql->fetch())
				{
					$text .= "<tr>
					<td style='width:80%' class='forumheader3'><a href='".e_SELF."?sr.".$row['gen_id']."'>".FORLAN_171." #".$row['gen_intdata']."</a></td>
					<td style='width:20%; text-align:center; vertical-align:top; white-space: nowrap' class='forumheader3'>
					".$rs->form_open("post", e_SELF."?sr", "", "", "", " onsubmit=\"return confirm_('sr',".$row['gen_datestamp'].")\"")."
					<input type='hidden' name='e-token' value='".e_TOKEN."' />
					".$rs->form_button("submit", "delete[reported_{$row['gen_id']}]", FORLAN_172)."
					".$rs->form_close()."
					</td>
					</tr>\n";
				}
				$text .= "</table>";
			}
			else
			{
				$text .= "<div style='text-align:center'>".FORLAN_121."</div>";
			}
			$text .= "</div>";
			$ns->tablerender(FORLAN_116, $text);
		}
	}

	function show_prune()
	{
		global $ns, $sql;

		//		$sql -> db_Select(CLAN_FORUM_TABLE_NOPREFIX, "forum_id, forum_name", "forum_parent!=0 ORDER BY forum_order ASC");
		$qry = "
		SELECT f.forum_id, f.forum_name, sp.forum_name AS sub_parent, fp.forum_name AS forum_parent
		FROM ".CLAN_FORUM_TABLE." AS f
		LEFT JOIN ".CLAN_FORUM_TABLE." AS sp ON sp.forum_id = f.forum_sub
		LEFT JOIN ".CLAN_FORUM_TABLE." AS fp ON fp.forum_id = f.forum_parent
		WHERE f.forum_parent != 0
		ORDER BY f.forum_parent ASC, f.forum_sub, f.forum_order ASC
		";
		$sql -> gen($qry);
		$forums = $sql -> db_getList();

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='".ADMIN_WIDTH."' class='fborder' id='show_prune'>
		<tr>
		<td style='text-align:center' class='forumheader3'>".FORLAN_60."</td>
		</tr>
		<tr>

		<td style='text-align:center' class='forumheader3'>".FORLAN_87."
		<input class='tbox' type='text' name='prune_days' size='6' value='' maxlength='3' />
		</td>
		</tr>

		<tr>
		<td style='text-align:center' class='forumheader3'>".FORLAN_2."<br />
		".FORLAN_89." <input type='radio' name='prune_type' value='delete' />&nbsp;&nbsp;&nbsp;
		".FORLAN_90." <input type='radio' name='prune_type' value='make_inactive' checked='checked' />
		</td>
		</tr>

		<tr>
		<td class='forumheader3'>".FORLAN_138.": <br />";

		foreach($forums as $forum)
		{
			$for_name = $forum['forum_parent']." -> ";
			$for_name .= ($forum['sub_parent'] ? $forum['sub_parent']." -> " : "");
			$for_name .= $forum['forum_name'];
			$text .= "<input type='checkbox' name='pruneForum[]' value='".$forum['forum_id']."' /> ".$for_name."<br />";
		}


		$text .= "<tr>
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input type='hidden' name='e-token' value='".e_TOKEN."' />
		<input class='button' type='submit' name='do_prune' value='".FORLAN_5."' />
		</td>
		</tr>
		</table>
		</form>
		</div>";
		$ns->tablerender(FORLAN_59, $text);
	}

	function show_levels()
	{
		global $sql,  $ns, $rs;
    $plugPref =  e107::pref(CLAN_FORUM_FOLDER);

		$rank_names = explode(",", $plugPref['forum_levels']);
		$rank_thresholds = ($plugPref['forum_thresholds'] ? explode(",", $plugPref['forum_thresholds']) : array(20, 100, 250, 410, 580, 760, 950, 1150, 1370, 1600));
		$rank_images = ($plugPref['forum_images'] ? explode(",", $plugPref['forum_images']) : array("lev1.png", "lev2.png", "lev3.png", "lev4.png", "lev5.png", "lev6.png", "lev7.png", "lev8.png", "lev9.png", "lev10.png"));

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='".ADMIN_WIDTH."' class='fborder' id='show_levels'>

		<tr>
		<td class='fcaption' style='width:40%'>".FORLAN_98."</td>
		<td class='fcaption' style='width:20%'>".FORLAN_102."<br /></td>
		<td class='fcaption' style='width:40%'>".FORLAN_104."<br /></td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:40%'>&nbsp;</td>
		<td class='forumheader3' style='width:20%'><span class='smalltext'>".FORLAN_99."</span></td>
		<td class='forumheader3' style='width:40%'><span class='smalltext'>".FORLAN_100."</span></td>
		</tr>";

		$text .= "<tr>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_main_admin' size='30' value='".($plugPref['rank_main_admin'] ? $plugPref['rank_main_admin'] : FORLAN_101)."' maxlength='30' /></td>
		<td class='forumheader3' style='width:40%'>&nbsp;</td>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_main_admin_image' size='30' value='".($plugPref['rank_main_admin_image'] ? $plugPref['rank_main_admin_image'] : e_LANGUAGE."_main_admin.png")."' maxlength='30' /></td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_admin' size='30' value='".($plugPref['rank_admin'] ? $plugPref['rank_admin'] : FORLAN_103)."' maxlength='30' /></td>
		<td class='forumheader3' style='width:40%'>&nbsp;</td>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_admin_image' size='30' value='".($plugPref['rank_admin_image'] ? $plugPref['rank_admin_image'] : e_LANGUAGE."_admin.png")."' maxlength='30' /></td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_moderator' size='30' value='".($plugPref['rank_moderator'] ? $plugPref['rank_moderator'] : FORLAN_105)."' maxlength='30' /></td>
		<td class='forumheader3' style='width:40%'>&nbsp;</td>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_moderator_image' size='30' value='".($plugPref['rank_moderator_image'] ? $plugPref['rank_moderator_image'] : e_LANGUAGE."_moderator.png")."' maxlength='30' /></td>
		</tr>";

		for($a = 0; $a <= 9; $a++)
		{
			$low_val = ($a == 0 ? '0' : (int)$rank_thresholds[$a-1]+1);
			$high_val = ($a == 9 ? '&#8734' : "<input class='tbox' type='text' name='rank_thresholds[]' size='10' value='".$rank_thresholds[$a]."' maxlength='5' />");

			$text .= "<tr>
			<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_names[]' size='30' value='".($rank_names[$a] ? $rank_names[$a] : "")."' maxlength='30' /></td>
			<td class='forumheader3' style='width:20%; text-align:center'>{$low_val} - {$high_val}</td>
			<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_images[]' size='30' value='".($rank_images[$a] ? $rank_images[$a] : "")."' maxlength='30' /></td>
			</tr>";
		}

		$text .= "<tr>
		<td colspan='3'  style='text-align:center' class='forumheader'>
		<input type='hidden' name='e-token' value='".e_TOKEN."' />
		<input class='button' type='submit' name='set_ranks' value='".FORLAN_94."' />
		</td>
		</tr>
		</table>\n</form>\n</div>";
		$ns->tablerender("Ranks", $text);
	}

	function show_mods()
	{
		global $sql, $ns, $clanfor, $tp;
		$forumList = $clanfor->forum_getforums('all');
		$parentList = $clanfor->forum_getparents('list');
		$subList   = $clanfor->forum_getsubs('bysub');

		$txt = "<form method='post' action='".e_SELF."?".e_QUERY."'><table class='fborder' style='width:100%' id='show_mods' ><tr><td> &nbsp; </td>";

		foreach($parentList as $p)
		{
			$txt .= "
			<tr>
			<td colspan='2' class='fcaption'><strong>".$tp->toHTML($p['forum_name'])."</strong></td>
			</tr>
			";

			foreach($forumList[$p['forum_id']] as $f)
			{
				$txt .= "
				<tr>
				<td class='forumheader'>{$f['forum_name']}</td>
				<td class='forumheader'>".r_userclass("mods[{$f['forum_id']}]", $f['forum_moderators'], 'off', 'admin,classes')."</td>
				</tr>
				";
				foreach($subList[$f['forum_id']] as $s)
				{
					$txt .= "
					<tr>
					<td class='forumheader3'>&nbsp;&nbsp;&nbsp;&nbsp;{$s['forum_name']}</td>
					<td class='forumheader3'>".r_userclass("mods[{$s['forum_id']}]", $s['forum_moderators'], 'off', 'admin,classes')."</td>
					</tr>
					";
				}
			}
		}
			$txt .= "
			<tr>
			<td colspan='2' class='fcaption' style='text-align:center'>
			<input type='hidden' name='e-token' value='".e_TOKEN."' />
			<input class='button' type='submit' name='setMods' value='".WMGLAN_4." ".FORLAN_33."' />
			</td>
			</tr>

			</table></form>";
			$ns->tablerender(FORLAN_33, $txt);
		}

		function show_rules()
		{
			global  $ns, $tp;
      $sql = e107::getDb();
      $plugPref =  e107::pref(CLAN_FORUM_FOLDER);
      
			$sql->db_Select("wmessage");
			list($null) = $sql->fetch();
			list($null) = $sql->fetch();
			list($null) = $sql->fetch();
			list($id, $guestrules, $wm_active4) = $sql->fetch();
			list($id, $memberrules, $wm_active5) = $sql->fetch();
			list($id, $adminrules, $wm_active6) = $sql->fetch();

			if($sql->select('generic','*',"gen_type='forum_rules_guest'"))
			{
				$guest_rules = $sql->fetch();
			}
			if($sql->select('generic','*',"gen_type='forum_rules_member'"))
			{
				$member_rules = $sql->fetch();
			}
			if($sql->select('generic','*',"gen_type='forum_rules_admin'"))
			{
				$admin_rules = $sql->fetch();
			}

			$guesttext = $tp->toFORM($guest_rules['gen_chardata']);
			$membertext = $tp->toFORM($member_rules['gen_chardata']);
			$admintext = $tp->toFORM($admin_rules['gen_chardata']);

			$text = "
			<div style='text-align:center'>
			<form method='post' action='".e_SELF."?rules'  id='wmform'>
			<table style='".ADMIN_WIDTH."' class='fborder' id='show_rules'>
			<tr>";

			$text .= "

			<td style='width:20%' class='forumheader3'>".WMGLAN_1.": <br />
			".WMGLAN_6.":";
			if ($guest_rules['gen_intdata'])
			{
				$text .= "<input type='checkbox' name='guest_active' value='1'  checked='checked' />";
			}
			else
			{
				$text .= "<input type='checkbox' name='guest_active' value='1' />";
			}
			$text .= "</td>
			<td style='width:60%' class='forumheader3'>
			<textarea class='tbox' name='guestrules' cols='70' rows='10'>$guesttext</textarea>
			<br />
			<input class='helpbox' type='text' name='helpguest' size='100' />
			<br />
			".display_help('helpb', 1, 'addtext1', 'help1')."
			</td>
			</tr>

			<tr>
			<td style='width:20%' class='forumheader3'>".WMGLAN_2.": <br />
			".WMGLAN_6.":";
			if ($member_rules['gen_intdata'])
			{
				$text .= "<input type='checkbox' name='member_active' value='1'  checked='checked' />";
			}
			else
			{
				$text .= "<input type='checkbox' name='member_active' value='1' />";
			}
			$text .= "</td>
			<td style='width:60%' class='forumheader3'>
			<textarea class='tbox' name='memberrules' cols='70' rows='10'>$membertext</textarea>
			<br />
			<input class='helpbox' type='text' name='helpmember' size='100' />
			<br />
			".display_help('helpb', 1, 'addtext2', 'help2')."
			</td>
			</tr>

			<tr>
			<td style='width:20%' class='forumheader3'>".WMGLAN_3.": <br />
			".WMGLAN_6.": ";

			if ($admin_rules['gen_intdata'])
			{
				$text .= "<input type='checkbox' name='admin_active' value='1'  checked='checked' />";
			}
			else
			{
				$text .= "<input type='checkbox' name='admin_active' value='1' />";
			}

			$text .= "</td>
			<td style='width:60%' class='forumheader3'>
			<textarea class='tbox' name='adminrules' cols='70' rows='10'>$admintext</textarea>
			<br />
			<input class='helpbox' type='text' name='helpadmin' size='100' />
			<br />
			".display_help('helpb', 1, 'addtext3', 'help3')."
			</td>
			</tr>

			<tr style='vertical-align:top'>
			<td class='forumheader'>&nbsp;</td>
			<td style='width:60%' class='forumheader'>
			<input type='hidden' name='e-token' value='".e_TOKEN."' />
			<input class='button' type='submit' name='frsubmit' value='".WMGLAN_4."' />
			</td>
			</tr>
			</table>
			</form>
			</div>";

			$ns->tablerender(WMGLAN_5, $text);

			echo "
			<script type=\"text/javascript\">
			function addtext1(sc){
				document.getElementById('wmform').guestrules.value += sc;
			}
			function addtext2(sc){
				document.getElementById('wmform').memberrules.value += sc;
			}
			function addtext3(sc){
				document.getElementById('wmform').adminrules.value += sc;
			}
			function help1(help){
				document.getElementById('wmform').helpguest.value = help;
			}
			function help2(help){
				document.getElementById('wmform').helpmember.value = help;
			}
			function help3(help){
				document.getElementById('wmform').helpadmin.value = help;
			}
			</script>
			";

		}
	}

	function forum_admin_adminmenu()
	{
		global $clanforum;
		global $action;
    if(!e107::isInstalled(CLAN_FORUM_FOLDER)) { return; }
		$clanforum->show_options($action);
	}
	?>
