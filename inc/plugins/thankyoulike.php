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

if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if(defined("IN_ADMINCP"))
{
	$plugins->add_hook('admin_formcontainer_output_row', 'thankyoulike_promotion_formcontainer_output_row');
	$plugins->add_hook('admin_user_group_promotions_edit_commit', 'thankyoulike_promotion_commit');
	$plugins->add_hook('admin_user_group_promotions_add_commit', 'thankyoulike_promotion_commit');
	$plugins->add_hook("admin_user_groups_edit_graph_tabs", "tyl_limits_usergroup_permission_tabs");
	$plugins->add_hook("admin_formcontainer_end", "tyl_limits_usergroup_permission");
	$plugins->add_hook("admin_user_groups_edit_commit", "tyl_limits_usergroup_permission_commit");
	$plugins->add_hook('admin_load', 'thankyoulike_admin_load');
	$plugins->add_hook("admin_user_users_delete_commit","thankyoulike_delete_user");
	$plugins->add_hook("admin_tools_recount_rebuild", "acp_tyl_do_recounting");
	$plugins->add_hook("admin_tools_recount_rebuild_output_list", "acp_tyl_recount_form");
	$plugins->add_hook("admin_config_settings_change","thankyoulike_settings_page");
	$plugins->add_hook("admin_page_output_footer","thankyoulike_settings_peeker");
}
else
{
	$plugins->add_hook("global_start", "thankyoulike_templatelist");
	$plugins->add_hook("postbit","thankyoulike_postbit");
	$plugins->add_hook("postbit_prev","thankyoulike_postbit_udetails");
	$plugins->add_hook("postbit_pm","thankyoulike_postbit_udetails");
	$plugins->add_hook("postbit_announcement","thankyoulike_postbit_udetails");
	$plugins->add_hook("member_profile_end","thankyoulike_memprofile");
	$plugins->add_hook("fetch_wol_activity_end", "thankyoulike_wol_activity");
	$plugins->add_hook("build_friendly_wol_location_end", "thankyoulike_friendly_wol_activity");
	$plugins->add_hook("class_moderation_delete_thread_start","thankyoulike_delete_thread");
	$plugins->add_hook("class_moderation_delete_post_start","thankyoulike_delete_post");
	$plugins->add_hook("class_moderation_merge_posts","thankyoulike_merge_posts");
	$plugins->add_hook("class_moderation_merge_threads","thankyoulike_merge_threads");
	$plugins->add_hook("class_moderation_split_posts","thankyoulike_split_posts");
	$plugins->add_hook('task_promotions', 'thankyoulike_promotion_task');
}


function thankyoulike_info()
{
	global $plugins_cache, $mybb, $db, $lang, $cache;
	$lang->load('config_thankyoulike');
	$prefix = 'g33k_thankyoulike_';
	$codename = 'thankyoulike';

$url_AT= '<a href="http://my-bb.ir" target="_blank">AliReza_Tofighi</a>';
$url_SP = '<a href="https://community.mybb.com/user-91011.html" target="_blank">SvePu</a>';
$url_E = '<a href="https://community.mybb.com/user-84065.html" target="_blank">Eldenroot</a>';
$url_DN = '<a href="https://community.mybb.com/user-51493.html" target="_blank">Dark Neo</a>';
$url_S = '<a href="https://github.com/Cu8eeeR/MyBB_Thank-you-like-plugin" target="_blank">GitHub</a>';

	$info = array(
		"name"		=> $db->escape_string($lang->tyl_info_title),
		"description"	=> $db->escape_string($lang->tyl_info_desc) . $lang->sprintf($lang->tyl_info_desc_url,$url_AT,$url_SP,$url_E,$url_DN,$url_S),
		"website"	=> "https://community.mybb.com/thread-169382.html",
		"author"	=> "- G33K -, Eldenroot, SvePu",
		"authorsite"	=> "https://community.mybb.com/thread-169382.html",
		"version"	=> "2.3.0",
		"codename"	=> "thankyoulikesystem",
		"compatibility"	=> "18*"
	);

	$info_desc = '';

	if(function_exists("myalerts_info")){
		$my_alerts_info = myalerts_info();
		$verify = $my_alerts_info['version'];
		if($verify >= 2.0){
			// Load cache data and compare if version is the same or not
				$myalerts_plugins = $cache->read('mybbstuff_myalerts_alert_types');

			if($myalerts_plugins['tyl']['code'] == 'tyl' && $myalerts_plugins['tyl']['enabled'] == 1){
				$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/success.png)\"><span style=\"color: green;\">".$db->escape_string($lang->tyl_info_desc_alerts_integrated)."</span></li></ul>";
			}
			else if(!$myalerts_plugins['tyl']['code'] == 'tyl' && $mybb->settings['g33k_thankyoulike_enabled']){
				$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/warning.png)\"><a href=\"index.php?module=config-plugins&amp;action=tyl_myalerts_integrate\" style=\"color: red;\">".$db->escape_string($lang->tyl_info_desc_alerts_integrate)."</a></li></ul>";
			}
			else{
				$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/error.png)\"><span style=\"color: red\">".$db->escape_string($lang->tyl_info_desc_alerts_error)."</span></li></ul>";
			}
		}
	}
	$result = $db->simple_select('settinggroups', 'gid', "name = '{$prefix}settings'", array('limit' => 1));
	$group = $db->fetch_array($result);
	if(!empty($group['gid']))
	{
		$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/custom.png)\"><a href=\"index.php?module=config-settings&action=change&gid=".$group['gid']."\">".$db->escape_string($lang->tyl_info_desc_configsettings)."</a></li></ul>";
	}

	if(is_array($plugins_cache) && is_array($plugins_cache['active']) && $plugins_cache['active'][$codename])
	{
		$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/default.png)\"><a href=\"index.php?module=tools-recount_rebuild\">".$db->escape_string($lang->tyl_info_desc_recount)."</a></li></ul>";
		$info_desc .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: right;" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="KCNAC5PE828X8">
<input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>';
	}

	if($info_desc != '')
	{
		$info['description'] = $info_desc.'<br />'.$info['description'];
	}

	return $info;
}

function thankyoulike_admin_load()
{
	global $page, $mybb;
	if($mybb->input['action'] == 'tyl_myalerts_integrate')
	{
		tyl_myalerts_integrate();
		exit;
	}
}

function tyl_myalerts_integrate()
{
	global $db, $cache;
	// Verify if MyAlerts exists and if is compatible with 1.8.x then add alert type
	if(function_exists("myalerts_info")){
		// Load MyAlerts info into an array
		$my_alerts_info = myalerts_info();
		// Set version info to a new var
		$verify = $my_alerts_info['version'];
		// If MyAlerts 2.0 or better then do this !!!
		if($verify >= "2.0.0"){
			// Load cache data and compare if version is the same or not
			$myalerts_plugins = $cache->read('mybbstuff_myalerts_alert_types');
			if($myalerts_plugins['tyl']['code'] != 'tyl'){
				// Adding alert type into db
				$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
					$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
					$alertType->setCode('tyl');
					$alertType->setEnabled(true);
				$alertTypeManager->add($alertType);
			}

			flash_message("MyAlerts and Thank You/Like System were integrated successfully!", 'success');
			admin_redirect('index.php?module=config-plugins');
		}
	}
}

function thankyoulike_install()
{
	global $mybb, $db, $cache, $lang;
	$lang->load('config_thankyoulike');
	$prefix = 'g33k_thankyoulike_';
	$info = thankyoulike_info();

	// Run preinstall cleanup
	tyl_preinstall_cleanup();

	if(!$db->field_exists('tyl_pnumtyls', 'posts'))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."posts ADD `tyl_pnumtyls` int(100) NOT NULL default '0'");
	}

	if(!$db->field_exists('tyl_tnumtyls', 'threads'))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."threads ADD `tyl_tnumtyls` int(100) NOT NULL default '0'");
	}

	if(!$db->field_exists('tyl_unumtyls', 'users'))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users ADD `tyl_unumtyls` int(100) NOT NULL default '0'");
	}

	if(!$db->field_exists('tyl_unumrcvtyls', 'users'))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users ADD `tyl_unumrcvtyls` int(100) NOT NULL default '0'");
	}

	if(!$db->field_exists('tyl_unumptyls', 'users'))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users ADD `tyl_unumptyls` int(100) NOT NULL default '0'");
	}

	if(!$db->table_exists($prefix.'thankyoulike'))
	{
		$db->query("CREATE TABLE ".TABLE_PREFIX.$prefix."thankyoulike (
				tlid int unsigned NOT NULL auto_increment,
				pid int unsigned NOT NULL default '0',
				uid int unsigned NOT NULL default '0',
				puid int unsigned NOT NULL default '0',
				dateline bigint(30) NOT NULL default '0',
				UNIQUE KEY pid (pid, uid),
				PRIMARY KEY (tlid)
				) ENGINE=MyISAM
				".$db->build_create_table_collation().";");
	}

	// Added puid field after v1.0 so check for that
	if($db->table_exists($prefix.'thankyoulike') && !$db->field_exists('puid', $prefix.'thankyoulike'))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX.$prefix."thankyoulike ADD `puid` int unsigned NOT NULL default '0' AFTER `uid`");
	}

	if(!$db->table_exists($prefix.'stats'))
	{
		$db->query("CREATE TABLE ".TABLE_PREFIX.$prefix."stats (
				title varchar(50) NOT NULL default '',
				value int unsigned NOT NULL default '0',
				UNIQUE KEY title (title),
				PRIMARY KEY(title)
				) ENGINE=MyISAM
				".$db->build_create_table_collation().";");
	}
	$options = array(
			"limit" => 1
		);
	$query = $db->simple_select($prefix."stats", "*", "title='total'", $options);
	$total = $db->fetch_array($query);

	if(!isset($total['title']))
	{
		$total_data = array(
			"title" => "total",
			"value" => 0
		);

		$db->insert_query($prefix."stats", $total_data);
	}
	// Add Thank You/Like Promotions Tables Fields
	if(!$db->field_exists("tylreceived", "promotions"))
	{
		$db->add_column("promotions", "tylreceived", "int NOT NULL default '0'");
	}
	if(!$db->field_exists("tylreceivedtype", "promotions"))
	{
		$db->add_column("promotions", "tylreceivedtype", "char(2) NOT NULL default ''");
	}
	if(!$db->field_exists("tylgiven", "promotions"))
	{
		$db->add_column("promotions", "tylgiven", "int NOT NULL default '0'");
	}
	if(!$db->field_exists("tylgiventype", "promotions"))
	{
		$db->add_column("promotions", "tylgiventype", "char(2) NOT NULL default ''");
	}

	// Add Thank You/Like Limits Tables Fields
	if(!$db->field_exists("tyl_limits_max", "usergroups"))
	{
		$db->add_column("usergroups", "tyl_limits_max", "int(10) NOT NULL DEFAULT '10'");
	}


	// Insert settings into the database
	$query = $db->query("SELECT disporder FROM ".TABLE_PREFIX."settinggroups ORDER BY `disporder` DESC LIMIT 1");
	$disporder = $db->fetch_field($query, 'disporder')+1;

	$setting_group = array(
		'name'			=>	$prefix.'settings',
		'title'			=>	$db->escape_string($lang->tyl_title),
		'description'	=>	$db->escape_string($lang->tyl_desc),
		'disporder'		=>	intval($disporder),
		'isdefault'		=>	0
	);
	$db->insert_query('settinggroups', $setting_group);
	$gid = $db->insert_id();

	$settings = array(
		'enabled'				=> array(
				'title'				=> $lang->tyl_enabled_title,
				'description'		=> $lang->tyl_enabled_desc,
				'optionscode'		=> 'onoff',
				'value'				=> '1'),
		'thankslike'			=> array(
				'title'				=> $lang->tyl_thankslike_title,
				'description'		=> $lang->tyl_thankslike_desc,
				'optionscode'		=> 'radio
thanks='.$lang->tyl_thankslike_op_1.'
like='.$lang->tyl_thankslike_op_2.'',
				'value'				=> 'thanks'),
		'firstall'					=> array(
				'title'				=> $lang->tyl_firstall_title,
				'description'		=> $lang->tyl_firstall_desc,
				'optionscode'		=> 'radio
first='.$lang->tyl_firstall_op_1.'
all='.$lang->tyl_firstall_op_2.'',
				'value'				=> 'first'),
		'firstalloverwrite'		=> array(
				'title'				=> $lang->tyl_firstalloverwrite_title,
				'description'		=> $lang->tyl_firstalloverwrite_desc,
				'optionscode'		=> 'forumselect',
				'value'				=> ''),
		'removing'				=> array(
				'title'				=> $lang->tyl_removing_title,
				'description'		=> $lang->tyl_removing_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0'),
		'tylownposts'			=> array(
				'title'				=> $lang->tyl_tylownposts_title,
				'description'		=> $lang->tyl_tylownposts_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0'),
		'remowntylfroms'			=> array(
				'title'				=> $lang->tyl_remowntylfroms_title,
				'description'		=> $lang->tyl_remowntylfroms_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0'),
		'remowntylfromc'			=> array(
				'title'				=> $lang->tyl_remowntylfromc_title,
				'description'		=> $lang->tyl_remowntylfromc_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0'),
		'reputation_add'			=> array(
				'title'				=> $lang->tyl_reputation_add_title,
				'description'		=> $lang->tyl_reputation_add_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0'),
		'reputation_add_reppoints'	=> array(
				'title'				=> $lang->tyl_reputation_add_reppoints_title,
				'description'		=> $lang->tyl_reputation_add_reppoints_desc,
				'optionscode'		=> 'numeric',
				'value'				=> '1'),
		'reputation_add_repcomment'	=> array(
				'title'				=> $lang->tyl_reputation_add_repcomment_title,
				'description'		=> $lang->tyl_reputation_add_repcomment_desc,
				'optionscode'		=> 'text',
				'value'				=> ''),
		'closedthreads'			=> array(
				'title'				=> $lang->tyl_closedthreads_title,
				'description'		=> $lang->tyl_closedthreads_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0'),
		'exclude'				=> array(
				'title'				=> $lang->tyl_exclude_title,
				'description'		=> $lang->tyl_exclude_desc,
				'optionscode'		=> 'forumselect',
				'value'				=> ''),
		'unameformat'			=> array(
				'title'				=> $lang->tyl_unameformat_title,
				'description'		=> $lang->tyl_unameformat_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '1'),
		'hideforgroups'			=> array(
				'title'				=> $lang->tyl_hideforgroups_title,
				'description'		=> $lang->tyl_hideforgroups_desc,
				'optionscode'		=> 'groupselect',
				'value'				=> '1,7'),
		'showdt'				=> array(
				'title'				=> $lang->tyl_showdt_title,
				'description'		=> $lang->tyl_showdt_desc,
				'optionscode'		=> 'radio
none='.$lang->tyl_showdt_op_1.'
nexttoname='.$lang->tyl_showdt_op_2.'
astitle='.$lang->tyl_showdt_op_3.'',
				'value'				=> 'astitle'),
		'dtformat'				=> array(
				'title'				=> $lang->tyl_dtformat_title,
				'description'		=> $lang->tyl_dtformat_desc,
				'optionscode'		=> 'text',
				'value'				=> 'm-d-Y'),
		'sortorder'				=> array(
				'title'				=> $lang->tyl_sortorder_title,
				'description'		=> $lang->tyl_sortorder_desc,
				'optionscode'		=> 'select
userasc='.$lang->tyl_sortorder_op_1.'
userdesc='.$lang->tyl_sortorder_op_2.'
dtasc='.$lang->tyl_sortorder_op_3.'
dtdesc='.$lang->tyl_sortorder_op_4.'',
				'value'				=> 'userasc'),
		'collapsible'			=> array(
				'title'				=> $lang->tyl_collapsible_title,
				'description'		=> $lang->tyl_collapsible_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '1'),
		'colldefault'			=> array(
				'title'				=> $lang->tyl_colldefault_title,
				'description'		=> $lang->tyl_colldefault_desc,
				'optionscode'		=> 'radio
open='.$lang->tyl_colldefault_op_1.'
closed='.$lang->tyl_colldefault_op_2.'',
				'value'				=> 'open'),
		'hidelistforgroups'			=> array(
				'title'				=> $lang->tyl_hidelistforgroups_title,
				'description'		=> $lang->tyl_hidelistforgroups_desc,
				'optionscode'		=> 'groupselect',
				'value'				=> ''),
		'displaygrowl'		=> array(
				'title'				=> $lang->tyl_displaygrowl_title,
				'description'		=> $lang->tyl_displaygrowl_desc,
				'optionscode'		=> 'onoff',
				'value'				=> '1'),
		'limits'		=> array(
				'title'				=> $lang->tyl_limits_title,
				'description'		=> $lang->tyl_limits_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0'),
		'highlight_popular_posts'		=> array(
				'title'				=> $lang->tyl_highlight_popular_posts_title,
				'description'		=> $lang->tyl_highlight_popular_posts_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0'),
		'highlight_popular_posts_count'		=> array(
				'title'				=> $lang->tyl_highlight_popular_posts_count_title,
				'description'		=> $lang->tyl_highlight_popular_posts_count_desc,
				'optionscode'		=> 'numeric',
				'value'				=> '0'),
		'show_memberprofile_box'	=> array(
				'title'				=> $lang->tyl_show_memberprofile_box_title,
				'description'		=> $lang->tyl_show_memberprofile_box_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '1'),
		'profile_box_post_cutoff'	=> array(
				'title'				=> $lang->tyl_profile_box_post_cutoff_title,
				'description'		=> $lang->tyl_profile_box_post_cutoff_desc,
				'optionscode'		=> 'numeric',
				'value'				=> '0'),
		'profile_box_post_allowhtml'	=> array(
				'title'				=> $lang->tyl_profile_box_post_allowhtml_title,
				'description'		=> $lang->tyl_profile_box_post_allowhtml_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0'),
		'profile_box_post_allowmycode'	=> array(
				'title'				=> $lang->tyl_profile_box_post_allowmycode_title,
				'description'		=> $lang->tyl_profile_box_post_allowmycode_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '1'),
		'profile_box_post_allowsmilies'	=> array(
				'title'				=> $lang->tyl_profile_box_post_allowsmilies_title,
				'description'		=> $lang->tyl_profile_box_post_allowsmilies_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '1'),
		'profile_box_post_allowimgcode'	=> array(
				'title'				=> $lang->tyl_profile_box_post_allowimgcode_title,
				'description'		=> $lang->tyl_profile_box_post_allowimgcode_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0'),
		'profile_box_post_allowvideocode'	=> array(
				'title'				=> $lang->tyl_profile_box_post_allowvideocode_title,
				'description'		=> $lang->tyl_profile_box_post_allowvideocode_desc,
				'optionscode'		=> 'yesno',
				'value'				=> '0')
	);

	$x = 1;
	foreach($settings as $name => $setting)
	{
		$insert_settings = array(
			'name' => $db->escape_string($prefix.$name),
			'title' => $db->escape_string($setting['title']),
			'description' => $db->escape_string($setting['description']),
			'optionscode' => $db->escape_string($setting['optionscode']),
			'value' => $db->escape_string($setting['value']),
			'disporder' => $x,
			'gid' => $gid,
			'isdefault' => 0
			);
		$db->insert_query('settings', $insert_settings);
		$x++;
	}

	rebuild_settings();


	// Insert Template elements
	$templateset = array(
		"prefix" => "thankyoulike",
		"title" => "Thank You/Like",
	);
	$db->insert_query("templategroups", $templateset);

	$tyl_templates = array(
		'thankyoulike_postbit'			=> "<div class=\"post_controls tyllist {\$unapproved_shade}\">
	{\$tyl_expcol}
	<span id=\"tyl_title_{\$post['pid']}\" style=\"{\$tyl_title_display}\">{\$lang->tyl_title}</span><span id=\"tyl_title_collapsed_{\$post['pid']}\" style=\"{\$tyl_title_display_collapsed}\">{\$lang->tyl_title_collapsed}</span><br />
	<span id=\"tyl_data_{\$post['pid']}\" style=\"{\$tyl_data_display}\">&nbsp;&nbsp;• {\$post['thankyoulike']}</span>
</div>",
		'thankyoulike_postbit_classic'		=> "<div class=\"post_controls tyllist_classic {\$unapproved_shade}\">
	{\$tyl_expcol}
	<span id=\"tyl_title_{\$post['pid']}\" style=\"{\$tyl_title_display}\">{\$lang->tyl_title}</span><span id=\"tyl_title_collapsed_{\$post['pid']}\" style=\"{\$tyl_title_display_collapsed}\">{\$lang->tyl_title_collapsed}</span><br />
	<span id=\"tyl_data_{\$post['pid']}\" style=\"{\$tyl_data_display}\">&nbsp;&nbsp;• {\$post['thankyoulike']}</span>
</div>",
		'thankyoulike_expcollapse'	=> "<a href=\"javascript:void(0)\" onclick=\"thankyoulike.tgl({\$post['pid']});return false;\" title=\"{\$tyl_showhide}\" id=\"tyl_a_expcol_{\$post['pid']}\"><img src=\"{\$theme['imgdir']}/{\$tyl_expcolimg}\" alt=\"{\$tyl_showhide}\" id=\"tyl_i_expcol_{\$post['pid']}\" /></a> ",
		'thankyoulike_button_add'	=> "<div id=\"tyl_btn_{\$post['pid']}\" class=\"postbit_buttons\"><a class=\"add_tyl_button\" href=\"thankyoulike.php?action=add&amp;pid={\$post['pid']}&amp;my_post_key={\$mybb->post_code}\" onclick=\"thankyoulike.add({\$post['pid']}, {\$post['tid']}); return false;\" title=\"{\$lang->add_tyl_button_title}\" id=\"tyl_a{\$post['pid']}\"><span id=\"tyl_i{\$post['pid']}\">{\$lang->add_tyl}</span></a></div>",
		'thankyoulike_button_del'	=> "<div id=\"tyl_btn_{\$post['pid']}\" class=\"postbit_buttons\"><a class=\"del_tyl_button\" href=\"thankyoulike.php?action=del&amp;pid={\$post['pid']}&amp;my_post_key={\$mybb->post_code}\" onclick=\"thankyoulike.del({\$post['pid']}, {\$post['tid']}); return false;\" title=\"{\$lang->del_tyl_button_title}\" id=\"tyl_a{\$post['pid']}\"><span id=\"tyl_i{\$post['pid']}\">{\$lang->del_tyl}</span></a></div>",
		'thankyoulike_users'		=> "<span class=\"smalltext\">{\$comma}</span><a href=\"{\$profile_link}\" class=\"smalltext\" {\$datedisplay_title}>{\$tyl_list}</a>{\$datedisplay_next}",
		'thankyoulike_postbit_author_user'		=> "{\$lang->tyl_rcvd}: {\$post['tyl_unumrtyls']}
<br />
{\$lang->tyl_given}: {\$post['tyl_unumtyls']}",
		'thankyoulike_member_profile'	=> "<tr>
	<td class=\"trow1\"><strong>{\$lang->tyl_total_tyls_rcvd}</strong></td>
	<td class=\"trow1\">{\$memprofile['tyl_unumrcvtyls']} ({\$tylrcvpd_percent_total})<br /><span class=\"smalltext\">(<a href=\"tylsearch.php?action=usertylforthreads&amp;uid={\$uid}\">{\$lang->tyl_find_threads_for}</a> &mdash; <a href=\"tylsearch.php?action=usertylforposts&amp;uid={\$uid}\">{\$lang->tyl_find_posts_for}</a>)</span></td>
</tr>
<tr>
	<td class=\"trow2\"><strong>{\$lang->tyl_total_tyls_given}</strong></td>
	<td class=\"trow2\">{\$memprofile['tyl_unumtyls']} ({\$tylpd_percent_total})<br /><span class=\"smalltext\">(<a href=\"tylsearch.php?action=usertylthreads&amp;uid={\$uid}\">{\$lang->tyl_find_threads}</a> &mdash; <a href=\"tylsearch.php?action=usertylposts&amp;uid={\$uid}\">{\$lang->tyl_find_posts}</a>)</span></td>
</tr>",
		'thankyoulike_member_profile_box'	=> "<table border=\"0\" cellspacing=\"{\$theme['borderwidth']}\" cellpadding=\"{\$theme['tablespace']}\" width=\"100%\" class=\"tborder\">
<tr>
<td colspan=\"2\" class=\"thead\"><strong>{\$lang->tyl_profile_box_thead}</strong></td>
</tr>
{\$tyl_profile_box_content}
</table>
<br />",
		'thankyoulike_member_profile_box_content'	=> "<tr>
<td class=\"tfoot\" width=\"80%\"><span class=\"smalltext\">{\$lang->tyl_profile_box_subject}</span></td>
<td class=\"tfoot\" width=\"20%\" align=\"center\"><span class=\"smalltext\">{\$lang->tyl_profile_box_number}</span></td>
</tr>
<tr>
<td class=\"trow1\"><strong>{\$memprofile['tylsubject']}</strong></td>
<td class=\"trow1\" align=\"center\"><strong>{\$memprofile['tylcount']}</strong></td>
</tr>
<tr>
<td class=\"trow1\" colspan=\"2\" style=\"padding: 0; border: 0;\">
<table border=\"0\" cellspacing=\"{\$theme['borderwidth']}\" cellpadding=\"{\$theme['tablespace']}\" width=\"100%\">
<tr>
<td class=\"tfoot\" width=\"50%\"><span class=\"smalltext\">{\$lang->tyl_profile_box_thread}</span></td>
<td class=\"tfoot\" width=\"50%\"><span class=\"smalltext\">{\$lang->tyl_profile_box_forum}</span></td>
</tr>
<tr>
<td class=\"trow1\">{\$memprofile['tylthreadname']}</td>
<td class=\"trow1\">{\$memprofile['tylforumname']}</td>
</tr>
</table>
</td>
</tr>
<tr>
<td class=\"tfoot\" colspan=\"2\"><span class=\"smalltext\">{\$lang->tyl_profile_box_message}</span></td>
</tr>
<tr>
<td class=\"trow1 scaleimages\" colspan=\"2\">{\$memprofile['tylmessage']}</td>
</tr>",
		'thankyoulike_member_profile_box_content_none'	=> "<tr>
<td class=\"trow1\" colspan=\"2\">{\$lang->tyl_profile_box_content_none}</td>
</tr>"
	);

	foreach($tyl_templates as $template_title => $template_data)
	{
		$insert_templates = array(
			'title' => $db->escape_string($template_title),
			'template' => $db->escape_string($template_data),
			'sid' => "-2",
			'version' => $info['intver'],
			'dateline' => TIME_NOW
			);
		$db->insert_query('templates', $insert_templates);
	}

	// css-class for thankyoulike
	$css = array(
	"name" => "thankyoulike.css",
	"tid" => 1,
	"attachedto" => "showthread.php",
	"stylesheet" => "div[id^=tyl_btn_] {
	display: inline-block;
}

a.add_tyl_button span{
	background-image: url(images/thankyoulike/tyl_add.png);
	background-repeat: no-repeat;
	font-weight: bold;
}

a.del_tyl_button span{
	background-image: url(images/thankyoulike/tyl_del.png);
	background-repeat: no-repeat;
	font-weight: normal;
}

.tyllist{
	background-color: #f5f5f5;
	border-top: 1px dotted #ccc;
	border-bottom: 1px dotted #ccc;
	padding: 2px 5px;
}

.tyllist_classic{
	background-color: #f5f5f5;
	border-top: 1px dotted #ccc;
	border-bottom: 1px dotted #ccc;
	padding: 2px 5px;
}

img[id^=tyl_i_expcol_]{
	vertical-align: bottom;
}

.popular_post{
	border: 2px solid;
	border-radius: 3px;
	border-color: rgba(112,202,47,0.5);
	background-color: rgba(139,195,74,0.3);
}",
	"cachefile" => $db->escape_string(str_replace('/', '', thankyoulike.css)),
	"lastmodified" => TIME_NOW
	);

	require_once MYBB_ADMIN_DIR."inc/functions_themes.php";

	$sid = $db->insert_query("themestylesheets", $css);
	$db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=".$sid), "sid = '".$sid."'", 1);

	$tids = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($tids))
	{
		update_theme_stylesheet_list($theme['tid']);
	}

	$cache->update_usergroups();
	$cache->update_forums();
	$cache->update_tasks();
}

function thankyoulike_is_installed()
{
	global $mybb, $db;
	$prefix = 'g33k_thankyoulike_';

	$result = $db->simple_select('templategroups', 'gid', "prefix in ('thankyoulike')", array('limit' => 1));
	$templategroup = $db->fetch_array($result);

	if($db->table_exists($prefix.'thankyoulike') && $db->table_exists($prefix.'stats') && $db->field_exists('tyl_pnumtyls', 'posts') && $db->field_exists('tyl_tnumtyls', 'threads') && $db->field_exists('tyl_unumtyls', 'users') && $db->field_exists('tyl_unumrcvtyls', 'users') && $db->field_exists('tyl_unumptyls', 'users') && !empty($templategroup['gid']))
	{
		return true;
	}
	return false;
}

function thankyoulike_activate()
{
	global $mybb, $db, $cache;
	$prefix = 'g33k_thankyoulike_';

	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets("showthread", "#".preg_quote('</head>')."#i", '<script type="text/javascript" src="{$mybb->settings[\'bburl\']}/jscripts/thankyoulike.min.js"></script>
<script type="text/javascript">
<!--
	var tylEnabled = "{$mybb->settings[\'g33k_thankyoulike_enabled\']}";
	var tylDisplayGrowl = "{$mybb->settings[\'g33k_thankyoulike_displaygrowl\']}";
	var tylCollapsible = "{$mybb->settings[\'g33k_thankyoulike_collapsible\']}";
	var tylUser = "{$mybb->user[\'uid\']}";
	var tylSend = "{$lang->tyl_send}";
	var tylRemove = "{$lang->tyl_remove}";
// -->
</script>
</head>');

	find_replace_templatesets("postbit","#".preg_quote('<div class="post {$unapproved_shade}" style="{$post_visibility}" id="post_{$post[\'pid\']}">')."#i","<div class=\"post{\$post['styleclass']} {\$unapproved_shade}\" style=\"{\$post_visibility}\" id=\"post_{\$post['pid']}\">");
	find_replace_templatesets("postbit_classic","#".preg_quote('<div class="post classic {$unapproved_shade}" style="{$post_visibility}" id="post_{$post[\'pid\']}">')."#i","<div class=\"post classic{\$post['styleclass']} {\$unapproved_shade}\" style=\"{\$post_visibility}\" id=\"post_{\$post['pid']}\">");
	find_replace_templatesets("postbit_classic","#".preg_quote('<div class="post_controls">')."#i","<div style=\"{\$post['tyl_display']}\" id=\"tyl_{\$post['pid']}\">{\$post['thankyoulike_data']}</div>\n<div class=\"post_controls\">");
	find_replace_templatesets("postbit","#".preg_quote('<div class="post_controls">')."#i","<div style=\"{\$post['tyl_display']}\" id=\"tyl_{\$post['pid']}\">{\$post['thankyoulike_data']}</div>\n<div class=\"post_controls\">");
	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'button_edit\']}')."#i", '{$post[\'button_tyl\']}{$post[\'button_edit\']}');
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'button_edit\']}')."#i", '{$post[\'button_tyl\']}{$post[\'button_edit\']}');
	find_replace_templatesets("postbit_author_user", "#".preg_quote('{$lang->postbit_threads} {$post[\'threadnum\']}<br />')."#i", '{$lang->postbit_threads} {$post[\'threadnum\']}<br />
	%%TYL_NUMTHANKEDLIKED%%<br />');
	if(!find_replace_templatesets("member_profile", '#{\$reputation}(\r?)\n#', "{\$tyl_memprofile}\n{\$reputation}\n"))
	{
		find_replace_templatesets("member_profile", '#{\$reputation}(\r?)\n#', "{\$tyl_memprofile}\n{\$reputation}\n");
	}
	find_replace_templatesets("member_profile", '#{\$modoptions}(\r?)\n#', "{\$tyl_profile_box}\n{\$modoptions}\n");

	// Verify if MyAlerts exists and if it is compatible with 1.8.x, then add alert type
	if(function_exists("myalerts_info")){
		// Load MyAlerts info into an array
		$my_alerts_info = myalerts_info();
		// Set version info to a new var
		$verify = $my_alerts_info['version'];
		// If MyAlerts 2.0 or better then do this!!!
		if($verify >= "2.0.0"){
		// Load cache data and compare if version is the same or not
		$myalerts_plugins = $cache->read('mybbstuff_myalerts_alert_types');
		if($myalerts_plugins['tyl']['code'] != 'tyl'){
			// Adding alert type into database
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
				$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
				$alertType->setCode('tyl');
				$alertType->setEnabled(true);
			$alertTypeManager->add($alertType);
			}
		}
	}
}

function thankyoulike_deactivate()
{
	global $db, $cache;
	$prefix = 'g33k_thankyoulike_';

	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets("showthread", "#".preg_quote('<script type="text/javascript" src="{$mybb->settings[\'bburl\']}/jscripts/thankyoulike.min.js"></script>
<script type="text/javascript">
<!--
	var tylEnabled = "{$mybb->settings[\'g33k_thankyoulike_enabled\']}";
	var tylDisplayGrowl = "{$mybb->settings[\'g33k_thankyoulike_displaygrowl\']}";
	var tylCollapsible = "{$mybb->settings[\'g33k_thankyoulike_collapsible\']}";
	var tylUser = "{$mybb->user[\'uid\']}";
	var tylSend = "{$lang->tyl_send}";
	var tylRemove = "{$lang->tyl_remove}";
// -->
</script>
</head>')."#i", '</head>', 0);

	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'styleclass\']}')."#i", '', 0);
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'styleclass\']}')."#i", '', 0);
	find_replace_templatesets("postbit", "#".preg_quote('<div style="{$post[\'tyl_display\']}" id="tyl_{$post[\'pid\']}">{$post[\'thankyoulike_data\']}</div>')."(\r?)\n#", '', 0);
	find_replace_templatesets("postbit_classic", "#".preg_quote('<div style="{$post[\'tyl_display\']}" id="tyl_{$post[\'pid\']}">{$post[\'thankyoulike_data\']}</div>')."(\r?)\n#", '', 0);
	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'button_tyl\']}')."#i", '', 0);
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'button_tyl\']}')."#i", '', 0);
	find_replace_templatesets("postbit_author_user", "#".preg_quote('
	%%TYL_NUMTHANKEDLIKED%%<br />')."#i", '', 0);
	find_replace_templatesets("member_profile", '#{\$tyl_memprofile}(\r?)\n#', "", 0);
	find_replace_templatesets("member_profile", '#{\$tyl_profile_box}(\r?)\n#', "", 0);

	if(function_exists("myalerts_info")){
		// Load MyAlerts info into an array
		$my_alerts_info = myalerts_info();
		// Set version info to a new var
		$verify = $my_alerts_info['version'];
		// If MyAlerts is v2.0 or better then do this!!!
		if($verify >= "2.0.0"){
			if($db->table_exists("alert_types")){
				$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();
				$alertTypeManager->deleteByCode('tyl');
			}
		}
	}
}

function thankyoulike_uninstall()
{
	global $mybb, $db, $cache;
	$prefix = 'g33k_thankyoulike_';

	if($mybb->request_method != 'post')
	{
		global $page, $lang;
		$lang->load('config_thankyoulike');
		$page->output_confirm_action('index.php?module=config-plugins&action=deactivate&uninstall=1&plugin=thankyoulike', $lang->tyl_uninstall_message, $lang->tyl_uninstall);
	}

	// Remove templates
	$db->delete_query("templates", "title LIKE 'thankyoulike_%'");
	$db->delete_query("templategroups", "prefix in ('thankyoulike')");

	// Remove CSS rules for g33k_thankyoulike
	require_once MYBB_ADMIN_DIR."inc/functions_themes.php";

	$db->delete_query("themestylesheets", "name = 'thankyoulike.css'");

	$query = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($query))
	{
		update_theme_stylesheet_list($theme['tid']);
	}

	// Remove settings
	$result = $db->simple_select('settinggroups', 'gid', "name = '{$prefix}settings'", array('limit' => 1));
	$group = $db->fetch_array($result);
	if(!empty($group['gid']))
	{
		$db->delete_query('settinggroups', "gid='{$group['gid']}'");
		$db->delete_query('settings', "gid='{$group['gid']}'");
		rebuild_settings();
	}

	// This part will remove the database tables
	if(!isset($mybb->input['no']))
	{
		if($db->field_exists('tyl_unumtyls', 'users'))
		{
			$db->query("ALTER TABLE ".TABLE_PREFIX."users DROP column `tyl_unumtyls`");
		}
		if($db->field_exists('tyl_unumrcvtyls', 'users'))
		{
			$db->query("ALTER TABLE ".TABLE_PREFIX."users DROP column `tyl_unumrcvtyls`");
		}
		if($db->field_exists('tyl_unumptyls', 'users'))
		{
			$db->query("ALTER TABLE ".TABLE_PREFIX."users DROP column `tyl_unumptyls`");
		}
		if($db->field_exists('tyl_pnumtyls', 'posts'))
		{
			$db->query("ALTER TABLE ".TABLE_PREFIX."posts DROP column `tyl_pnumtyls`");
		}
		if($db->field_exists('tyl_tnumtyls', 'threads'))
		{
			$db->query("ALTER TABLE ".TABLE_PREFIX."threads DROP column `tyl_tnumtyls`");
		}
		if($db->table_exists($prefix.'thankyoulike'))
		{
			$db->drop_table($prefix.'thankyoulike');
		}
		if($db->table_exists($prefix.'stats'))
		{
			$db->drop_table($prefix.'stats');
		}
	}
	// Remove Thank You/Like Promotions Tables Fields
		if($db->field_exists("tylreceived", "promotions"))
		{
			$query = $db->simple_select("promotions", "pid", "tylreceived>'0'");
			$pid = $db->fetch_array($query);
			if(!empty($pid['pid']))
			{
				$db->delete_query("promotions", "pid='{$pid['pid']}'");
			}
			$db->drop_column("promotions", "tylreceived");
		}
		if($db->field_exists("tylreceivedtype", "promotions"))
		{
			$db->drop_column("promotions", "tylreceivedtype");
		}
		if($db->field_exists("tylgiven", "promotions"))
		{
			$query = $db->simple_select("promotions", "pid", "tylgiven>'0'");
			$pid = $db->fetch_array($query);
			if(!empty($pid['pid']))
			{
				$db->delete_query("promotions", "pid='{$pid['pid']}'");
			}
			$db->drop_column("promotions", "tylgiven");
		}
		if($db->field_exists("tylgiventype", "promotions"))
		{
			$db->drop_column("promotions", "tylgiventype");
		}

		if($db->field_exists("tyl_limits_max", "usergroups"))
		{
			$db->drop_column("usergroups", "tyl_limits_max");
		}


	$cache->update_usergroups();
	$cache->update_forums();
	$cache->update_tasks();
}

function thankyoulike_templatelist()
{
	global $mybb, $cache, $lang, $code, $templatelist;
	$prefix = 'g33k_thankyoulike_';
	if ($mybb->settings[$prefix.'enabled'] == "1")
	{
		$lang->load('thankyoulike', false, true);
		if ($mybb->settings[$prefix.'thankslike'] == "like")
		{
			$prelang = $lang->tyl_like;
			$prelang1 = $lang->tyl_likes;

			$lang->tyl_alert = $lang->tyl_alert_like;
			$lang->myalerts_setting_tyl = $lang->myalerts_setting_tyl_like;
		}
		else if ($mybb->settings[$prefix.'thankslike'] == "thanks")
		{

			$prelang = $lang->tyl_thankyou;
			$prelang1 = $lang->tyl_thanks;

			$lang->tyl_alert = $lang->tyl_alert_thanks;
			$lang->myalerts_setting_tyl = $lang->myalerts_setting_tyl_thanks;
		}

		$lang->tyl_send = $lang->sprintf($lang->tyl_send, $prelang);
		$lang->tyl_remove = $lang->sprintf($lang->tyl_remove, $prelang);

		// Registering alert formatter
		if((function_exists('myalerts_is_activated')) && myalerts_is_activated() && $mybb->user['uid']){
			global $cache, $formatterManager;
			tyl_myalerts_formatter_load();
			// Load cache data and compare if version is the same or not
			$myalerts_plugins = $cache->read('mybbstuff_myalerts_alert_types');

			if($myalerts_plugins['tyl']['code'] == 'tyl' && $myalerts_plugins['tyl']['enabled'] == 1){
				if (class_exists('MybbStuff_MyAlerts_AlertFormatterManager') && class_exists('ThankyouAlertFormatter')) {
					$code = 'tyl';
					$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
					$formatterManager->registerFormatter(new ThankyouAlertFormatter($mybb, $lang, $code));
				}
			}
		}
		$template_list = '';
		if (THIS_SCRIPT == 'showthread.php')
		{
			$template_list = "thankyoulike_users,thankyoulike_postbit_author_user,thankyoulike_postbit,thankyoulike_postbit_classic,thankyoulike_expcollapse,thankyoulike_button_add,thankyoulike_button_del";
		}
		if (THIS_SCRIPT == 'member.php')
		{
			$template_list = "thankyoulike_member_profile,thankyoulike_member_profile_box,thankyoulike_member_profile_box_content,thankyoulike_member_profile_box_content_none";
		}
		if (THIS_SCRIPT == 'announcements.php')
		{
			$template_list = "thankyoulike_postbit_author_user";
		}
		if (THIS_SCRIPT == 'private.php')
		{
			$template_list = "thankyoulike_postbit_author_user";
		}
		if (isset($templatelist))
		{
			$templatelist .= ",".$template_list;
		}
		else
		{
			$templatelist = $template_list;
		}
	}
}

function thankyoulike_postbit(&$post)
{
	global $db, $mybb, $theme, $templates, $lang, $pids, $g33k_pcache;
	$prefix = 'g33k_thankyoulike_';

	$lang->load("thankyoulike");

	if ($mybb->settings[$prefix.'enabled'] == "1" && $mybb->settings[$prefix.'exclude'] != "-1")
	{
		// Check first if this post is in an excluded forum, if it is end right here.
		$forums = explode(",", $mybb->settings[$prefix.'exclude']);
		$excluded = false;
		foreach($forums as $forum)
		{
			if (trim($forum) == $post['fid'])
			{
				$excluded = true;
			}
		}

		if ($mybb->settings[$prefix.'remowntylfromc'] == 1)
		{
			$query = $db->simple_select($prefix."thankyoulike", "*", "uid=puid");
			while($result = $db->fetch_array($query))
			{
				if($result['uid'] == $post['uid'])
				{
					$post['tyl_unumtyls'] = $post['tyl_unumtyls'] - count($result['tlid']);
				}

				if($result['puid'] == $post['uid'])
				{
					$post['tyl_unumrcvtyls'] = $post['tyl_unumrcvtyls'] - count($result['tlid']);
				}
			}
		}

		// Setup stats in postbit
		if ($mybb->settings[$prefix.'thankslike'] == "like")
		{
			$lang->tyl_rcvd = $lang->tyl_likes_rcvd;
			$lang->tyl_given = $lang->tyl_likes_given;
			$post['tyl_unumrtyls'] = $lang->sprintf($lang->tyl_likes_rcvd_bit, my_number_format($post['tyl_unumrcvtyls']), my_number_format($post['tyl_unumptyls']));
			$post['tyl_unumtyls'] = my_number_format($post['tyl_unumtyls']);

			eval("\$tyl_thankslikes = \"".$templates->get("thankyoulike_postbit_author_user", 1, 0)."\";");
		}
		else if ($mybb->settings[$prefix.'thankslike'] == "thanks")
		{
			$lang->tyl_rcvd = $lang->tyl_thanks_rcvd;
			$lang->tyl_given = $lang->tyl_thanks_given;
			$post['tyl_unumrtyls'] = $lang->sprintf($lang->tyl_thanks_rcvd_bit, my_number_format($post['tyl_unumrcvtyls']), my_number_format($post['tyl_unumptyls']));
			$post['tyl_unumtyls'] = my_number_format($post['tyl_unumtyls']);

			eval("\$tyl_thankslikes = \"".$templates->get("thankyoulike_postbit_author_user", 1, 0)."\";");
		}
		// Setup stats in postbit
		$post['user_details'] = preg_replace("#".preg_quote('%%TYL_NUMTHANKEDLIKED%%')."#i", $tyl_thankslikes, $post['user_details']);

		if ($excluded)
		{
			// We're in an excluded forum, end right here
			return $post;
		}

		// Get all the thank you/like data for all the posts on this thread
		// Check first is it already fetched/cached?
		if(!is_array($g33k_pcache))
		{
			$g33k_pcache = array();
			// Use pids if $pids are there, otherwise use $post['pid'] as we're probably in threaded view
			if($pids != '')
			{
				$g33k_pids = 'tyl.'.trim($pids);
			}
			else
			{
				$g33k_pids = "tyl.pid='".$post['pid']."'";
			}

			// Set retrieve order
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

			$query = $db->query("
			SELECT u.username, u.usergroup, u.displaygroup, tyl.*
			FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
			LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=tyl.uid)
			WHERE ".$g33k_pids."
			".$order."");

			while($t = $db->fetch_array($query))
			{
				$g33k_pcache[$t['pid']][] = $t;
			}
		}

		$tyls = '';
		$comma = '';
		$tyled = 0;
		$count = 0;
		if(isset($g33k_pcache[$post['pid']]))
		{
			foreach($g33k_pcache[$post['pid']] AS $tyl)
			{
				$profile_link = get_profile_link($tyl['uid']);
				$username = htmlspecialchars_uni($post['username']);
				$profilelink = $username;
				// Format username... or not
				$tyl_list = $mybb->settings[$prefix.'unameformat'] == "1" ? format_name($tyl['username'], $tyl['usergroup'], $tyl['displaygroup']) : $tyl['username'];
				$datedisplay_next = $mybb->settings[$prefix.'showdt'] == "nexttoname" ? "<span class='smalltext'> (".date($mybb->settings[$prefix.'dtformat'], $tyl['dateline']).")</span>" : "";
				$datedisplay_title = $mybb->settings[$prefix.'showdt'] == "astitle" ? "title='".date($mybb->settings[$prefix.'dtformat'], $tyl['dateline'])."'" : "";
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
			$lang->add_tyl_button_title = $lang->add_l_button_title;
			$lang->del_tyl_button_title = $lang->del_l_button_title;
			if($mybb->settings[$prefix.'unameformat'] == "1"){
			$lang->tyl_title = $lang->sprintf($lang->tyl_title_l, $count, $tyl_user, $tyl_like, $post['profilelink']);
			$lang->tyl_title_collapsed = $lang->sprintf($lang->tyl_title_collapsed_l, $count, $tyl_user, $tyl_like, $post['profilelink']);
			}
			else{
			$lang->tyl_title = $lang->sprintf($lang->tyl_title_l, $count, $tyl_user, $tyl_like, $profilelink);
			$lang->tyl_title_collapsed = $lang->sprintf($lang->tyl_title_collapsed_l, $count, $tyl_user, $tyl_like, $profilelink);
			}
		}
		else if ($mybb->settings[$prefix.'thankslike'] == "thanks")
		{
			$pre = "ty";
			$lang->add_tyl = $lang->add_ty;
			$lang->del_tyl = $lang->del_ty;
			$lang->add_tyl_button_title = $lang->add_ty_button_title;
			$lang->del_tyl_button_title = $lang->del_ty_button_title;
			if($mybb->settings[$prefix.'unameformat'] == "1"){
			$lang->tyl_title = $lang->sprintf($lang->tyl_title_ty, $count, $tyl_user, $tyl_say, $post['profilelink']);
			$lang->tyl_title_collapsed = $lang->sprintf($lang->tyl_title_collapsed_ty, $count, $tyl_user, $tyl_say, $post['profilelink']);
			}
			else{
			$lang->tyl_title = $lang->sprintf($lang->tyl_title_ty, $count, $tyl_user, $tyl_say, $profilelink);
			$lang->tyl_title_collapsed = $lang->sprintf($lang->tyl_title_collapsed_ty, $count, $tyl_user, $tyl_say, $profilelink);
			}
		}
		// Setup the collapsible elements
		if ($mybb->settings[$prefix.'collapsible'] == "1" && $mybb->settings[$prefix.'colldefault'] == "closed")
		{
			$tyl_title_display = "display: none;";
			$tyl_title_display_collapsed = "";
			$tyl_data_display = "display: none;";
			$tyl_expcolimg = "collapse_collapsed.png";
			$tyl_showhide = "[+]";
			eval("\$tyl_expcol = \"".$templates->get("thankyoulike_expcollapse", 1, 0)."\";");
		}
		else if ($mybb->settings[$prefix.'collapsible'] == "1" && $mybb->settings[$prefix.'colldefault'] == "open")
		{
			$tyl_title_display = "";
			$tyl_title_display_collapsed = "display: none;";
			$tyl_data_display = "";
			$tyl_expcolimg = "collapse.png";
			$tyl_showhide = "[-]";
			eval("\$tyl_expcol = \"".$templates->get("thankyoulike_expcollapse", 1, 0)."\";");
		}
		else
		{
			$tyl_title_display = "";
			$tyl_title_display_collapsed = "display: none;";
			$tyl_data_display = "";
			$tyl_expcolimg = "";
			$tyl_expcol = "";
			$tyl_showhide = "";
			$lang->tyl_title_collapsed = "";
		}

		// Setup stats in postbit
		$post['user_details'] = preg_replace("#".preg_quote('%%TYL_NUMTHANKEDLIKED%%')."#i", $tyl_thankslikes, $post['user_details']);

		// Determine whether we're showing tyl for this post:
		$thread = get_thread($post['tid']);
		$tyluserid = $mybb->settings[$prefix.'tylownposts'] == "1" ? "-1" : $mybb->user['uid'];

		if(($tyled && $mybb->settings[$prefix.'removing'] != "1") || (!is_moderator($post['fid'], "caneditposts") && $thread['closed'] == 1 && $mybb->settings[$prefix.'closedthreads'] != "1") || $post['uid'] == $tyluserid || is_member($mybb->settings[$prefix.'hideforgroups']) || $mybb->settings[$prefix.'hideforgroups'] == "-1")
		{
			// Show no button for poster or user who has already thanked/liked and disabled removing.
			$post['button_tyl'] = '';
		}
		else if($tyled && $mybb->settings[$prefix.'removing'] == "1" && (($mybb->settings[$prefix.'firstall'] == "first" && $thread['firstpost'] == $post['pid']) || $mybb->settings[$prefix.'firstall'] == "all"))
		{
			// Show remove button if removing already thanked/liked and removing enabled and is either the first post in thread if setting is for first or setting is all
			eval("\$post['button_tyl'] = \"".$templates->get("thankyoulike_button_del")."\";");
		}
		else if(($mybb->settings[$prefix.'firstall'] == "first" && $thread['firstpost'] == $post['pid']) || $mybb->settings[$prefix.'firstall'] == "all")
		{
			if ((my_strpos($mybb->settings[$prefix.'firstalloverwrite'], $post['fid']) !== false || $mybb->settings[$prefix.'firstalloverwrite'] == "-1") && $thread['firstpost'] != $post['pid'])
			{
				// If all posts enabled, then show list and button...
				eval("\$post['button_tyl'] = \"".$templates->get("thankyoulike_button_add")."\";");
			}
			else
			{
				// Same as above but show add button, to first post only, both have to been on that way or won't work...
				eval("\$post['button_tyl'] = \"".$templates->get("thankyoulike_button_add")."\";");
			}
		}

		if($count>0 && ((($mybb->settings[$prefix.'firstall'] == "first" && $thread['firstpost'] == $post['pid']) || $mybb->settings[$prefix.'firstall'] == "all") && !is_member($mybb->settings[$prefix.'hidelistforgroups']) && $mybb->settings[$prefix.'hidelistforgroups'] != "-1"))
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
			$post['thankyoulike_data'] = $thankyoulike;
		}
		else
		{
			$lang->tyl_title = '';
			$lang->tyl_title_collapsed = '';
			$post['tyl_display'] = "display: none;";
			if($mybb->settings['postlayout'] == "classic")
			{
				eval("\$thankyoulike = \"".$templates->get("thankyoulike_postbit_classic")."\";");
			}
			else
			{
				eval("\$thankyoulike = \"".$templates->get("thankyoulike_postbit")."\";");
			}
			$post['thankyoulike_data'] = $thankyoulike;
		}
	}
	else
	{
		// Remove stats in postbit
		$post['user_details'] = preg_replace("#".preg_quote('%%TYL_NUMTHANKEDLIKED%%<br />')."#i", "", $post['user_details']);
	}
	$post['styleclass'] = '';
	if($mybb->settings[$prefix.'highlight_popular_posts'] == 1 && $mybb->settings[$prefix.'highlight_popular_posts_count'] > 0)
	{
		if($post['tyl_pnumtyls'] >= $mybb->settings[$prefix.'highlight_popular_posts_count'])
		{
			$post['styleclass'] = ' popular_post';
		}
	}

	return $post;
}

function thankyoulike_postbit_udetails(&$post)
{
	global $mybb, $db, $templates, $lang;
	$prefix = 'g33k_thankyoulike_';
	$lang->load("thankyoulike");

	if ($mybb->settings[$prefix.'enabled'] == "1")
	{
		if ($mybb->settings[$prefix.'remowntylfromc'] == 1)
		{
			$query = $db->simple_select($prefix."thankyoulike", "COUNT(tlid) AS owntyluser", "uid='".$post['uid']."' AND uid=puid");
			$owntyluser = $db->fetch_field($query, 'owntyluser');
			if($owntyluser)
			{
				$post['tyl_unumtyls'] = $post['tyl_unumtyls'] - $owntyluser;
				$post['tyl_unumrcvtyls'] = $post['tyl_unumrcvtyls'] - $owntyluser;
			}
			$query1 = $db->query("SELECT
				COUNT(pid) AS owntylposts
				FROM ".TABLE_PREFIX.$prefix."thankyoulike
				WHERE uid='".$post['uid']."' AND uid=puid AND pid NOT IN (
					SELECT pid FROM ".TABLE_PREFIX.$prefix."thankyoulike
					WHERE puid='".$post['uid']."'
					GROUP BY pid
					HAVING COUNT(pid) > 1
					)
				");
			$owntylposts = $db->fetch_field($query1, 'owntylposts');
			if($owntylposts)
			{
				$post['tyl_unumptyls'] = $post['tyl_unumptyls'] - $owntylposts;
			}
		}

		if ($mybb->settings[$prefix.'thankslike'] == "like")
		{
			$lang->tyl_rcvd = $lang->tyl_likes_rcvd;
			$lang->tyl_given = $lang->tyl_likes_given;
			$post['tyl_unumrtyls'] = $lang->sprintf($lang->tyl_likes_rcvd_bit, my_number_format($post['tyl_unumrcvtyls']), my_number_format($post['tyl_unumptyls']));
			$post['tyl_unumtyls'] = my_number_format($post['tyl_unumtyls']);

			eval("\$tyl_thankslikes = \"".$templates->get("thankyoulike_postbit_author_user", 1, 0)."\";");
		}
		else if ($mybb->settings[$prefix.'thankslike'] == "thanks")
		{
			$lang->tyl_rcvd = $lang->tyl_thanks_rcvd;
			$lang->tyl_given = $lang->tyl_thanks_given;
			$post['tyl_unumrtyls'] = $lang->sprintf($lang->tyl_thanks_rcvd_bit, my_number_format($post['tyl_unumrcvtyls']), my_number_format($post['tyl_unumptyls']));
			$post['tyl_unumtyls'] = my_number_format($post['tyl_unumtyls']);

			eval("\$tyl_thankslikes = \"".$templates->get("thankyoulike_postbit_author_user", 1, 0)."\";");
		}
		// Setup stats in postbit
		$post['user_details'] = preg_replace("#".preg_quote('%%TYL_NUMTHANKEDLIKED%%')."#i", $tyl_thankslikes, $post['user_details']);
	}
	else
	{
		// Remove stats in postbit
		$post['user_details'] = preg_replace("#".preg_quote('%%TYL_NUMTHANKEDLIKED%%<br />')."#i", "", $post['user_details']);
	}
	return $post;
}

// Sending the alert to database
function tyl_recordAlertThankyou()
{
	global $db, $lang, $mybb, $alert, $post, $prefix;
	$prefix = 'g33k_thankyoulike_';
	$lang->load("thankyoulike", false, true);

	if(!$mybb->settings[$prefix.'enabled'] == "1")
	{
		return false;
	}

	$uid = (int)$post['uid'];
	$tid = (int)$post['tid'];
	$pid = (int)$post['pid'];
	$subject = htmlspecialchars_uni($post['subject']);
	$fid = (int)$post['fid'];

	$alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('tyl');

	if(isset($alertType) && $alertType->getEnabled()){
		 // Check if already alerted
		$query = $db->simple_select(
			'alerts',
			'id',
			'object_id = ' .$pid . ' AND uid = ' . $uid . ' AND unread = 1 AND alert_type_id = ' . $alertType->getId() . ''
		);

		if ($db->num_rows($query) == 0) {
			$alert = new MybbStuff_MyAlerts_Entity_Alert($uid, $alertType, $pid, $mybb->user['uid']);
					$alert->setExtraDetails(
					array(
						'tid'		=> $tid,
						'pid'		=> $pid,
						't_subject'		=> $subject,
						'fid'		=> $fid
					));
			MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
		}
	}
}

 // Alert formatter for my custom alerts
function tyl_myalerts_formatter_load(){
	global $mybb, $prefix;
	$prefix = 'g33k_thankyoulike_';

	if ($mybb->settings[$prefix.'enabled'] == "1")
	{
	class ThankyouAlertFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter{
		public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
		{
			$alertContent = $alert->getExtraDetails();
			$postLink = $this->buildShowLink($alert);
			return $this->lang->sprintf(
				$this->lang->tyl_alert,
				$outputAlert['from_user'],
				$alertContent['t_subject']
			);
		}

		public function init()
		{
			if (!$this->lang->thankyoulike) {
				$this->lang->load('thankyoulike');
			}
		}

		public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
		{
		$alertContent = $alert->getExtraDetails();
		$postLink = $this->mybb->settings['bburl'] . '/' . get_post_link((int)$alertContent['pid'], (int)$alertContent['tid']).'#pid'.(int)$alertContent['pid'];

		return $postLink;
		}
	}
	}
}

function thankyoulike_memprofile()
{
	global $db, $mybb, $lang, $memprofile, $templates, $tyl_memprofile, $uid;
	$prefix = 'g33k_thankyoulike_';

	$lang->load("thankyoulike");

	if ($mybb->settings[$prefix.'enabled'] == "1")
	{
		if ($mybb->settings[$prefix.'thankslike'] == "like")
		{
			$lang->tyl_total_tyls_rcvd = $lang->tyl_total_likes_rcvd;
			$lang->tyl_total_tyls_given = $lang->tyl_total_likes_given;
			$lang->tyl_find_threads = $lang->tyl_find_l_threads;
			$lang->tyl_find_posts = $lang->tyl_find_l_posts;
			$lang->tyl_find_threads_for = $lang->tyl_find_l_threads_for;
			$lang->tyl_find_posts_for = $lang->tyl_find_l_posts_for;
			$tyl_thankslikes = $lang->tyl_likes;
		}
		else if ($mybb->settings[$prefix.'thankslike'] == "thanks")
		{
			$lang->tyl_total_tyls_rcvd = $lang->tyl_total_thanks_rcvd;
			$lang->tyl_total_tyls_given = $lang->tyl_total_thanks_given;
			$lang->tyl_find_threads = $lang->tyl_find_ty_threads;
			$lang->tyl_find_posts = $lang->tyl_find_ty_posts;
			$lang->tyl_find_threads_for = $lang->tyl_find_ty_threads_for;
			$lang->tyl_find_posts_for = $lang->tyl_find_ty_posts_for;
			$tyl_thankslikes = $lang->tyl_thanks;
		}

		if ($mybb->settings[$prefix.'remowntylfromc'] == 1)
		{
			$query = $db->simple_select($prefix."thankyoulike", "COUNT(tlid) AS owntyl_user", "uid='".$memprofile['uid']."' AND uid=puid");
			$owntyluser = $db->fetch_field($query, 'owntyl_user');
			if($owntyluser)
			{
				$memprofile['tyl_unumtyls'] = $memprofile['tyl_unumtyls'] - $owntyluser;
				$memprofile['tyl_unumrcvtyls'] = $memprofile['tyl_unumrcvtyls'] - $owntyluser;
			}
		}

		$daysreg = (TIME_NOW - $memprofile['regdate']) / (24*3600);
		$tylpd = $memprofile['tyl_unumtyls'] / $daysreg;
		$tylpd = round($tylpd, 2);
		if($tylpd > $memprofile['tyl_unumtyls'])
		{
			$tylpd = $memprofile['tyl_unumtyls'];
		}
		$tylrcvpd = $memprofile['tyl_unumrcvtyls'] / $daysreg;
		$tylrcvpd = round($tylrcvpd, 2);
		if($tylrcvpd > $memprofile['tyl_unumrcvtyls'])
		{
			$tylrcvpd = $memprofile['tyl_unumrcvtyls'];
		}

		// Get total tyl and percentage
		$query1 = $db->query("SELECT SUM(tyl_unumtyls) as totalgiv, SUM(tyl_unumrcvtyls) as totalrcv FROM ".TABLE_PREFIX."users");
		if($total = $db->fetch_array($query1))
		{
			$totalgiv = (int)$total['totalgiv'];
			$totalrcv = (int)$total['totalrcv'];
		}

		if($mybb->settings[$prefix.'remowntylfromc'] == 1)
		{
			$query = $db->simple_select($prefix."thankyoulike", "COUNT(tlid) AS owntyl_total", "uid=puid");
			$owntyltotal = $db->fetch_field($query, 'owntyl_total');
			if($owntyltotal)
			{
				$totalgiv = $totalgiv - $owntyltotal;
				$totalrcv = $totalrcv - $owntyltotal;
			}
		}

		if($totalgiv > 0)
		{
			$percent = $memprofile['tyl_unumtyls']*100/$totalgiv;
			$percent = round($percent, 2);
		}
		else
		{
			$percent = "0";
		}

		if($totalrcv > 0)
		{
			$percent_rcv = $memprofile['tyl_unumrcvtyls']*100/$totalrcv;
			$percent_rcv = round($percent_rcv, 2);
		}
		else
		{
			$percent_rcv = "0";
		}

		if($percent > 100)
		{
			$percent = 100;
		}
		if($percent_rcv > 100)
		{
			$percent_rcv = 100;
		}
		$memprofile['tyl_unumtyls'] = my_number_format($memprofile['tyl_unumtyls']);
		$memprofile['tyl_unumrcvtyls'] = my_number_format($memprofile['tyl_unumrcvtyls']);
		$tylpd_percent_total = $lang->sprintf($lang->tyl_tylpd_percent_total, my_number_format($tylpd), $percent, $totalgiv);
		$tylrcvpd_percent_total = $lang->sprintf($lang->tyl_tylpd_percent_total, my_number_format($tylrcvpd), $percent_rcv, $totalrcv);
		eval("\$tyl_memprofile = \"".$templates->get("thankyoulike_member_profile")."\";");

		// Member Profile Box Start
		if($mybb->settings[$prefix.'show_memberprofile_box'] != 0)
		{
			global $theme, $tyl_profile_box, $tyl_profile_box_content;

			$tyl_profile_box = $tyl_profile_box_content = "";
			if($mybb->settings[$prefix.'thankslike'] == "like")
			{
				$lang->tyl_profile_box_thead = $lang->sprintf($lang->tyl_profile_box_thead, $memprofile['username'], $lang->tyl_liked);
				$lang->tyl_profile_box_number = $lang->sprintf($lang->tyl_profile_box_number, $lang->tyl_likes);
				$lang->tyl_profile_box_content_none = $lang->sprintf($lang->tyl_profile_box_content_none, $memprofile['username'], $lang->tyl_liked_sm);
			}
			elseif($mybb->settings[$prefix.'thankslike'] == "thanks")
			{
				$lang->tyl_profile_box_thead = $lang->sprintf($lang->tyl_profile_box_thead, $memprofile['username'], $lang->tyl_thanked);
				$lang->tyl_profile_box_number = $lang->sprintf($lang->tyl_profile_box_number, $lang->tyl_thanks);
				$lang->tyl_profile_box_content_none = $lang->sprintf($lang->tyl_profile_box_content_none, $memprofile['username'], $lang->tyl_thanked_sm);
			}

			$unviewwhere = '';
			$unviewable = get_unviewable_forums(true);
			if($unviewable)
			{
				$unviewwhere = " AND p.fid NOT IN ($unviewable)";
			}
			$inactive = get_inactive_forums();
			if($inactive)
			{
				$unviewwhere .= " AND p.fid NOT IN ($inactive)";
			}

			$query = $db->query("
					SELECT l.pid, count( * ) AS tylcount, p.subject, p.username, p.message, p.tid, p.fid
					FROM ".TABLE_PREFIX."g33k_thankyoulike_thankyoulike l
					LEFT JOIN ".TABLE_PREFIX."posts p ON (l.pid=p.pid)
					WHERE p.visible='1' AND l.puid = {$memprofile['uid']}{$unviewwhere}
					GROUP BY l.pid
					ORDER BY tylcount DESC, l.pid ASC
					LIMIT 0,1"
				);
			if($post = $db->fetch_array($query))
			{
				global $parser;
				if(!$parser)
				{
					require_once MYBB_ROOT."inc/class_parser.php";
					$parser = new postParser;
				}

				$parser_options = array(
					"allow_html" => (int)$mybb->settings[$prefix.'profile_box_post_allowhtml'],
					"allow_mycode" => (int)$mybb->settings[$prefix.'profile_box_post_allowmycode'],
					"allow_smilies" => (int)$mybb->settings[$prefix.'profile_box_post_allowsmilies'],
					"allow_imgcode" => (int)$mybb->settings[$prefix.'profile_box_post_allowimgcode'],
					"allow_videocode" => (int)$mybb->settings[$prefix.'profile_box_post_allowvideocode'],
					"nofollow_on" => 1,
					"filter_badwords" => 1
				);

				$postlink = get_post_link($post['pid'], $post['tid'])."#pid".$post['pid'];

				$thread = get_thread($post['tid']);
				$threadlink = get_thread_link($post['tid']);

				$forum = get_forum($post['fid']);
				$forumlink = get_forum_link($post['fid']);

				$memprofile['tylsubject'] = "<a href=\"{$postlink}\"><span>{$parser->parse_badwords($post['subject'])}</span></a>";

				$memprofile['tylcount'] = (int)$post['tylcount'];

				if($mybb->settings[$prefix.'profile_box_post_cutoff'] > 0 && my_strlen($post['message']) > $mybb->settings[$prefix.'profile_box_post_cutoff'])
				{
					if($mybb->settings[$prefix.'profile_box_post_cutoff'] < 4)
					{
						$mybb->settings[$prefix.'profile_box_post_cutoff'] = 4;
					}
					$post['message'] = my_substr($post['message'], 0, $mybb->settings[$prefix.'profile_box_post_cutoff']-3, 0)."...";
				}
				$memprofile['tylmessage'] = $parser->parse_message($post['message'], $parser_options);

				$memprofile['tylthreadname'] = "<a href=\"{$threadlink}\"><span>{$parser->parse_badwords($thread['subject'])}</span></a>";
				$memprofile['tylforumname'] = "<a href=\"{$forumlink}\"><span>{$parser->parse_badwords($forum['name'])}</span></a>";

				eval("\$tyl_profile_box_content = \"".$templates->get("thankyoulike_member_profile_box_content")."\";");
			}
			else
			{
				eval("\$tyl_profile_box_content = \"".$templates->get("thankyoulike_member_profile_box_content_none")."\";");
			}

			eval("\$tyl_profile_box = \"".$templates->get("thankyoulike_member_profile_box")."\";");
		}
		// Member Profile Box End
	}
}

function thankyoulike_delete_thread($tid)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	$thread = get_thread($tid);

	// Only delete if there are any tyls
	if($thread['tyl_tnumtyls'] != 0)
	{
		// Find all tyl data for this tid
		$query = $db->query("
			SELECT tyl.*
			FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
			LEFT JOIN ".TABLE_PREFIX."posts p ON (p.pid=tyl.pid)
			WHERE p.tid='{$tid}'
		");
		$tlids = array();
		$user_tyls = array();
		$user_prcvtyls = array();
		while($tyl_post = $db->fetch_array($query))
		{
			$tlids[] = $tyl_post['tlid'];

			// Count # of posts and # of thanks received for every post to be subtracted
			if($user_prcvtyls[$tyl_post['puid']][$tyl_post['pid']])
			{
				$user_prcvtyls[$tyl_post['puid']][$tyl_post['pid']]--;
			}
			else
			{
				$user_prcvtyls[$tyl_post['puid']][$tyl_post['pid']] = -1;
			}

			// Count the tyl counts for each user to be subtracted
			if($user_tyls[$tyl_post['uid']])
			{
				$user_tyls[$tyl_post['uid']]--;
			}
			else
			{
				$user_tyls[$tyl_post['uid']] = -1;
			}
		}
		// Remove tyl count from users
		if(is_array($user_tyls))
		{
			foreach($user_tyls as $uid => $subtract)
			{
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumtyls=tyl_unumtyls$subtract WHERE uid='$uid'");
			}
		}
		if(is_array($user_prcvtyls))
		{
			foreach($user_prcvtyls as $puid => $value)
			{
				$rcv = 0;
				$prcv = count($value);
				foreach($value as $ppid => $value1)
				{
					$rcv = $rcv + $value1;
				}
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumptyls=tyl_unumptyls-$prcv WHERE uid='$puid'");
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumrcvtyls=tyl_unumrcvtyls$rcv WHERE uid='$puid'");
			}
		}
		// Delete the tyls
		if($tlids)
		{
			$tlids_count = count($tlids);
			$tlids = implode(',', $tlids);
			$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value-$tlids_count WHERE title='total'");
			$db->delete_query($prefix."thankyoulike", "tlid IN ($tlids)");
		}
	}
}

function thankyoulike_delete_post($pid)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	$pid = intval($pid);

	$query = $db->simple_select("posts", "*", "pid='".$pid."'");
	$post = $db->fetch_array($query);

	// Only delete if there are any tyls
	if($post['tyl_pnumtyls'] != 0)
	{
		// Find all tyl data for this pid
		$query = $db->query("
			SELECT tyl.*
			FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
			WHERE tyl.pid='{$pid}'
		");
		$tlids = array();
		$user_tyls = array();
		while($tyl_post = $db->fetch_array($query))
		{
			$tlids[] = $tyl_post['tlid'];

			// Count the tyl counts for each user to be subtracted
			if($user_tyls[$tyl_post['uid']])
			{
				$user_tyls[$tyl_post['uid']]--;
			}
			else
			{
				$user_tyls[$tyl_post['uid']] = -1;
			}
		}
		// Remove tyl count from users
		if(is_array($user_tyls))
		{
			foreach($user_tyls as $uid => $subtract)
			{
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumtyls=tyl_unumtyls$subtract WHERE uid='$uid'");
			}
		}
		// Remove tyl count from the thread and user's tyl received
		$db->write_query("UPDATE ".TABLE_PREFIX."threads SET tyl_tnumtyls=tyl_tnumtyls-".$post['tyl_pnumtyls']." WHERE tid='".$post['tid']."'");
		// Delete the tyls
		if($tlids)
		{
			$tlids_count = count($tlids);
			$tlids = implode(',', $tlids);
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumrcvtyls=tyl_unumrcvtyls-$tlids_count WHERE uid='".$post['uid']."'");
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumptyls=tyl_unumptyls-1 WHERE uid='".$post['uid']."'");
			$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value-$tlids_count WHERE title='total'");
			$db->delete_query($prefix."thankyoulike", "tlid IN ($tlids)");
		}
	}
}

function thankyoulike_merge_posts($args)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	$pids = $args['pids'];
	$tid = $args['tid'];

	$pidin = implode(',', $pids);
	// We first check which is the masterpid where others were merged, other posts should be gone by now
	$query1 = $db->simple_select("posts", "pid, uid", "pid IN ($pidin)");
	$master = $db->fetch_array($query1);

	// Get all the tyls for all the pids to be merged
	$query = $db->query("
		SELECT tyl.*
		FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
		WHERE tyl.pid IN($pidin)
		ORDER BY tyl.pid ASC, tyl.dateline ASC
	");
	$masterpiduid = array();
	$tlids_remove = array();
	$tlids_update = array();
	$user_tyls = array();
	$user_ptyls = array();
	$user_rcvtyls = array();
	while($tyl = $db->fetch_array($query))
	{
		if($master['pid'] == $tyl['pid'])
		{
			// User has tyled master post
			$masterpiduid[$tyl['uid']] = 1;
		}
		else
		{
			if(($masterpiduid[$tyl['uid']]) || $tyl['uid'] == $master['uid'])
			{
				// User has tyled master post or is author of master post, remove tyl, update count
				$tlids_remove[] = $tyl['tlid'];
				if($user_tyls[$tyl['uid']])
				{
					$user_tyls[$tyl['uid']]--;
				}
				else
				{
					$user_tyls[$tyl['uid']] = -1;
				}
				if($user_rcvtyls[$tyl['puid']][$tyl['pid']])
				{
					$user_rcvtyls[$tyl['puid']][$tyl['pid']]--;
				}
				else
				{
					$user_rcvtyls[$tyl['puid']][$tyl['pid']] = -1;
				}
			}
			else
			{
				// User has not tyled master post, add it to the master post
				$tlids_update[] = $tyl['tlid'];
			}
		}
	}
	// Remove tyl count from users
	if(is_array($user_tyls))
	{
		foreach($user_tyls as $uid => $subtract)
		{
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumtyls=tyl_unumtyls$subtract WHERE uid='$uid'");
		}
	}
	if(is_array($user_rcvtyls))
	{
		foreach($user_rcvtyls as $puid => $value)
		{
			$rcv = 0;
			$prcv = count($value);
			foreach($value as $ppid => $value1)
			{
				$rcv = $rcv + $value1;
			}
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumptyls=tyl_unumptyls-$prcv WHERE uid='$puid'");
			$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumrcvtyls=tyl_unumrcvtyls$rcv WHERE uid='$puid'");
		}
	}
	// Update the tyls moving to the masterpid and add them as tyls received to master post uid
	if($tlids_update)
	{
		$tlids_update_count = count($tlids_update);
		$tlids_update = implode(',', $tlids_update);
		$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumrcvtyls=tyl_unumrcvtyls+$tlids_update_count WHERE uid='".$master['uid']."'");
		$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."thankyoulike SET pid=".$master['pid'].", puid=".$master['uid']." WHERE tlid IN ($tlids_update)");
	}
	// Remove tyl count from the thread
	if($tlids_remove)
	{
		$tlids_count = count($tlids_remove);
		$tlids_remove = implode(',', $tlids_remove);
		$db->write_query("UPDATE ".TABLE_PREFIX."threads SET tyl_tnumtyls=tyl_tnumtyls-$tlids_count WHERE tid='$tid'");
		$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value-$tlids_count WHERE title='total'");
		// Delete the tyls
		$db->delete_query($prefix."thankyoulike", "tlid IN ($tlids_remove)");
	}
}

function thankyoulike_merge_threads($args)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	$mergetid = $args['mergetid'];
	$tid = $args['tid'];

	// Get the tyl num from old thread
	$query = $db->simple_select("threads", "tyl_tnumtyls", "tid='$mergetid'");
	$merge_tyltnum = $db->fetch_field($query, "tyl_tnumtyls");

	// Add tyl count from old thread to new one
	if ($merge_tyltnum != 0)
	{
		$db->write_query("UPDATE ".TABLE_PREFIX."threads SET tyl_tnumtyls=tyl_tnumtyls+$merge_tyltnum WHERE tid='$tid'");
	}
}

function thankyoulike_split_posts($args)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	$pids = $args['pids'];
	$tid = $args['tid'];

	$pids_list = implode(',', $pids);

	// Get tyl count for each post
	$query = $db->simple_select("posts", "tid, tyl_pnumtyls", "pid IN ($pids_list)");
	$tyl_pnumtyls = 0;
	$newtid = 0;
	while ($tyl_pnum = $db->fetch_array($query))
	{
		$tyl_pnumtyls = $tyl_pnumtyls + $tyl_pnum['tyl_pnumtyls'];
		$newtid = $tyl_pnum['tid'];
	}

	if ($tyl_pnumtyls != 0)
	{
		// Add it to new thread
		$db->write_query("UPDATE ".TABLE_PREFIX."threads SET tyl_tnumtyls=tyl_tnumtyls+$tyl_pnumtyls WHERE tid='$newtid'");
		// Remove from old thread
		$db->write_query("UPDATE ".TABLE_PREFIX."threads SET tyl_tnumtyls=tyl_tnumtyls-$tyl_pnumtyls WHERE tid='$tid'");
	}
}

function thankyoulike_delete_user()
{
	global $db, $user;
	$prefix = 'g33k_thankyoulike_';

	// Only delete/update if the user had tyls
	if($user['tyl_unumtyls'] != 0)
	{
		// Find all tyl data for this user
		$query = $db->query("
			SELECT tyl.*, p.tid
			FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
			LEFT JOIN ".TABLE_PREFIX."posts p ON (p.pid=tyl.pid)
			WHERE tyl.uid='".$user['uid']."'
		");
		$tlids = array();
		$post_tyls = array();
		$thread_tyls = array();
		$user_tyls = array();
		while($tyl_user = $db->fetch_array($query))
		{
			$tlids[] = $tyl_user['tlid'];

			// Count the tyl counts for each post to be subtracted
			if($post_tyls[$tyl_user['pid']])
			{
				$post_tyls[$tyl_user['pid']]--;
			}
			else
			{
				$post_tyls[$tyl_user['pid']] = -1;
			}
			// Same for threads
			if($thread_tyls[$tyl_user['tid']])
			{
				$thread_tyls[$tyl_user['tid']]--;
			}
			else
			{
				$thread_tyls[$tyl_user['tid']] = -1;
			}
			// Count tyls received by this user to be removed
			if($user_tyls[$tyl_user['puid']])
			{
				$user_tyls[$tyl_user['puid']]--;
			}
			else
			{
				$user_tyls[$tyl_user['puid']] = -1;
			}
		}
		// Remove tyl count from posts
		if(is_array($post_tyls))
		{
			foreach($post_tyls as $pid => $subtract)
			{
				$db->write_query("UPDATE ".TABLE_PREFIX."posts SET tyl_pnumtyls=tyl_pnumtyls$subtract WHERE pid='$pid'");
			}
		}
		// Remove tyl count from threads
		if(is_array($thread_tyls))
		{
			foreach($thread_tyls as $tid => $subtract)
			{
				$db->write_query("UPDATE ".TABLE_PREFIX."threads SET tyl_tnumtyls=tyl_tnumtyls$subtract WHERE tid='$tid'");
			}
		}
		// Remove tyl received count from users
		if(is_array($user_tyls))
		{
			foreach($user_tyls as $puid => $subtract)
			{
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumrcvtyls=tyl_unumrcvtyls$subtract WHERE uid='$puid'");
			}
		}
		// Delete the tyls, update total
		if($tlids)
		{
			$tlids_count = count($tlids);
			$tlids = implode(',', $tlids);
			$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value-$tlids_count WHERE title='total'");
			$db->delete_query($prefix."thankyoulike", "tlid IN ($tlids)");
		}
	}
}

function thankyoulike_wol_activity($user_activity)
{
	global $user;

	$split_loc = explode(".php", $user_activity['location']);
	if($split_loc[0] == $user['location'])
	{
		$filename = '';
	}
	else
	{
		$filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
	}

	if ($filename == "tylsearch")
	{
		$user_activity['activity'] = "tyl_searching";
	}

	return $user_activity;
}

function thankyoulike_friendly_wol_activity($plugin_array)
{
	global $mybb, $lang;
	$lang->load("thankyoulike");

	if ($plugin_array['user_activity']['activity'] == "tyl_searching")
	{
		if ($mybb->settings[$prefix.'thankslike'] == "like")
		{
			$plugin_array['location_name'] = $lang->sprintf($lang->tyl_wol_searching, "tylsearch.php", $lang->tyl_likes);
		}
		else
		{
			$plugin_array['location_name'] = $lang->sprintf($lang->tyl_wol_searching, "tylsearch.php", $lang->tyl_thanks);
		}
	}

	return $plugin_array;
}

function thankyoulike_settings_page()
{
	global $db, $mybb, $g33k_settings_peeker;
	$prefix = 'g33k_thankyoulike_';

	$query = $db->simple_select("settinggroups", "gid", "name='{$prefix}settings'", array('limit' => 1));
	$group = $db->fetch_array($query);
	$g33k_settings_peeker = ($mybb->input["gid"] == $group["gid"]) && ($mybb->request_method != "post");
}

function thankyoulike_settings_peeker()
{
	global $g33k_settings_peeker;
	$prefix = 'g33k_thankyoulike_';

	if($g33k_settings_peeker)
	{
		echo '<script type="text/javascript">
		$(document).ready(function(){
			new Peeker($(".setting_'.$prefix.'enabled"), $("#row_setting_'.$prefix.'thankslike, #row_setting_'.$prefix.'firstall, #row_setting_'.$prefix.'firstalloverwrite, #row_setting_'.$prefix.'removing, #row_setting_'.$prefix.'tylownposts, #row_setting_'.$prefix.'remowntylfroms, #row_setting_'.$prefix.'remowntylfromc, #row_setting_'.$prefix.'closedthreads, #row_setting_'.$prefix.'exclude, #row_setting_'.$prefix.'unameformat, #row_setting_'.$prefix.'hideforgroups, #row_setting_'.$prefix.'showdt, #row_setting_'.$prefix.'dtformat, #row_setting_'.$prefix.'sortorder, #row_setting_'.$prefix.'collapsible, #row_setting_'.$prefix.'colldefault, #row_setting_'.$prefix.'hidelistforgroups, #row_setting_'.$prefix.'displaygrowl, #row_setting_'.$prefix.'limits"), 1, true),
			new Peeker($(".setting_'.$prefix.'tylownposts"), $("#row_setting_'.$prefix.'remowntylfroms, #row_setting_'.$prefix.'remowntylfromc"), 1, true),
			new Peeker($(".setting_'.$prefix.'reputation_add"), $("#row_setting_'.$prefix.'reputation_add_reppoints, #row_setting_'.$prefix.'reputation_add_repcomment"), 1, true),
			new Peeker($(".setting_'.$prefix.'showdt"), $("#row_setting_'.$prefix.'dtformat"),/^(?!none)/, true),
			new Peeker($(".setting_'.$prefix.'collapsible"), $("#row_setting_'.$prefix.'colldefault"), 1, true),
			new Peeker($(".setting_'.$prefix.'highlight_popular_posts"), $("#row_setting_'.$prefix.'highlight_popular_posts_count"), 1, true),
			new Peeker($(".setting_'.$prefix.'show_memberprofile_box"), $("#row_setting_'.$prefix.'profile_box_post_cutoff, #row_setting_'.$prefix.'profile_box_post_allowhtml, #row_setting_'.$prefix.'profile_box_post_allowmycode, #row_setting_'.$prefix.'profile_box_post_allowsmilies, #row_setting_'.$prefix.'profile_box_post_allowimgcode, #row_setting_'.$prefix.'profile_box_post_allowvideocode"), 1, true)
		});
		</script>';
	}
}

// Start Thank You/Like Promotions Functions
function thankyoulike_promotion_formcontainer_output_row(&$args)
{
	global $run_module, $form_container, $mybb, $db, $lang, $form, $options, $options_type, $promotion;

	if(!($run_module == 'user' && !empty($form_container->_title) && $mybb->get_input('module') == 'user-group_promotions' && in_array($mybb->get_input('action'), array('add', 'edit'))))
	{
		return;
	}

	$lang->load('config_thankyoulike');

	if($args['label_for'] == 'requirements')
	{
		$options['tylreceived'] = $lang->setting_thankyoulike_promotion_rcv;
		$args['content'] = $form->generate_select_box('requirements[]', $options, $mybb->input['requirements'], array('id' => 'requirements', 'multiple' => true, 'size' => 5));
	}

	if($args['label_for'] == 'timeregistered')
	{
		if($mybb->get_input('pid', 1) && !isset($mybb->input['tylreceived']))
		{
			$tylreceived = $promotion['tylreceived'];
			$tylreceivedtype = $promotion['tylreceivedtype'];
		}
		else
		{
			$tylreceived = $mybb->get_input('tylreceived');
			$tylreceivedtype = $mybb->get_input('tylreceivedtype');
		}

		$form_container->output_row($lang->setting_thankyoulike_promotion_rcv, $lang->setting_thankyoulike_promotion_rcv_desc, $form->generate_numeric_field('tylreceived', (int)$tylreceived, array('id' => 'tylreceived'))." ".$form->generate_select_box("tylreceivedtype", $options_type, $tylreceivedtype, array('id' => 'tylreceivedtype')), 'tylreceived');
	}

	if($args['label_for'] == 'requirements')
	{
		$options['tylgiven'] = $lang->setting_thankyoulike_promotion_gvn;
		$args['content'] = $form->generate_select_box('requirements[]', $options, $mybb->input['requirements'], array('id' => 'requirements', 'multiple' => true, 'size' => 5));
	}

	if($args['label_for'] == 'timeregistered')
	{
		if($mybb->get_input('pid', 1) && !isset($mybb->input['tylgiven']))
		{
			$tylgiven = $promotion['tylgiven'];
			$tylgiventype = $promotion['tylgiventype'];
		}
		else
		{
			$tylgiven = $mybb->get_input('tylgiven');
			$tylgiventype = $mybb->get_input('tylgiventype');
		}

		$form_container->output_row($lang->setting_thankyoulike_promotion_gvn, $lang->setting_thankyoulike_promotion_gvn_desc, $form->generate_numeric_field('tylgiven', (int)$tylgiven, array('id' => 'tylgiven'))." ".$form->generate_select_box("tylgiventype", $options_type, $tylgiventype, array('id' => 'tylgiventype')), 'tylgiven');
	}
}

function thankyoulike_promotion_commit()
{
	global $db, $mybb, $pid, $update_promotion;

	is_array($update_promotion) or $update_promotion = array();

	$update_promotion['tylreceived'] = $mybb->get_input('tylreceived', 1);
	$update_promotion['tylreceivedtype'] = $db->escape_string($mybb->get_input('tylreceivedtype'));
	$update_promotion['tylgiven'] = $mybb->get_input('tylgiven', 1);
	$update_promotion['tylgiventype'] = $db->escape_string($mybb->get_input('tylgiventype'));

	if($mybb->get_input('action') == 'add')
	{
		$db->update_query('promotions', $update_promotion, "pid='{$pid}'");
	}
}

function thankyoulike_promotion_task(&$args)
{
	if(in_array('tylreceived', explode(',', $args['promotion']['requirements'])) && (int)$args['promotion']['tylreceived'] >= 0 && !empty($args['promotion']['tylreceivedtype']))
	{
		$args['sql_where'] .= "{$args['and']}tyl_unumrcvtyls{$args['promotion']['tylreceivedtype']}'{$args['promotion']['tylreceived']}'";
		$args['and'] = ' AND ';
	}
	if(in_array('tylgiven', explode(',', $args['promotion']['requirements'])) && (int)$args['promotion']['tylgiven'] >= 0 && !empty($args['promotion']['tylgiventype']))
	{
		$args['sql_where'] .= "{$args['and']}tyl_unumtyls{$args['promotion']['tylgiventype']}'{$args['promotion']['tylgiven']}'";
		$args['and'] = ' AND ';
	}
}

// Start Thank You/Like Counter Functions
function acp_tyl_do_recounting()
{
	global $db, $mybb, $lang;
	$prefix = "g33k_thankyoulike_";
	$lang->load("config_thankyoulike");

	if($mybb->request_method == "post")
	{
		if(!isset($mybb->input['page']) || $mybb->get_input('page', MyBB::INPUT_INT) < 1)
		{
			$mybb->input['page'] = 1;
		}

		if(isset($mybb->input['do_recounttyls']))
		{
			if($mybb->input['page'] == 1)
			{
				// Log admin action
				log_admin_action($lang->tyl_admin_log_action);
			}

			if(!$mybb->get_input('tyls', MyBB::INPUT_INT))
			{
				$mybb->input['tyls'] = 500;
			}

			$page = $mybb->get_input('page', MyBB::INPUT_INT);
			$per_page = $mybb->get_input('tyls', MyBB::INPUT_INT);
			if($per_page <= 0)
			{
				$per_page = 500;
			}
			$start = ($page-1) * $per_page;
			$end = $start + $per_page;

			if ($page == 1)
			{
				$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=0 WHERE title='total'");
				$db->write_query("UPDATE ".TABLE_PREFIX."posts SET tyl_pnumtyls=0");
				$db->write_query("UPDATE ".TABLE_PREFIX."threads SET tyl_tnumtyls=0");
				$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumtyls=0, tyl_unumptyls=0, tyl_unumrcvtyls=0");

				$query = $db->query("
						SELECT tyl.tlid
						FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
						LEFT JOIN ".TABLE_PREFIX."posts p ON ( p.pid = tyl.pid )
						LEFT JOIN ".TABLE_PREFIX."users u ON ( u.uid = tyl.uid )
						WHERE p.pid IS NULL OR u.uid IS NULL
					");
				$tlids_remove = array();
				while($orphan = $db->fetch_array($query))
				{
					$tlids_remove[] = $orphan['tlid'];
				}
				if($tlids_remove)
				{
					$tlids_remove = implode(',', $tlids_remove);
					// Delete the tyls
					$db->delete_query($prefix."thankyoulike", "tlid IN ($tlids_remove)");
				}
				// Lets also update the puid field in the db with uid values from the posts table
				// This is done to sync up the db with the puids of post tyled, since this feature wasn't there in v1.0 so data needs to be generated.
				$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."thankyoulike tyl
							LEFT JOIN ".TABLE_PREFIX."posts p ON ( p.pid=tyl.pid )
							SET tyl.puid=p.uid");
				// Update the number of tyled posts for the post owners, we do this here because this needs to be done in one swoop and will break if done in parts
				$db->write_query("UPDATE ".TABLE_PREFIX."users u
							JOIN (SELECT puid, COUNT(DISTINCT(pid)) AS pidcount
							FROM ".TABLE_PREFIX.$prefix."thankyoulike
							GROUP BY puid) tyl
							ON ( u.uid=tyl.puid )
							SET u.tyl_unumptyls=tyl.pidcount");
			}

			$query1 = $db->simple_select($prefix."thankyoulike", "COUNT(tlid) AS num_tyls");
			$num_tyls = $db->fetch_field($query1, 'num_tyls');

			$query2 = $db->query("
					SELECT tyl.*, p.tid
					FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
					LEFT JOIN ".TABLE_PREFIX."posts p ON (p.pid=tyl.pid)
					ORDER BY tyl.dateline ASC
					LIMIT $start, $per_page
				");
			$tlids = array();
			$post_tyls = array();
			$thread_tyls = array();
			$user_tyls = array();
			$user_rcvtyls = array();
			while($tyl = $db->fetch_array($query2))
			{
				// Total tyls
				$tlids[] = $tyl['tlid'];
				// Count the tyl for each post, thread and user
				if($post_tyls[$tyl['pid']])
				{
					$post_tyls[$tyl['pid']]++;
				}
				else
				{
					$post_tyls[$tyl['pid']] = 1;
				}
				if($thread_tyls[$tyl['tid']])
				{
					$thread_tyls[$tyl['tid']]++;
				}
				else
				{
					$thread_tyls[$tyl['tid']] = 1;
				}
				if($user_tyls[$tyl['uid']])
				{
					$user_tyls[$tyl['uid']]++;
				}
				else
				{
					$user_tyls[$tyl['uid']] = 1;
				}
				if($user_rcvtyls[$tyl['puid']])
				{
					$user_rcvtyls[$tyl['puid']]++;
				}
				else
				{
					$user_rcvtyls[$tyl['puid']] = 1;
				}
			}
			// Update the counts
			if(is_array($post_tyls))
			{
				foreach($post_tyls as $pid => $add)
				{
					$db->write_query("UPDATE ".TABLE_PREFIX."posts SET tyl_pnumtyls=tyl_pnumtyls+$add WHERE pid='$pid'");
				}
			}
			if(is_array($thread_tyls))
			{
				foreach($thread_tyls as $tid => $add)
				{
					$db->write_query("UPDATE ".TABLE_PREFIX."threads SET tyl_tnumtyls=tyl_tnumtyls+$add WHERE tid='$tid'");
				}
			}
			if(is_array($user_tyls))
			{
				foreach($user_tyls as $uid => $add)
				{
					$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumtyls=tyl_unumtyls+$add WHERE uid='$uid'");
				}
			}
			if(is_array($user_rcvtyls))
			{
				foreach($user_rcvtyls as $puid => $add)
				{
					$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumrcvtyls=tyl_unumrcvtyls+$add WHERE uid='$puid'");
				}
			}
			if($tlids)
			{
				$tlids_count = count($tlids);
				$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value+$tlids_count WHERE title='total'");
			}
			check_proceed($num_tyls, $end, ++$page, $per_page, "tyls", "do_recounttyls", $lang->tyl_success_thankyoulike_rebuilt);
		}
	}
}

function acp_tyl_recount_form()
{
	global $lang, $form_container, $form;
	$lang->load("config_thankyoulike");

	$form_container->output_cell("<label>{$lang->tyl_recount}</label><div class=\"description\">{$lang->tyl_recount_do_desc}</div>");
	$form_container->output_cell($form->generate_numeric_field("tyls", 500, array('style' => 'width: 150px;', 'min' => 0)));
	$form_container->output_cell($form->generate_submit_button($lang->go, array("name" => "do_recounttyls")));
	$form_container->construct_row();
}


function tyl_limits_usergroup_permission_tabs(&$tabs)
{
	global $lang;
	$prefix = 'g33k_thankyoulike_';
	$lang->load("config_thankyoulike");

	$tabs['tyl_limits'] = $lang->tyl_limits_tab;
	return $tabs;
}

function tyl_limits_usergroup_permission()
{
	global $mybb, $lang, $form, $form_container;
	$prefix = 'g33k_thankyoulike_';
	$lang->load("config_thankyoulike");

	if($mybb->settings[$prefix.'limits'] == 1)
	{
		if(!empty($form_container->_title) & !empty($lang->users_permissions) & $form_container->_title == $lang->users_permissions)
		{
			$tyl_limits_options = array(
			"{$lang->tyl_limits_permissions_title}<br /><small class=\"input\">{$lang->tyl_limits_permissions_desc}</small><br />".$form->generate_numeric_field('tyl_limits_max', $mybb->input['tyl_limits_max'], array('id' => 'max_tyl_limits', 'class' => 'field50', 'min' => 0))
			);
			$form_container->output_row($lang->tyl_limits_permissions_system, "", "<div class=\"group_settings_bit\">".implode("</div><div class=\"group_settings_bit\">", $tyl_limits_options)."</div>");
		}
	}
}

function tyl_limits_usergroup_permission_commit()
{
	global $db, $mybb, $updated_group;
	$updated_group['tyl_limits_max'] = $db->escape_string((int)$mybb->input['tyl_limits_max']);
}

function tyl_preinstall_cleanup()
{
	global $mybb, $db, $cache;

	$codename = basename(__FILE__, ".php");
	$prefix = 'g33k_'.$codename.'_';

	//delete old unnecessary files
	if(file_exists(MYBB_ROOT."/admin/modules/tools/thankyoulike_recount.php"))
	{
		@unlink(MYBB_ROOT."/admin/modules/tools/thankyoulike_recount.php");
	}
	if(file_exists(MYBB_ROOT."/inc/languages/english/admin/tools_thankyoulike_recount.lang.php"))
	{
		@unlink(MYBB_ROOT."/inc/languages/english/admin/tools_thankyoulike_recount.lang.php");
	}

	// Remove old templates
	$db->delete_query("templates", "title LIKE 'thankyoulike%'");
	$db->delete_query("templategroups", "prefix in ('thankyoulike')");

	// Remove old CSS rules for g33k_thankyoulike
	require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
	$db->delete_query("themestylesheets", "name='g33k_thankyoulike.css'");
	$query = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($query))
	{
		update_theme_stylesheet_list($theme['tid']);
	}

	// Remove old settings
	$result = $db->simple_select('settinggroups', 'gid', "name = '{$prefix}settings'", array('limit' => 1));
	$group = $db->fetch_array($result);

	if(!empty($group['gid']))
	{
		$db->delete_query('settinggroups', "gid='{$group['gid']}'");
		$db->delete_query('settings', "gid='{$group['gid']}'");
		rebuild_settings();
	}

	$cache->update_usergroups();
	$cache->update_forums();
	$cache->update_tasks();
}
