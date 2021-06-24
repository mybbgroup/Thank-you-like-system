<?php
/**
 * Filename   : tyl-converter.php
 * Author     : Dark Neo with modifications by Laird.
 * Description: Imports into the Thank You/Like System plugin the likes of
 *              either the SimpleLikes[1] plugin or the Thanks[2][3] plugin.
 *              [1] https://community.mybb.com/mods.php?action=view&pid=24
 *              [2] https://community.mybb.com/mods.php?action=view&pid=82
 * Usage      : Install the Thank You/Like System plugin in both the filesystem
 *              and via the ACP Plugins page, then move this file into your MyBB
 *              forum's root directory and browse to it. After the conversion
 *              completes, remove this file from your forum's root directory.
 * Original Description[3]: Thanks SaeedGh & Thank You MyBB System Converter
 *              [3] Prior to Laird's mods.
 */

define('IN_MYBB', 1);
require_once './inc/init.php';
define('THIS_SCRIPT', 'MULTI_SRC_TYL_CONVERSION_SCRIPT');
ini_set('max_execution_time', 300);
$prefix = 'g33k_';
$do_conversion = false;

if (!$db->table_exists($prefix.'thankyoulike_thankyoulike')) {
	echo '<span style="color: red;">Fatal error:</span> The Thank You/Like System plugin is not installed.';
	exit;
} else if ($db->table_exists('thx')) {
	$do_conversion = true;
	$query = $db->simple_select('thx', 'pid, adduid as uid, uid as puid, time as dateline');
	$tyl_from_type = 'thanks';
	$from_plugin_name = 'the Thanks plugin';
} else if ($db->table_exists('post_likes')) {
	$do_conversion = true;
	$query = $db->query('SELECT pl.post_id as pid, pl.user_id as uid, p.uid as puid, UNIX_TIMESTAMP(pl.created_at) as dateline FROM '.TABLE_PREFIX.'post_likes pl INNER JOIN '.TABLE_PREFIX.'posts p ON pl.post_id = p.pid');
	$tyl_from_type = 'likes';
	$from_plugin_name = 'the SimpleLikes plugin';
} else {
	echo '<span style="color: red;">Fatal error:</span> a compatible thanks/likes system from which to convert thanks/likes was not detected.';
	exit;
}

if ($do_conversion) {
	if (empty($query)) {
		echo "<span style=\"color: orange;\">Warning:</span> no {$tyl_from_type} found in the database table for {$from_plugin_name}. Nothing to convert, so no conversion performed.";
	} else {
		$batch = $total = 0;
		$rows = array();
		while ($row = $db->fetch_array($query)) {
			$rows[] = $row;
			$batch++;
			$total++;
			if ($batch == 1000) {
				$db->insert_query_multiple($prefix.'thankyoulike_thankyoulike', $rows);
				$rows = array();
				$batch = 0;
				echo "Converted {$total} {$tyl_from_type} from {$from_plugin_name} into thank yous / likes for the Thank You/Like System plugin so far.<br/>";
			}
		}
		if ($batch > 0) {
			$db->insert_query_multiple($prefix.'thankyoulike_thankyoulike', $rows);
		}
		echo "<span style=\"color: green;\">Done!</span> Converted {$total} {$tyl_from_type} from {$from_plugin_name} into thank yous / likes for the Thank You/Like System plugin in total.";
	}
}
