<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.
 * If not, see <http://www.gnu.org/licenses/>.
 */


define("IN_MYBB", 1);
define("NO_ONLINE", 1);
define('THIS_SCRIPT', 'thankyoulike.php');

$prefix = "g33k_thankyoulike_";

$templatelist = "thankyoulike_users,thankyoulike_postbit,thankyoulike_postbit_classic,thankyoulike_button_add,thankyoulike_button_del";

require_once "./global.php";

// Load global language phrases
$lang->load("thankyoulike");

if($mybb->user['uid'] == 0)
{
	error_no_permission();
}

// Access to this file only if plugin is existant and active
if (!$mybb->settings[$prefix.'enabled'])
{
	error($lang->sprintf($lang->tyl_error_disabled, "This"));
}

if ($mybb->settings[$prefix.'thankslike'] == "like")
{
	$pre = $lang->tyl_like;
	$pre1 = $lang->tyl_liked;
	$pre2 = $lang->tyl_likes;
}
else
{
	$pre = $lang->tyl_thankyou;
	$pre1 = $lang->tyl_thanked;
	$pre2 = $lang->tyl_thanks;
}

if($mybb->settings[$prefix.'enabled'] != "1")
{
	error($lang->sprintf($lang->tyl_error_disabled, $pre));
}

// Exit if no regular action.
if($mybb->input['action'] != "add" && $mybb->input['action'] != "del")
{
	error($lang->tyl_error_invalid_action);
}

// Verify post key
verify_post_check($mybb->input['my_post_key']);

// Get the pid and tid
$pid = intval($mybb->input['pid']);
$options = array(
		"limit" => 1
	);
$query_post = $db->simple_select("posts", "*", "pid='".$pid."'", $options);
$post = $db->fetch_array($query_post);
if(!$post['pid'])
{
	error($lang->error_invalidpost);
}
$tid = $post['tid'];

// Set up $thread and $forum.
$options = array(
	"limit" => 1
);
$query_thread = $db->simple_select("threads", "*", "tid='".$tid."'", $options);
$thread = $db->fetch_array($query_thread);
$fid = $thread['fid'];

// Get forum info
$forum = get_forum($fid);
if(!$forum)
{
	error($lang->error_invalidforum);
}

$forumpermissions = forum_permissions($fid);

// See if everything is valid up to here.
if(isset($post) && (($post['visible'] == 0 && !is_moderator($fid)) || $post['visible'] == 0))
{
	error($lang->error_invalidpost);
}
if(isset($thread) && (($thread['visible'] == 0 && !is_moderator($fid)) || $thread['visible'] < 0))
{
	error($lang->error_invalidthread);
}
if($forum['open'] == 0 || $forum['type'] != "f")
{
	error($lang->error_closedinvalidforum);
}
if($forumpermissions['canview'] == 0 || $forumpermissions['canpostreplys'] == 0 || $mybb->user['suspendposting'] == 1)
{
	error_no_permission();
}

// Check if this forum is password protected and we have a valid password
check_forum_password($forum['fid']);

// Check to see if the thread is closed, and if the user is a mod.
if(!is_moderator($fid, "caneditposts"))
{
	if($thread['closed'] == 1 && $mybb->settings[$prefix.'closedthreads'] != "1")
	{
		error($lang->sprintf($lang->tyl_error_threadclosed, $pre));
	}
}

// Check if setting is first post or not and if it is, whether its the first post
if($mybb->settings[$prefix.'firstall'] == "first" && $thread['firstpost'] != $post['pid'])
{
	error($lang->tyl_error_not_allowed);
}

// Check if the post is in a forum that has been excluded
// Check first if this post is in an exclued forum, if it is end right here.
$exc_forums = explode(",", $mybb->settings[$prefix.'exclude']);
$excluded = false;
foreach($exc_forums as $exc_forum)
{
	if (trim($exc_forum) == $fid)
	{
		$excluded = true;
	}
}
if ($excluded)
{
	error($lang->tyl_error_excluded);
}

if($mybb->input['action'] == "add")
{
	$message = '';

	// Check if this user has reached their "maximum thanks/likes per day" quota
	if($mybb->usergroup['tyl_limits_max'] != 0 && $mybb->settings[$prefix.'limits'] == "1")
	{
		$timesearch = TIME_NOW - (60 * 60 * 24);
		$query = $db->simple_select($prefix."thankyoulike", "*", "uid='{$mybb->user['uid']}' AND dateline>'$timesearch'");
		$numtoday = $db->num_rows($query);

		// Time left to next thankyou/like
		if($numtoday>0)
		{
			$lastthank = $db->fetch_array($db->simple_select($prefix."thankyoulike", "dateline", "uid='{$mybb->user['uid']}' AND dateline>'$timesearch'", array("order_by" => 'dateline',"order_dir" => 'ASC', "limit" => 1)));
			$tyltimediff = $lastthank['dateline'] - $timesearch;

			if($tyltimediff > 0)
			{
				$tyltimeleft = my_date("H", $tyltimediff).'h '.my_date("i", $tyltimediff).'m '.my_date("s", $tyltimediff).'s';
			}
		}

		// Reached the quota - error.
		if($numtoday >= $mybb->usergroup['tyl_limits_max'])
		{
			if($mybb->settings[$prefix.'displaygrowl'] == 1 || !$mybb->get_input('ajax', MyBB::INPUT_INT))
			{
				$message = $lang->sprintf("<strong>".$lang->tyl_error_reached_max_limit."</strong>", $pre2);
				if($tyltimeleft)
				{
					$message .= $lang->sprintf("<br/>".$lang->tyl_error_reached_max_timeleft, $pre2).$tyltimeleft;
				}
			}
			else
			{
				$message = $lang->sprintf($lang->tyl_error_reached_max_limit, $pre2);
				if($tyltimeleft)
				{
					$message .= $lang->sprintf("\n==>".$lang->tyl_error_reached_max_timeleft, $pre2).$tyltimeleft;
				}
			}
		}
	}

	// Can't thank/like own post
	if($post['uid'] == $mybb->user['uid'] && $mybb->settings[$prefix.'tylownposts'] != "1")
	{
		$message = $lang->sprintf($lang->tyl_error_own_post, $pre);
	}

	if($message)
	{
		if($mybb->get_input('ajax', MyBB::INPUT_INT))
		{
			echo json_encode($message);
			exit;
		}
		else
		{
			$url = get_post_link($pid, $tid)."#pid{$pid}";
			redirect($url, $message.$lang->tyl_redirect_back, $lang->tyl_error, true);
		}

	}

	// Check if user has already thanked/liked this post.
	$options = array(
			"limit" => 1
		);
	$query_check = $db->simple_select($prefix."thankyoulike", "*", "pid='".$pid."' AND uid='".$mybb->user['uid']."'", $options);
	$utyl = $db->fetch_array($query_check);

	if(isset($utyl['tlid']))
	{
		error($lang->sprintf($lang->tyl_error_already_tyled, $pre1));
	}

	// Add ty/l to db
	$tyl_data = array(
			"pid" => intval($post['pid']),
			"uid" => intval($mybb->user['uid']),
			"puid" => intval($post['uid']),
			"dateline" => TIME_NOW
	);

	$tlid = $db->insert_query($prefix."thankyoulike", $tyl_data);
	// Verify if myalerts exists and if compatible with 1.8.x then add alert type
	include_once('inc/plugins/thankyoulike.php');
	if(function_exists("myalerts_info")){
		// Load myalerts info into an array
		$my_alerts_info = myalerts_info();
		// Set version info to a new var
		$verify = $my_alerts_info['version'];
		// If MyAlerts 2.0 or better then do this !!!
		if($verify >= "2.0.0"){
		global $cache;
			// Load cache data and compare if version is the same or don't
			$myalerts_plugins = $cache->read('mybbstuff_myalerts_alert_types');
			if($myalerts_plugins['tyl']['code'] == 'tyl'){
				tyl_recordAlertThankyou();
			}
		}
	}

	if($tlid)
	{
		// Update tyl count in posts and threads and users and total
		if($post['tyl_pnumtyls'] == 0)
		{
			// Post thanks were previously 0, so add this post to user's thanked posts
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumptyls=tyl_unumptyls+1 WHERE uid='".intval($post['uid'])."'");
		}
		$db->write_query("UPDATE ".TABLE_PREFIX."posts SET tyl_pnumtyls=tyl_pnumtyls+1 WHERE pid='".intval($pid)."'");
		$db->write_query("UPDATE ".TABLE_PREFIX."threads SET tyl_tnumtyls=tyl_tnumtyls+1 WHERE tid='".intval($tid)."'");
		$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumtyls=tyl_unumtyls+1 WHERE uid='".intval($mybb->user['uid'])."'");
		$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumrcvtyls=tyl_unumrcvtyls+1 WHERE uid='".intval($post['uid'])."'");
		$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value+1 WHERE title='total'");
		if($mybb->input['ajax'])
		{
			// Do nothing here
		}
		else
		{
			// Go back to the post
			$url = get_post_link($pid, $tid)."#pid{$pid}";
			redirect($url, $lang->sprintf($lang->tyl_redirect_tyled, $pre).$lang->tyl_redirect_back);
			exit;
		}

		// Add also reputation points on thank or like
		if($mybb->settings[$prefix.'reputation_add'] != 0)
		{
			$reppoints = 1;
			if($mybb->settings[$prefix.'reputation_add_reppoints'] > 0)
			{
				$reppoints = intval($mybb->settings[$prefix.'reputation_add_reppoints']);
			}

			$repcomment = "";
			if(!empty($mybb->settings[$prefix.'reputation_add_repcomment']))
			{
				$repcomment = htmlspecialchars_uni($mybb->settings[$prefix.'reputation_add_repcomment']);
			}

			// Build array of reputation data.
			$reputation = array(
				"uid" => intval($post['uid']),
				"adduid" => $mybb->user['uid'],
				"pid" => intval($post['pid']),
				"reputation" => $reppoints,
				"dateline" => TIME_NOW,
				"comments" => $repcomment
			);

			// Insert a new reputation
			$db->insert_query("reputation", $reputation);
			// Update user
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET reputation=reputation+{$reppoints} WHERE uid='".intval($post['uid'])."'"); 
		}
	}
	else
	{
		error($lang->sprintf($lang->tyl_error_unknown, $pre));
	}
}

if($mybb->input['action'] == "del")
{
	if($mybb->settings[$prefix.'removing'] != "1")
	{
		error($lang->sprintf($lang->tyl_error_removal_disabled, $pre));
	}
	// Check tyl owner and tyl exists
	$options = array(
			"limit" => 1
		);
	$query_r = $db->simple_select($prefix."thankyoulike", "*", "pid='".$pid."' AND uid='".$mybb->user['uid']."'", $options);
	$tyl_r = $db->fetch_array($query_r);

	if(isset($tyl_r['tlid']))
	{
		if($tyl_r['uid'] == $mybb->user['uid'])
		{
			// process delete
			$db->delete_query($prefix."thankyoulike", "tlid='".$tyl_r['tlid']."'", "1");
			// if alert of user was added and unread then review if delete thanks and delete alert too.
			if((function_exists('myalerts_is_activated') && myalerts_is_activated()) && $mybb->user['uid']){
				$db->query("DELETE FROM ".TABLE_PREFIX."alerts WHERE from_user_id={$mybb->user['uid']} AND object_id='{$pid}' AND unread=1 LIMIT 1");
			}
			// Update counts
			if($post['tyl_pnumtyls'] == 1)
			{
				// This was the last thanks in the post, so remove this post from user's thanked posts
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumptyls=tyl_unumptyls-1 WHERE uid='".intval($post['uid'])."'");
			}
			$db->write_query("UPDATE ".TABLE_PREFIX."posts SET tyl_pnumtyls=tyl_pnumtyls-1 WHERE pid='".intval($pid)."'");
			$db->write_query("UPDATE ".TABLE_PREFIX."threads SET tyl_tnumtyls=tyl_tnumtyls-1 WHERE tid='".intval($tid)."'");
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumtyls=tyl_unumtyls-1 WHERE uid='".intval($mybb->user['uid'])."'");
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumrcvtyls=tyl_unumrcvtyls-1 WHERE uid='".intval($post['uid'])."'");
			$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value-1 WHERE title='total'");

			if($mybb->input['ajax'])
			{
				// Do nothing here
			}
			else
			{
				$url = get_post_link($pid, $tid)."#pid{$pid}";
				redirect($url, $lang->sprintf($lang->tyl_redirect_deleted, $pre).$lang->tyl_redirect_back);
			}

			// delete given reputation points for thank or like
			if($mybb->settings[$prefix.'reputation_add'] != 0)
			{
				$reppoints = 1;
				if($mybb->settings[$prefix.'reputation_add_reppoints'] > 0)
				{
					$reppoints = intval($mybb->settings[$prefix.'reputation_add_reppoints']);
				}
				
				// delete given reputation
				$db->delete_query("reputation", "pid='".intval($post['pid'])."' AND adduid='".intval($mybb->user['uid'])."'");
				// Update user
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET reputation=reputation-{$reppoints} WHERE uid='".intval($post['uid'])."'"); 
			}
		}
		else
		{
			error($lang->sprintf($lang->tyl_error_own_delete, $pre));
		}
	}
	else
	{
		error($lang->sprintf($lang->tyl_error_not_found, $pre));
	}
}

if($mybb->input['ajax'])
{
	// Send headers.

	header("Content-type: application/json; charset={$charset}");
	// Get all the thanks/likes for this post
	switch($mybb->settings[$prefix.'sortorder'])
	{
		case "userdesc":
			$order = " ORDER BY username DESC";
			break;
		case "dtasc":
			$order = " ORDER BY dateline ASC";
			break;
		case "dtdesc":
			$order = " ORDER BY dateline DESC";
			break;
		case "userasc":
		default:
			$order = " ORDER BY username ASC";
			break;
	}
	$query1 = $db->query("
		SELECT tyl.*, u.username, u.usergroup, u.displaygroup
		FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
		LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=tyl.uid)
		WHERE tyl.pid='".$post['pid']."'
		".$order."
	");

	$tyls = '';
	$comma = '';
	$tyled = 0;
	$count = 0;
	while($tyl = $db->fetch_array($query1))
	{
		$profile_link = get_profile_link($tyl['uid']);
		// Format username... or not
		$tyl_list = $mybb->settings[$prefix.'unameformat'] == "1" ? format_name($tyl['username'], $tyl['usergroup'], $tyl['displaygroup']) : $tyl['username'];
		$datedisplay_next = $mybb->settings[$prefix.'showdt'] == "nexttoname" ? "<span class='smalltext'> (".my_date($mybb->settings[$prefix.'dtformat'], $tyl['dateline']).")</span>" : "";
		$datedisplay_title = $mybb->settings[$prefix.'showdt'] == "astitle" ? "title='".my_date($mybb->settings[$prefix.'dtformat'], $tyl['dateline'])."'" : "";
		eval("\$thankyoulike_users = \"".$templates->get("thankyoulike_users", 1, 0)."\";");
		$tyls .= trim($thankyoulike_users);
		$comma = ', ';
		// Has this user tyled?
		if($tyl['uid'] == $mybb->user['uid'])
		{
			$tyled = 1;
		}
		$count++;
	}

	// Are we using thanks or like? Setup titles
	if($count == 1)
	{
		$tyl_user = $lang->tyl_user;
		$tyl_say = $lang->tyl_says;
		$tyl_like = $lang->tyl_likes;
	}
	else
	{
		$tyl_user = $lang->tyl_users;
		$tyl_say = $lang->tyl_say;
		$tyl_like = $lang->tyl_like;
	}

	if ($mybb->settings[$prefix.'thankslike'] == "like")
	{
		$pre = "l";
		$lang->add_tyl = $lang->add_l;
		$lang->del_tyl = $lang->del_l;
		$tyl_thankslikes = $lang->tyl_likes;
		$tyl_data = get_user($post['uid']);
		if($mybb->settings[$prefix.'unameformat'] == "1"){
		$tyl_data['username'] = format_name($tyl_data['username'], $tyl_data['usergroup'], $tyl_data['displaygroup']);
		$tyl_profilelink = build_profile_link($tyl_data['username'], $tyl_data['uid']);
		}else{
		$tyl_profilelink  = htmlspecialchars_uni($tyl_data['username']);
		}
		$lang->tyl_title = $lang->sprintf($lang->tyl_title_l, $count, $tyl_user, $tyl_like, $tyl_profilelink);
		$lang->tyl_title_collapsed = $lang->sprintf($lang->tyl_title_collapsed_l, $count, $tyl_user, $tyl_like, $tyl_profilelink);
	}
	else if ($mybb->settings[$prefix.'thankslike'] == "thanks")
	{
		$pre = "ty";
		$lang->add_tyl = $lang->add_ty;
		$lang->del_tyl = $lang->del_ty;
		$tyl_thankslikes = $lang->tyl_thanks;
		$tyl_data = get_user($post['uid']);
		if($mybb->settings[$prefix.'unameformat'] == "1"){
		$tyl_data['username'] = format_name($tyl_data['username'], $tyl_data['usergroup'], $tyl_data['displaygroup']);
		$tyl_profilelink = build_profile_link($tyl_data['username'], $tyl_data['uid']);
		}else{
		$tyl_profilelink  = htmlspecialchars_uni($tyl_data['username']);
		}
		$lang->tyl_title = $lang->sprintf($lang->tyl_title_ty, $count, $tyl_user, $tyl_say, $tyl_profilelink);
		$lang->tyl_title_collapsed = $lang->sprintf($lang->tyl_title_collapsed_ty, $count, $tyl_user, $tyl_say, $tyl_profilelink);
	}
	// Setup the collapsible elements
	if ($mybb->settings[$prefix.'collapsible'] == "1" && $mybb->settings[$prefix.'colldefault'] == "closed")
	{
		$tyl_title_display = "display: none;";
		$tyl_title_display_collapsed = "";
		$tyl_data_display = "display: none;";
		$tyl_expcolimg = "collapse_collapsed.png";
		eval("\$tyl_expcol = \"".$templates->get("thankyoulike_expcollapse", 1, 0)."\";");
	}
	else if ($mybb->settings[$prefix.'collapsible'] == "1" && $mybb->settings[$prefix.'colldefault'] == "open")
	{
		$tyl_title_display = "";
		$tyl_title_display_collapsed = "display: none;";
		$tyl_data_display = "";
		$tyl_expcolimg = "collapse.png";
		eval("\$tyl_expcol = \"".$templates->get("thankyoulike_expcollapse", 1, 0)."\";");
	}
	else
	{
		$tyl_title_display = "";
		$tyl_title_display_collapsed = "display: none;";
		$tyl_data_display = "";
		$tyl_expcolimg = "";
		$tyl_expcol = "";
		$lang->tyl_title_collapsed = "";
	}
	$button_tyl = '';
	$tyluserid = $mybb->settings[$prefix.'tylownposts'] == "1" ? "-1" : $mybb->user['uid'];

	if(($tyled && $mybb->settings[$prefix.'removing'] != "1") || (!is_moderator($post['fid'], "caneditposts") && $thread['closed'] == 1 && $mybb->settings[$prefix.'closedthreads'] != "1") || $post['uid'] == $tyluserid || is_member($mybb->settings[$prefix.'hideforgroups']) || $mybb->settings[$prefix.'hideforgroups'] == "-1")
	{
		// Show no button for poster or user who has already thanked/liked or removing is disabled.
		$button_tyl = '';
	}
	else if($tyled && $mybb->settings[$prefix.'removing'] == "1" && (($mybb->settings[$prefix.'firstall'] == "first" && $thread['firstpost'] == $post['pid']) || $mybb->settings[$prefix.'firstall'] == "all"))
	{
		// Show remove button if removing already thanked/liked and removing enabled and is either the first post in thread if setting is for first or setting is all
		eval("\$button_tyl = \"".$templates->get("thankyoulike_button_del")."\";");
	}
	else if(($mybb->settings[$prefix.'firstall'] == "first" && $thread['firstpost'] == $post['pid']) || $mybb->settings[$prefix.'firstall'] == "all")
	{
		if ((my_strpos($mybb->settings[$prefix.'firstalloverwrite'], $post['fid']) !== false || $mybb->settings[$prefix.'firstalloverwrite'] == "-1") && $thread['firstpost'] != $post['pid'])
		{
			eval("\$button_tyl = \"".$templates->get("thankyoulike_button_add")."\";");
		}
		else
		{
			// Same as above but show add button
			eval("\$button_tyl = \"".$templates->get("thankyoulike_button_add")."\";");
		}
	}

	// Cleanup for JSON
	$button_tyl = thankyoulike_cleanup_json($button_tyl);

	if($count>0 && (($mybb->settings[$prefix.'firstall'] == "first" && $thread['firstpost'] == $post['pid']) || $mybb->settings[$prefix.'firstall'] == "all"))
	{
		// We have thanks/likes to show
		$post['thankyoulike'] = $tyls;
		$post['tyl_display'] = "";
		if($mybb->settings['postlayout'] == "classic")
		{
			eval("\$thankyoulike = \"".$templates->get("thankyoulike_postbit_classic")."\";");
		}
		else
		{
			eval("\$thankyoulike = \"".$templates->get("thankyoulike_postbit")."\";");
		}
		// Cleanup for JSON
		$thankyoulike = thankyoulike_cleanup_json($thankyoulike);

		echo '{';
		echo '"tylButton":"'.$button_tyl.'",';
		echo '"tylData":"'.$thankyoulike.'"';
		echo '}';
	}
	else
	{
		// Nothing to show, return blank data with buttons.

		echo '{';
		echo '"tylButton":"'.$button_tyl.'",';
		echo '"tylData":""';
		echo '}';
	}
	exit;
}

function thankyoulike_cleanup_json($data)
{
	return addcslashes($data, "\\\/\"\n\r\t/".chr(0).chr(8).chr(12));
}
