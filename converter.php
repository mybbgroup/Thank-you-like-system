<?php
/**
 * Author: Dark Neo
 * Plugin: Thank You/Like System
 * Version: 1.9.4
 * File: Thanks SaeedGh & Thank You MyBB System Converter
 */
define('IN_MYBB', 1);
require_once "./inc/init.php";
define('THX_CONVERSION_SCRIPT');
ini_set('max_execution_time', 300);
$batch = 0;
$total = 0;
$prefix = 'g33k_';
$thx = array();
	if($db->table_exists("thx") && $db->table_exists($prefix.'thankyoulike_thankyoulike'))
	{
		$query = $db->simple_select('thx', '*');
		while ($thanks = $db->fetch_array($query)) {
			$thx[] = array(
				'pid'	 	=> (int) $thanks['pid'],
				'uid'		=> (int) $thanks['adduid'],
				'puid'		=> (int) $thanks['uid'],
				'dateline'	=> (int) $thanks['time']
			);
			$batch++;
			$total++;
			if($batch == 1000) {
				$db->insert_query_multiple($prefix.'thankyoulike_thankyoulike', $thx);
				$thx = array();
				$batch = 0;
				echo "Converted {$total} from Thanks single system to Thank You/Like System<br/>";
			}
		}
		$db->insert_query_multiple($prefix.'thankyoulike_thankyoulike', $thx);
		echo "<span style=\"color: green;\">Done!!!</span><br />Converted {$total} from Thanks single system to Thank You/Like System<br/>";
	}
	
	else if($db->table_exists("post_likes") && $db->table_exists($prefix.'thankyoulike_thankyoulike'))
	{
		$query = $db->simple_select('post_likes', '*');
		while ($thanks = $db->fetch_array($query)) {
			$thx[] = array(
				'pid'	 	=> (int) $thanks['post_id'],
				'uid'		=> (int) $thanks['user_uid'],
			);
			$batch++;
			$total++;
			if($batch == 1000) {
				$db->insert_query_multiple($prefix.'thankyoulike_thankyoulike', $thx);
				$thx = array();
				$batch = 0;
				echo "Converted {$total} from Simple likes system to Thank You/Like System<br/>";
			}
		}
		$db->insert_query_multiple($prefix.'thankyoulike_thankyoulike', $thx);
		echo "<span style=\"color: green;\">Done!!!</span><br />Converted {$total} from Simple likes system to Thank You/Like System (There are some missing data in this version)<br/>";
	}
	
	else if(!$db->table_exists($prefix.'thankyoulike_thankyoulike'))
	{
		echo "<span style=\"color: red;\">Alert!!!</span><br />You have to install Thank You/Like System plugin before running this script!!!";
	}
	
	else
	{
		echo "<span style=\"color: red;\">Alert!!!</span>You have not an available suite of thanks system to be converted...";
	}
