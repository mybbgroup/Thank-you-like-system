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

// (For AJAX) The extended duration in milliseconds for the popup when extra info, especially time remaining, is shown on adding a like.
// This is to give the human viewer enough time to read and process that extra info.
const c_long_life_popup_duration_ms = '12000';

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

// Output the CSS of the current version of the plugin's thankyoulike.css file for the Master theme.
if($mybb->input['action'] == "css")
{
	header("Content-type: text/css; charset={$charset}");
	echo tyl_get_thankyoulike_css();
	exit;
}

// Exit if no regular action.
if($mybb->input['action'] != "add" && $mybb->input['action'] != "del")
{
	error($lang->tyl_error_invalid_action);
}

// Check whether the flood limit is enabled and the user added/removed this tyl too rapidly.
if ($mybb->usergroup['tyl_flood_interval'] > 0 && $mybb->settings[$prefix.'limits'] == "1") {
	$lastadddeldate =  $db->fetch_array($db->simple_select("users", "tyl_lastadddeldate", "uid='{$mybb->user['uid']}'"))['tyl_lastadddeldate'];
	if (TIME_NOW <= $lastadddeldate + $mybb->usergroup['tyl_flood_interval']) {
		$secondsleft = $lastadddeldate + $mybb->usergroup['tyl_flood_interval'] - TIME_NOW;
		error($lang->sprintf($lang->tyl_error_flood_interval_exceeded, $pre2, $secondsleft, $pre));
	}
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
if($forumpermissions['canview'] == 0 || $mybb->user['suspendposting'] == 1)
{
	error_no_permission();
}

$err_msgs = array();
if (tyl_is_tyling_forbidden($thread, $fid, $pid, $post['uid'], $mybb->user['uid'], false, $err_msgs))
{
	error(implode('<br /><br />', $err_msgs));
}

$msg_num_left = '';

if($mybb->input['action'] == "add")
{
	$message = '';

	// Check if this user has reached their "maximum thanks/likes per day" quota
	if($mybb->usergroup['tyl_limits_max'] != 0 && $mybb->settings[$prefix.'limits'] == "1")
	{
		$timesearch = TIME_NOW - (60 * 60 * 24);
		$query = $db->simple_select($prefix."thankyoulike", "*", "uid='{$mybb->user['uid']}' AND dateline>'$timesearch'");
		$numtoday = $db->num_rows($query);

		$msg_num_left_life = c_long_life_popup_duration_ms;

		$num_left_after_add = $mybb->usergroup['tyl_limits_max'] - $numtoday - 1;

		// Time left to next thankyou/like
		if($numtoday>0)
		{
			$lastthank = $db->fetch_array($db->simple_select($prefix."thankyoulike", "dateline", "uid='{$mybb->user['uid']}' AND dateline>'$timesearch'", array("order_by" => 'dateline',"order_dir" => 'ASC', "limit" => 1)));
			$tyltimediff = $lastthank['dateline'] - $timesearch;

			if($tyltimediff > 0)
			{
				$tyltimeleft = my_date("H", $tyltimediff).'h '.my_date("i", $tyltimediff).'m '.my_date("s", $tyltimediff).'s';
			}

			$msg_num_left = $lang->sprintf($lang->tyl_num_left_for, $num_left_after_add, $pre2, $tyltimeleft);
		}
		else
		{
			$msg_num_left = $lang->sprintf($lang->tyl_num_left, $num_left_after_add, $pre2);
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
	else
	{
		$msg_num_left = $lang->sprintf($lang->tyl_num_left_unlimited, $pre2);
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

	// Update user's last like add/del date
	$db->update_query('users', array('tyl_lastadddeldate' => TIME_NOW), 'uid='.intval($mybb->user['uid']));

	// If a compatible version of MyAlerts exists, then add an alert for this tyl.
	tyl_recordAlertThankyou();

	if($tlid)
	{
		// Update tyl count in posts and threads and users and total
		if($post['tyl_pnumtyls'] == 0)
		{
			if(!tyl_in_forums($fid, $mybb->settings[$prefix.'exclude_count']))
			{
				// Post thanks were previously 0, so add this post to user's thanked posts
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumptyls=tyl_unumptyls+1 WHERE uid='".intval($post['uid'])."'");
			}
		}
		$db->write_query("UPDATE ".TABLE_PREFIX."posts SET tyl_pnumtyls=tyl_pnumtyls+1 WHERE pid='".intval($pid)."'");

		if(!tyl_in_forums($fid, $mybb->settings[$prefix.'exclude_count']))
		{
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumtyls=tyl_unumtyls+1 WHERE uid='".intval($mybb->user['uid'])."'");
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumrcvtyls=tyl_unumrcvtyls+1 WHERE uid='".intval($post['uid'])."'");
		}
		$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value+1 WHERE title='total'");

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
			// If a compatible version of MyAlerts is active, then delete any unread alert for the deleted tyl.
			if(tyl_have_myalerts() && $mybb->user['uid']){
				$db->query("DELETE FROM ".TABLE_PREFIX."alerts WHERE from_user_id={$mybb->user['uid']} AND object_id='{$pid}' AND unread=1 LIMIT 1");
			}
			// Update user's last like add/del date
			$db->update_query('users', array('tyl_lastadddeldate' => TIME_NOW), 'uid='.intval($mybb->user['uid']));
			// Update counts
			if($post['tyl_pnumtyls'] == 1)
			{
				if(!tyl_in_forums($fid, $mybb->settings[$prefix.'exclude_count']))
				{
					// This was the last thanks in the post, so remove this post from user's thanked posts
					$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumptyls=tyl_unumptyls-1 WHERE uid='".intval($post['uid'])."'");
				}
			}
			$db->write_query("UPDATE ".TABLE_PREFIX."posts SET tyl_pnumtyls=tyl_pnumtyls-1 WHERE pid='".intval($pid)."'");
			if(!tyl_in_forums($fid, $mybb->settings[$prefix.'exclude_count']))
			{
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumtyls=tyl_unumtyls-1 WHERE uid='".intval($mybb->user['uid'])."'");
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumrcvtyls=tyl_unumrcvtyls-1 WHERE uid='".intval($post['uid'])."'");
			}
			$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value-1 WHERE title='total'");

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

			if($mybb->input['ajax'])
			{
				// Do nothing here
			}
			else
			{
				$url = get_post_link($pid, $tid)."#pid{$pid}";
				redirect($url, $lang->sprintf($lang->tyl_redirect_deleted, $pre).$lang->tyl_redirect_back);
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
		SELECT tyl.*, u.username, u.usergroup, u.displaygroup, u.avatar
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
		$avatar = format_avatar($tyl['avatar']);
		// Format username... or not
		$tyl_list = $mybb->settings[$prefix.'unameformat'] == "1" ? format_name($tyl['username'], $tyl['usergroup'], $tyl['displaygroup']) : $tyl['username'];
		$datedisplay_plain = my_date($mybb->settings[$prefix.'dtformat'], $tyl['dateline']);
		$datedisplay_next = $mybb->settings[$prefix.'showdt'] == "nexttoname" ? "<span class='smalltext'> (".$datedisplay_plain.")</span>" : "";
		$datedisplay_title = $mybb->settings[$prefix.'showdt'] == "astitle" ? "title='".$datedisplay_plain."'" : "";
		eval("\$thankyoulike_users = \"".$templates->get($mybb->settings[$prefix.'likersdisplay'] == "avatars" ? "thankyoulike_users_avatars" : "thankyoulike_users", 1, 0)."\";");
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

	if (($which_btn = tyl_get_which_btn($thread, $post['fid'], $post['pid'], $post['uid'], $mybb->user['uid'], $tyled)))
	{
		eval("\$button_tyl = \"".$templates->get("thankyoulike_button_$which_btn")."\";");
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
			eval("\$thankyoulike = \"".$templates->get($mybb->settings[$prefix.'likersdisplay'] == "avatars" ? "thankyoulike_postbit_classic_avatars" : "thankyoulike_postbit_classic")."\";");
		}
		else
		{
			eval("\$thankyoulike = \"".$templates->get($mybb->settings[$prefix.'likersdisplay'] == "avatars" ? "thankyoulike_postbit_avatars" : "thankyoulike_postbit")."\";");
		}
		// Cleanup for JSON
		$thankyoulike = thankyoulike_cleanup_json($thankyoulike);
		$msg_num_left = thankyoulike_cleanup_json($msg_num_left);

		echo '{';
		echo '"tylButton":"'.$button_tyl.'",';
		echo '"tylData":"'.$thankyoulike.'",';
		echo '"tylMsgNumLeft":"'.$msg_num_left.'"';
		if (isset($msg_num_left_life))
		{
			echo ',"tylMsgLife":'.$msg_num_left_life.'';
		}
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
