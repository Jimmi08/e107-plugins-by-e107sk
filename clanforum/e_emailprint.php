<?php
if (!defined('e107_INIT')) { exit; }

function print_item($thread_id)
{
	global $tp;
	$gen = new convert;
	include_once(CLAN_FORUM_FORUMCLASS);
	$forum = new e107clanforum;
	if (!$forum->thread_get_allowed($thread_id))
	{
		echo "Insufficient permissions";
		exit;
	}
	$thread_info = $forum->thread_get($thread_id,0,999);
	$thread_name = $tp -> toHTML($thread_info[0]['thread_name'], TRUE);
	$text = "<b>".$thread_name."</b><br />
	".$thread_info[0]['user_name'].", ".$gen->convert_date($thread_info[0]['thread_datestamp'], CLAN_FORUM_TIMEFORMAT)."<br /><br />
	".$tp -> toHTML($thread_info[0]['thread_thread'], TRUE);


	$count = 1;
	
	unset($thread_info[0], $thread_info['head']);
	foreach($thread_info as $reply)
	{
		$text .= "<br /><br />Re: <b>".$thread_name."</b><br />
		".$reply['user_name'].", ".$gen->convert_date($reply['thread_datestamp'], CLAN_FORUM_TIMEFORMAT)."<br /><br />
		".$tp -> toHTML($reply['thread_thread'], TRUE);
	}


//	return "<pre>".print_r($thread_info,TRUE)."</pre>";
	return $text;
}

function email_item($thread_id)
{
	global $tp;
	$gen = new convert;
	include_once(CLAN_FORUM_FORUMCLASS);
	$forum = new e107forum;
	if (!$forum->thread_get_allowed($thread_id))
	{
		echo "Insufficient permissions";
		exit;
	}
	$thread_info = $forum->thread_get($thread_id,0,999);

	$thread_name = $tp -> toHTML($thread_info[0]['thread_name'], TRUE);
	$text = "<b>".$thread_name."</b><br />
	".$thread_info[0]['user_name'].", ".$gen->convert_date($thread_info[0]['thread_datestamp'], CLAN_FORUM_TIMEFORMAT)."<br /><br />
	".$tp -> toHTML($thread_info[0]['thread_thread'], TRUE);

	$count = 1;

	unset($thread_info[0], $thread_info['head']);
	foreach($thread_info as $reply)
	{
		$text .= "<br /><br />Re: <b>".$thread_name."</b><br />
		".$reply['user_name'].", ".$gen->convert_date($reply['thread_datestamp'], CLAN_FORUM_TIMEFORMAT)."<br /><br />
		".$tp -> toHTML($reply['thread_thread'], TRUE);
	}
	return $text;
}

?>