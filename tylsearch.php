<?php
/**
 * Thank You/Like system - plugin for MyBB 1.8.x forum software
 * 
 * @package MyBB Plugin
 * @author MyBB Group - Eldenroot & SvePu & lairdshaw - <eldenroot@gmail.com>
 * @copyright 2018 MyBB Group <http://mybb.group>
 * @link <https://github.com/mybbgroup/MyBB_Thank-you-like-plugin>
 * @version 3.0.0
 * @license GPL-3.0
 * 
 */

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
 *
 ****************************************************
 * This is a modified search.php file 
 * to use for thank you/like system
 ****************************************************
 */

define("IN_MYBB", 1);
define("IGNORE_CLEAN_VARS", "sid");
define('THIS_SCRIPT', 'tylsearch.php');

require_once "./global.php";
require_once MYBB_ROOT."inc/functions_post.php";
require_once MYBB_ROOT."inc/functions_search.php";

$prefix = "g33k_thankyoulike_";

// Load global language phrases
$lang->load("search");
$lang->load("thankyoulike");

// Access to this file only if plugin is active and enabled
if(!in_array('thankyoulike', $cache->read('plugins')['active']) || !$mybb->settings[$prefix.'enabled'])
{
	error($lang->sprintf($lang->tyl_error_disabled, $lang->tyl_this));
}

if($mybb->settings[$prefix.'thankslike'] == "like")
{
	$pre = $lang->tyl_like;
}
else
{
	$pre = $lang->tyl_thankyou;
}

if($mybb->settings[$prefix.'enabled'] != "1")
{
	error($lang->sprintf($lang->tyl_error_disabled, $pre));
}

$tyl_uid = 	intval($mybb->input['uid']);

if($mybb->input['action'] == "usertylthreads" && $tyl_uid)
{
	$ownTYLout = '';
	if($mybb->settings[$prefix.'remowntylfroms'] == 1)
	{
		$ownTYLout = 't.uid <> '.$tyl_uid.' AND ';
	}
	
	$where_sql = "{$ownTYLout}t.tid IN (SELECT p.tid 
				FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
				LEFT JOIN ".TABLE_PREFIX."posts p ON ( p.pid = tyl.pid )
				WHERE tyl.uid = $tyl_uid)";

	$unsearchforums = get_unsearchable_forums();
	if($unsearchforums)
	{
		$where_sql .= " AND t.fid NOT IN ($unsearchforums)";
	}
	$inactiveforums = get_inactive_forums();
	if($inactiveforums)
	{
		$where_sql .= " AND t.fid NOT IN ($inactiveforums)";
	}
	
	$permsql = "";
	$onlyusfids = array();

	// Check group permissions if we can't view threads not started by us
	$group_permissions = forum_permissions();
	foreach($group_permissions as $fid => $forum_permissions)
	{
		if($forum_permissions['canonlyviewownthreads'] == 1)
		{
			$onlyusfids[] = $fid;
		}
	}
	if(!empty($onlyusfids))
	{
		$where_sql .= " AND t.fid NOT IN(".implode(',', $onlyusfids).")";
	}
	
	$sid = md5(uniqid(microtime(), 1));
	$searcharray = array(
		"sid" => $db->escape_string($sid),
		"uid" => $mybb->user['uid'],
		"dateline" => TIME_NOW,
		"ipaddress" => $db->escape_binary($session->packedip),
		"threads" => '',
		"posts" => '',
		"resulttype" => "threads",
		"querycache" => $db->escape_string($where_sql),
		"keywords" => ''
	);
	$db->insert_query("searchlog", $searcharray);
	redirect("search.php?action=results&uid=".$tyl_uid."&sid=".$sid, $lang->redirect_searchresults);
}
elseif($mybb->input['action'] == "usertylposts" && $tyl_uid)
{
	$ownTYLout = '';
	if($mybb->settings[$prefix.'remowntylfroms'] == 1)
	{
		$ownTYLout = 'uid <> '.$tyl_uid.' AND ';
	}
	
	$where_sql = "{$ownTYLout}pid IN (SELECT tyl.pid 
				FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
				WHERE tyl.uid = $tyl_uid)";
	
	$unsearchforums = get_unsearchable_forums();
	if($unsearchforums)
	{
		$where_sql .= " AND fid NOT IN ($unsearchforums)";
	}
	$inactiveforums = get_inactive_forums();
	if($inactiveforums)
	{
		$where_sql .= " AND fid NOT IN ($inactiveforums)";
	}
	
	$permsql = "";
	$onlyusfids = array();

	// Check group permissions if we can't view threads not started by us
	$group_permissions = forum_permissions();
	foreach($group_permissions as $fid => $forum_permissions)
	{
		if($forum_permissions['canonlyviewownthreads'] == 1)
		{
			$onlyusfids[] = $fid;
		}
	}
	if(!empty($onlyusfids))
	{
		$where_sql .= "AND ((fid IN(".implode(',', $onlyusfids).") AND uid='{$mybb->user['uid']}') OR fid NOT IN(".implode(',', $onlyusfids)."))";
	}
	$options = array(
		'order_by' => 'dateline',
		'order_dir' => 'desc'
	);

	// Do we have a hard search limit?
	if($mybb->settings['searchhardlimit'] > 0)
	{
		$options['limit'] = intval($mybb->settings['searchhardlimit']);
	}

	$pids = '';
	$comma = '';
	$query = $db->simple_select("posts", "pid", "{$where_sql}", $options);
	while($pid = $db->fetch_field($query, "pid"))
	{
			$pids .= $comma.$pid;
			$comma = ',';
	}

	$sid = md5(uniqid(microtime(), 1));
	$searcharray = array(
		"sid" => $db->escape_string($sid),
		"uid" => $mybb->user['uid'],
		"dateline" => TIME_NOW,
		"ipaddress" => $db->escape_binary($session->packedip),
		"threads" => '',
		"posts" => $db->escape_string($pids),
		"resulttype" => "posts",
		"querycache" => '',
		"keywords" => ''
	);
	$db->insert_query("searchlog", $searcharray);
	redirect("search.php?action=results&uid=".$tyl_uid."&sid=".$sid, $lang->redirect_searchresults);
}
elseif($mybb->input['action'] == "usertylforthreads" && $tyl_uid)
{
	$ownTYLout = '';
	if($mybb->settings[$prefix.'remowntylfroms'] == 1)
	{
		$ownTYLout = 'tyl.uid <> '.$tyl_uid.' AND ';
	}
	
	$where_sql = "t.uid = ".$tyl_uid." AND t.tid IN (SELECT p.tid 
				FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
				LEFT JOIN ".TABLE_PREFIX."posts p ON ( p.pid = tyl.pid )
				WHERE {$ownTYLout}tyl.puid = $tyl_uid)";
	

	$unsearchforums = get_unsearchable_forums();
	if($unsearchforums)
	{
		$where_sql .= " AND t.fid NOT IN ($unsearchforums)";
	}
	$inactiveforums = get_inactive_forums();
	if($inactiveforums)
	{
		$where_sql .= " AND t.fid NOT IN ($inactiveforums)";
	}
	
	$permsql = "";
	$onlyusfids = array();

	// Check group permissions if we can't view threads not started by us
	$group_permissions = forum_permissions();
	foreach($group_permissions as $fid => $forum_permissions)
	{
		if($forum_permissions['canonlyviewownthreads'] == 1)
		{
			$onlyusfids[] = $fid;
		}
	}
	if(!empty($onlyusfids))
	{
		$where_sql .= " AND ((t.fid IN(".implode(',', $onlyusfids).") AND t.uid='{$mybb->user['uid']}') OR t.fid NOT IN(".implode(',', $onlyusfids)."))";
	}
	
	$sid = md5(uniqid(microtime(), 1));
	$searcharray = array(
		"sid" => $db->escape_string($sid),
		"uid" => $mybb->user['uid'],
		"dateline" => TIME_NOW,
		"ipaddress" => $db->escape_binary($session->packedip),
		"threads" => '',
		"posts" => '',
		"resulttype" => "threads",
		"querycache" => $db->escape_string($where_sql),
		"keywords" => ''
	);
	$db->insert_query("searchlog", $searcharray);
	redirect("search.php?action=results&uid=".$tyl_uid."&sid=".$sid, $lang->redirect_searchresults);
}
elseif($mybb->input['action'] == "usertylforposts" && $tyl_uid)
{
	$ownTYLout = '';
	if($mybb->settings[$prefix.'remowntylfroms'] == 1)
	{
		$ownTYLout = 'tyl.uid <> '.$tyl_uid.' AND ';
	}
	
	$where_sql = "pid IN (
				SELECT tyl.pid 
				FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
				WHERE {$ownTYLout}tyl.puid = $tyl_uid)";
	
	$unsearchforums = get_unsearchable_forums();
	if($unsearchforums)
	{
		$where_sql .= " AND fid NOT IN ($unsearchforums)";
	}
	$inactiveforums = get_inactive_forums();
	if($inactiveforums)
	{
		$where_sql .= " AND fid NOT IN ($inactiveforums)";
	}
	
	$permsql = "";
	$onlyusfids = array();

	// Check group permissions if we can't view threads not started by us
	$group_permissions = forum_permissions();
	foreach($group_permissions as $fid => $forum_permissions)
	{
		if($forum_permissions['canonlyviewownthreads'] == 1)
		{
			$onlyusfids[] = $fid;
		}
	}
	if(!empty($onlyusfids))
	{
		$where_sql .= " AND ((fid IN(".implode(',', $onlyusfids).") AND uid='{$mybb->user['uid']}') OR fid NOT IN(".implode(',', $onlyusfids)."))";
	}
	
	$options = array(
	'order_by' => 'dateline',
	'order_dir' => 'desc'
	);
		
	// Do we have a hard search limit?
	if($mybb->settings['searchhardlimit'] > 0)
	{
		$options['limit'] = intval($mybb->settings['searchhardlimit']);
	}
	
	$pids = '';
	$comma = '';
	$query = $db->simple_select("posts", "pid", "{$where_sql}", $options);
	while($pid = $db->fetch_field($query, "pid"))
	{
		$pids .= $comma.$pid;
		$comma = ',';
	}

	$sid = md5(uniqid(microtime(), 1));
	$searcharray = array(
		"sid" => $db->escape_string($sid),
		"uid" => $mybb->user['uid'],
		"dateline" => TIME_NOW,
		"ipaddress" => $db->escape_binary($session->packedip),
		"threads" => '',
		"posts" => $db->escape_string($pids),
		"resulttype" => "posts",
		"querycache" => '',
		"keywords" => ''
	);
	$db->insert_query("searchlog", $searcharray);
	redirect("search.php?action=results&uid=".$tyl_uid."&sid=".$sid, $lang->redirect_searchresults);
}
else
{
	redirect("search.php");
}
