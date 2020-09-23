<?php

if (!defined('e107_INIT'))
{
	exit;
}

if (check_class($extraclass) && e107::isInstalled('forum') )
{

	// Fourm

	if ($extrahide == 1)
	{
		$text.= "<div id='topreplier-title' style='cursor:hand; font-size: " . $onlineinfomenufsize . "px; text-align:left; vertical-align: middle; width:" . $onlineinfomenuwidth . "; font-weight:bold;' title='" . ONLINEINFO_LOGIN_MENU_L57 . "'>&nbsp;" . ONLINEINFO_LOGIN_MENU_L57 . "</div>";
		$text.= "<div id='topreplier' class='switchgroup1' style='display:none'>";
		$text.= "<table style='text-align:left; width:" . $onlineinfomenuwidth . "; margin-left:5px;'>";
	}
	else
	{
		$text.= "<div class='smallblacktext' style='font-size: " . $onlineinfomenufsize . "px; font-weight:bold; margin-left:5px; margin-top:10px; width:" . $onlineinfomenuwidth . "'>" . ONLINEINFO_LOGIN_MENU_L57 . "</div><div style='text-align:left; width:" . $onlineinfomenuwidth . "; margin-left:5px;'><table style='text-align:left; width:" . $onlineinfomenuwidth . "'>";
	}

	$query = "
    SELECT FLOOR(post_user) as t_user, COUNT(FLOOR(fp.post_user)) AS ucount, u.user_name, u.user_id FROM  #forum_post as fp
		LEFT JOIN #user AS u ON FLOOR(fp.post_user) = u.user_id
    GROUP BY fp.post_user
		ORDER BY ucount DESC 
    LIMIT 0," . $extrarecords . "";
	/*
	$query = "
	SELECT FLOOR(thread_user) as t_user, COUNT(FLOOR(ft.thread_user)) AS ucount, u.user_name, u.user_id FROM #forum_thread  as ft
	LEFT JOIN #user AS u ON FLOOR(ft.thread_user) = u.user_id
	WHERE ft.thread_parent!=0
	GROUP BY t_user
	ORDER BY ucount DESC
	LIMIT 0,".$extrarecords."";
	*/
  
  /* forum stats      
  		SELECT COUNT(fp.post_id) AS post_count, u.user_name, u.user_id, fp.post_thread FROM #forum_post as fp
		LEFT JOIN #user AS u ON fp.post_user = u.user_id
		GROUP BY fp.post_user
		ORDER BY post_count DESC LIMIT 0,10";
    */
    
	if ($extraacache == 1)
	{
		$cachet = $extracachetime * 60;
		$currenttime = time();
		$script = "SELECT * FROM " . MPREFIX . "onlineinfo_cache Where type='toppostreplier'";
		$sql->gen($script);
		while ($row = $sql->fetch())
		{
			extract($row);
			$lasttimerun = $cache_timestamp;
		}

		if (($currenttime - $lasttimerun) > $cachet)
		{

			// run cache update

			$buildcache = "";
			$sql->gen($query);
			$setarray = 0;
			while ($row = $sql->fetch())
			{
				extract($row);
				$buildcache[$setarray] = $t_user . "|" . $user_name . "=>" . $ucount;
				$setarray++;
			}

			$arraydata = "";
			for ($y = 0; $y <= ($setarray - 1); $y++)
			{
				$arraydata.= $buildcache[$y];
				$arraydata.= ($y < $setarray - 1) ? "," : "";
			}

			$sql->db_Update("onlineinfo_cache", "cache='" . $arraydata . "',cache_timestamp='" . time() . "' WHERE type='toppostreplier'");
		}

		// use cache

		$script = "SELECT * FROM " . MPREFIX . "onlineinfo_cache Where type='toppostreplier'";
		$sql->gen($script);
		while ($row = $sql->fetch())
		{
			extract($row);
			$blowdata = explode(",", $cache);
			$countdata = count($blowdata);
			for ($z = 0; $z <= ($countdata - 1); $z++)
			{
				$blowmoredata = explode("=>", $blowdata[$z]);
				$blowdataagain = explode("|", $blowmoredata[0]);
				$toppostreplier = $blowmoredata[1];
				$t_user = $blowdataagain[0];
				$user_name = $blowdataagain[1];
				if ($t_user == "0")
				{
					$text.= "<tr><td style='vertical-align:top; text-align:left; width:80%;'>" . ONLINEINFO_LOGIN_MENU_L56 . "</td>
					<td style='vertical-align:top; text-align:right; width:20%; padding-right:20px;'>" . $toppostreplier . "</td></tr>";
				}
				else
				{
					$text.= "<tr><td style='vertical-align:top; text-align:left; width:80%;'><a href='" . e_BASE . "user.php?id." . $t_user . "' " . getuserclassinfo($t_user) . ">" . $user_name . "</a></td>
					<td style='vertical-align:top; text-align:right; width:20%; padding-right:20px;'>" . $toppostreplier . "</td></tr>";
				}
			}
		}
	}
	else
	{
		//$sql->db_Select_gen($query);
    $posters = $sql->retrieve($query, true);
 
		foreach($posters as $poster)
		{
			if ($poster['t_user'] == "0")
			{
				$text.= "<tr>
<td style='vertical-align:top; text-align:left; width:80%;'>" . ONLINEINFO_LOGIN_MENU_L56 . "</td>
<td style='vertical-align:top; text-align:right; width:20%; padding-right:20px;'>" . $poster['ucount'] . "</td>
</tr>";
			}
			else
			{
				$text.= "<tr>
<td style='vertical-align:top; text-align:left; width:80%;'><a href='" . e_BASE . "user.php?id." . $poster['user_id'] . "' " . getuserclassinfo($poster['user_id']) . ">" . $poster['user_name'] . "</a></td>
<td style='vertical-align:top; text-align:right; width:20%; padding-right:20px;'>" . $poster['ucount'] . "</td>
</tr>";
			}
		}
	}

	$text.= "</table><br /></div>";
}
 