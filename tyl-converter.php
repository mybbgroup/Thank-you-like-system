<?php
/**
 * Filename   : tyl-converter.php
 * Author     : Dark Neo and martec; combined and refactored by Laird.
 * Description: Imports into the Thank You/Like System plugin either the
 *              thanks/likes of the SimpleLikes[1] plugin or the Thanks[2]
 *              plugin, or the reputations of the core reputation system, the
 *              latter of which supports migration from the MyLikes[3] plugin.
 *              [1] https://community.mybb.com/mods.php?action=view&pid=24
 *              [2] https://community.mybb.com/mods.php?action=view&pid=82
 *              [3] https://community.mybb.com/mods.php?action=view&pid=188.
 * Usage      : Install the Thank You/Like System plugin in both the filesystem
 *              and via the ACP Plugins page, then move this file into your MyBB
 *              board's root directory and browse to it, appending to its
 *              address in the address bar one of:
 *               ?from=thanks      (to convert from the Thanks plugin)
 *               ?from=simplelikes (to convert from the SimpleLikes plugin).
 *               ?from=reputation  (to convert from core reputations).
 *              If you do not append any of the above, the script will attempt
 *              to auto-detect the plugin from which you are migrating, but for
 *              safety, it will not auto-detect the core reputation system as a
 *              source, so in that case the ?from=reputation is required.
 *              After the conversion completes, remove this file from your
 *              forum's root directory.
 * Original Description[4]: Thanks SaeedGh & Thank You MyBB System Converter
 *              [4] Prior to martec's additions and Laird's modifications.
 */

define('IN_MYBB', 1);
require_once './inc/init.php';
define('THIS_SCRIPT', 'TYL_CONVERSION_SCRIPT');
ini_set('max_execution_time', 300);
$prefix = 'g33k_';
$sort_function = $do_conversion = false;
$from = $mybb->get_input('from', MyBB::INPUT_STRING);

if (!$db->table_exists($prefix.'thankyoulike_thankyoulike')) {
	echo '<span style="color: red;">Fatal error:</span> The Thank You/Like System plugin is not installed.';
	exit;
} else if ($from === 'thanks' || empty($from) && $db->table_exists('thx')) {
	if (!$db->table_exists('thx')) {
		echo '<span style="color: red;">Fatal error:</span> You selected via the "from" query parameter to convert from the Thanks plugin, however, its table was not found in the database.';
	} else {
		$do_conversion = true;
		$query = $db->simple_select('thx', 'pid, adduid as uid, uid as puid, time as dateline');
		$tyl_from_type = 'thanks';
		$from_plugin_name = 'the Thanks plugin';
	}
} else if ($from == 'simplelikes' || empty($from) && $db->table_exists('post_likes')) {
	if (!$db->table_exists('post_likes')) {
		echo '<span style="color: red;">Fatal error:</span> You selected via the "from" query parameter to convert from the SimpleLikes plugin, however, its table was not found in the database.';
	} else {
		$do_conversion = true;
		$query = $db->query('SELECT pl.post_id as pid, pl.user_id as uid, p.uid as puid, UNIX_TIMESTAMP(pl.created_at) as dateline FROM '.TABLE_PREFIX.'post_likes pl INNER JOIN '.TABLE_PREFIX.'posts p ON pl.post_id = p.pid');
		$tyl_from_type = 'likes';
		$from_plugin_name = 'the SimpleLikes plugin';
	}
} else if ($from === 'reputation') {
	$do_conversion = true;
	$query = $db->simple_select('reputation', 'pid, adduid as uid, uid as puid, dateline', 'reputation > 0 AND pid > 0');
	$tyl_from_type = 'reputations';
	$from_plugin_name = 'core reputations (compatible with the MyLikes plugin)';
	$sort_function = function(&$rows) {
		//https://stackoverflow.com/a/3233009
		# get a list of sort columns and their data to pass to array_multisort
		$sort = array();
		$sort['pid'] = $sort['uid'] = array();
		foreach ($rows as $k => $v) {
			$sort['pid'][$k] = $v['pid'];
			$sort['uid'][$k] = $v['uid'];
		}
		# sort by pid asc and then uid asc
		array_multisort($sort['pid'], SORT_ASC, $sort['uid'], SORT_ASC, $rows);
	};
} else {
	echo '<span style="color: red;">Fatal error:</span> a compatible thanks/likes plugin from which to convert thanks/likes was not detected.';
	exit;
}

if ($do_conversion) {
	if (empty($query)) {
		echo "<span style=\"color: orange;\">Warning:</span> no {$tyl_from_type} found in the database table for {$from_plugin_name}. Nothing to convert, so no conversion performed.";
	} else {
		$sel_rows = array();
		while ($row = $db->fetch_array($query)) {
			$sel_rows[] = $row;
		}
		$db->free_result($query);

		if ($sort_function) {
			$sort_function($sel_rows);
		}

		$prev_pid = $prev_uid = $batch = $total = 0;
		foreach ($sel_rows as $row) {
			if ($prev_pid != (int)$row['pid'] || $prev_uid != (int)$row['uid']) {
				$ins_rows[] = $row;
				$prev_pid = (int)$row['pid'];
				$prev_uid = (int)$row['uid'];
				$batch++;
				$total++;
			}
			if ($batch == 1000) {
				$db->insert_query_multiple($prefix.'thankyoulike_thankyoulike', $ins_rows);
				$ins_rows = array();
				$batch = 0;
				echo "Converted {$total} {$tyl_from_type} so far from {$from_plugin_name} into thank yous / likes for the Thank You/Like System plugin.<br/>";
			}
		}
		if ($batch > 0) {
			$db->insert_query_multiple($prefix.'thankyoulike_thankyoulike', $ins_rows);
		}
		echo "<span style=\"color: green;\">Done!</span> Converted {$total} {$tyl_from_type} in total from {$from_plugin_name} into thank yous / likes for the Thank You/Like System plugin.";
	}
}
