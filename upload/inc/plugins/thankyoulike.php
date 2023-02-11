<?php
/**
 * Thank You/Like system - plugin for MyBB 1.8.x forum software
 *
 * @package MyBB Plugin
 * @author MyBB Group - Eldenroot - <eldenroot@gmail.com>
 * @copyright 2020 MyBB Group <http://mybb.group>
 * @link <https://github.com/mybbgroup/MyBB_Thank-you-like-plugin>
 * @version 3.4.4
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
	$plugins->add_hook("admin_formcontainer_end", "tyl_limits_usergroup_permission");
	$plugins->add_hook("admin_user_groups_edit_commit", "tyl_limits_usergroup_permission_commit");
	$plugins->add_hook('admin_load', 'thankyoulike_admin_load');
	$plugins->add_hook("admin_user_users_delete_commit","thankyoulike_delete_user");
	$plugins->add_hook("admin_tools_recount_rebuild", "acp_tyl_do_recounting");
	$plugins->add_hook("admin_tools_recount_rebuild_output_list", "acp_tyl_recount_form");
	$plugins->add_hook("admin_config_settings_change","thankyoulike_settings_page");
	$plugins->add_hook("admin_page_output_footer","thankyoulike_settings_peeker");
	$plugins->add_hook("admin_config_plugins_activate_commit", "tyl_plugins_activate_commit");
	$plugins->add_hook("admin_user_users_merge_commit", "tyl_user_users_merge_commit");
}
else
{
	$plugins->add_hook("global_start", "thankyoulike_templatelist");
	$plugins->add_hook("postbit","thankyoulike_postbit");
	$plugins->add_hook("postbit_prev","thankyoulike_postbit_udetails");
	$plugins->add_hook("postbit_pm","thankyoulike_postbit_udetails");
	$plugins->add_hook("forumdisplay_thread_end","thankyoulike_threads_udetails");
	$plugins->add_hook("search_results_thread","thankyoulike_threads_udetails");
	$plugins->add_hook("postbit_announcement","thankyoulike_postbit_udetails");
	$plugins->add_hook("member_profile_end","thankyoulike_memprofile");
	$plugins->add_hook("fetch_wol_activity_end", "thankyoulike_wol_activity");
	$plugins->add_hook("build_friendly_wol_location_end", "thankyoulike_friendly_wol_activity");
	$plugins->add_hook("class_moderation_delete_thread_start","thankyoulike_delete_thread");
	$plugins->add_hook("class_moderation_delete_post_start","thankyoulike_delete_post");
	$plugins->add_hook("class_moderation_merge_posts","thankyoulike_merge_posts");
	$plugins->add_hook('task_promotions', 'thankyoulike_promotion_task');
	$plugins->add_hook("myalerts_alert_manager_mark_read", "tyl_myalerts_alert_manager_mark_read");
}

function thankyoulike_info()
{
	global $plugins_cache, $db, $lang, $admin_session;
	$lang->load('config_thankyoulike');
	$prefix = 'g33k_thankyoulike_';
	$codename = 'thankyoulike';

	$changelog_url = 'https://github.com/mybbgroup/MyBB_Thank-you-like-plugin/releases';

	$url_AT= '<a href="https://community.mybb.com/user-69212.html" target="_blank">ATofighi</a>';
	$url_SP = '<a href="https://community.mybb.com/user-91011.html" target="_blank">SvePu</a>';
	$url_E = '<a href="https://community.mybb.com/user-84065.html" target="_blank">Eldenroot</a>';
	$url_DN = '<a href="https://community.mybb.com/user-51493.html" target="_blank">Whiteneo</a>';
	$url_L = '<a href="https://community.mybb.com/user-116662.html" target="_blank">Laird</a>';
	$url_CH = '<a href="https://community.mybb.com/user-95538.html" target="_blank">chack1172</a>';
	$url_S = '<a href="https://github.com/mybbgroup/MyBB_Thank-you-like-plugin" target="_blank">GitHub</a>';
	$url_MyBBGroup = '<a href="https://mybb.group" target="_blank">MyBB Group official website</a>';

	$info = array(
		"name"		=> htmlspecialchars_uni($lang->tyl_info_title),
		"description"	=> htmlspecialchars_uni($lang->tyl_info_desc) . $lang->sprintf($lang->tyl_info_desc_url,$url_AT,$url_SP,$url_E,$url_DN,$url_L,$url_CH,$url_S,$url_MyBBGroup),
		"website"	=> "https://community.mybb.com/thread-169382.html",
		"author"	=> "MyBB Group with love <3",
		"authorsite"	=> "https://community.mybb.com/thread-169382.html",
		"version"	=> "3.4.4",
		// Constructed by converting each digit of "version" above into two digits (zero-padded if necessary),
		// then concatenating them, then removing any leading zero to avoid the value being interpreted as octal.
		"version_code"  => 30404,
		"codename"	=> "thankyoulikesystem",
		"compatibility"	=> "18*"
	);

	$info_desc = '';

	if(is_array($plugins_cache) && is_array($plugins_cache['active']) && isset($plugins_cache['active'][$codename]))
	{
		$msg = "<a href=\"".htmlspecialchars_uni($changelog_url)."\">".$lang->tyl_view_changelog."</a>";
		if(!empty($admin_session['data']['tyl_plugin_info_upgrade_message']))
		{
			$msg = $admin_session['data']['tyl_plugin_info_upgrade_message'].' '.$msg;
			$img_type = 'success';
			$class = ' class="success"';
			update_admin_session('tyl_plugin_info_upgrade_message', '');
		}
		else
		{
			$img_type = 'default';
			$class = '';
		}
		$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/{$img_type}.png)\"><div$class>$msg</div></li></ul>\n";

		// Is a supported version of the MyAlerts plugin active?
		if(tyl_have_myalerts(true))
		{
			// Yes, so: is the tyl alert type registered and enabled?
			if (tyl_have_myalerts(true, true, true))
			{
				// It is: hooray, we have full MyAlerts integration.
				$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/success.png)\"><span style=\"color: green;\">".$lang->tyl_info_desc_alerts_integrated."</span></li></ul>";
			}
			else
			{
				// Too bad, it isn't, so offer the admin the chance to fully integrate with MyAlerts.
				$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/warning.png)\"><a href=\"index.php?module=config-plugins&amp;action=tyl_myalerts_integrate\" style=\"color: red;\">".$lang->tyl_info_desc_alerts_integrate."</a></li></ul>";
			}
			$myalerts_info = myalerts_info();
			if(version_compare($myalerts_info['version'], '2.0.4') < 0)
			{
				$info_desc .= '<ul><li style="list-style-image: url(styles/default/images/icons/warning.png)"><span style="color: red;">'.$lang->sprintf($lang->tyl_myalerts_version_under_2_0_4, $myalerts_info['version']).'</span></li></ul>';
			}
		}

		$missing_indexes = '';
		$query = $db->query("SHOW INDEX FROM ".TABLE_PREFIX.$prefix."thankyoulike WHERE Key_name='uid_dateline'");
		if($db->num_rows($query) == 0)
		{
			$missing_indexes .= "<li style=\"list-style-image: url(styles/default/images/icons/warning.png)\">".$lang->sprintf($lang->tyl_missing_index, 'uid_dateline', TABLE_PREFIX.$prefix.'thankyoulike', $lang->tyl_missing_index_purpose1, 'tyl_create_index_uid_dateline')."</li>";
		}
		$db->free_result($query);
		$query = $db->query("SHOW INDEX FROM ".TABLE_PREFIX.$prefix."thankyoulike WHERE Key_name='puid_dateline'");
		if($db->num_rows($query) == 0)
		{
			$missing_indexes .= "<li style=\"list-style-image: url(styles/default/images/icons/warning.png)\">".$lang->sprintf($lang->tyl_missing_index, 'puid_dateline', TABLE_PREFIX.$prefix.'thankyoulike', $lang->tyl_missing_index_purpose1, 'tyl_create_index_puid_dateline')."</li>";
		}
		$db->free_result($query);
		$query = $db->query("SHOW INDEX FROM ".TABLE_PREFIX.$prefix."thankyoulike WHERE Key_name='puid_uid'");
		if($db->num_rows($query) == 0)
		{
			$missing_indexes .= "<li style=\"list-style-image: url(styles/default/images/icons/warning.png)\">".$lang->sprintf($lang->tyl_missing_index, 'puid_uid', TABLE_PREFIX.$prefix.'thankyoulike', $lang->tyl_missing_index_purpose1, 'tyl_create_index_puid_uid')."</li>";
		}
		$db->free_result($query);
		$query = $db->query("SHOW INDEX FROM ".TABLE_PREFIX.$prefix."thankyoulike WHERE Key_name='puid_pid'");
		if($db->num_rows($query) == 0)
		{
			$missing_indexes .= "<li style=\"list-style-image: url(styles/default/images/icons/warning.png)\">".$lang->sprintf($lang->tyl_missing_index, 'puid_pid', TABLE_PREFIX.$prefix.'thankyoulike', $lang->tyl_missing_index_purpose2, 'tyl_create_index_puid_pid')."</li>";
		}
		$db->free_result($query);
		if ($missing_indexes)
		{
			$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/warning.png)\">Missing index(es):<ul>$missing_indexes</ul></li></ul>";
		}

		$result = $db->simple_select('settinggroups', 'gid', "name = '{$prefix}settings'", array('limit' => 1));
		$group = $db->fetch_array($result);
		if(!empty($group['gid']))
		{
			$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/custom.png)\"><a href=\"index.php?module=config-settings&action=change&gid=".$group['gid']."\">".htmlspecialchars_uni($lang->tyl_info_desc_configsettings)."</a></li></ul>";
		}

		$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/run_task.png)\"><a href=\"index.php?module=tools-recount_rebuild\">".htmlspecialchars_uni($lang->tyl_info_desc_recount)."</a></li></ul>\n";
		$info_desc .= "<ul><li style=\"list-style-image: url(styles/default/images/icons/find.png)\"><a href=\"../thankyoulike.php?action=css\">".htmlspecialchars_uni($lang->tyl_view_master_thankyoulike_css)."</a> {$lang->tyl_use_this_css_for}</li></ul>\n";
		$info_desc .= '<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float: right;" target="_blank">
<input type="hidden" name="cmd" value="_donations">
<input type="hidden" name="business" value="5FSNQNV52TXGS">
<input type="hidden" name="item_name" value="Thank You/Like system plugin for MyBB" />
<input type="hidden" name="currency_code" value="USD" />
<input type="image" src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_donate_pp_142x27.png" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Donate with PayPal button">
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
	global $mybb, $lang, $db;
	$prefix = 'g33k_thankyoulike_';
	$lang->load('config_thankyoulike');

	switch($mybb->input['action'])
	{
	case 'tyl_myalerts_integrate':
		if (tyl_myalerts_integrate())
		{
			$msg = $lang->tyl_alerts_integration_success_msg;
			$type = 'success';
		}
		else
		{
			$msg = $lang->tyl_alerts_integration_failure_msg;
			$type = 'error';
		}
		flash_message($msg, $type);
		admin_redirect('index.php?module=config-plugins');
		break;
	case 'tyl_create_index_uid_dateline';
		$db->query("ALTER TABLE ".TABLE_PREFIX.$prefix."thankyoulike ADD KEY uid_dateline  (uid , dateline)");
		$msg = $lang->sprintf($lang->tyl_success_index_create, 'uid_dateline', TABLE_PREFIX.$prefix.'thankyoulike');
		$type = 'success';
		flash_message($msg, $type);
		admin_redirect('index.php?module=config-plugins');
		break;
	case 'tyl_create_index_puid_dateline';
		$db->query("ALTER TABLE ".TABLE_PREFIX.$prefix."thankyoulike ADD KEY puid_dateline (puid, dateline)");
		$msg = $lang->sprintf($lang->tyl_success_index_create, 'puid_dateline', TABLE_PREFIX.$prefix.'thankyoulike');
		$type = 'success';
		flash_message($msg, $type);
		admin_redirect('index.php?module=config-plugins');
		break;
	case 'tyl_create_index_puid_uid':
		$db->query("ALTER TABLE ".TABLE_PREFIX.$prefix."thankyoulike ADD KEY puid_uid (puid, uid)");
		$msg = $lang->sprintf($lang->tyl_success_index_create, 'puid_uid', TABLE_PREFIX.$prefix.'thankyoulike');
		$type = 'success';
		flash_message($msg, $type);
		admin_redirect('index.php?module=config-plugins');
		break;
	case 'tyl_create_index_puid_pid':
		$db->query("ALTER TABLE ".TABLE_PREFIX.$prefix."thankyoulike ADD KEY puid_pid (puid, pid)");
		$msg = $lang->sprintf($lang->tyl_success_index_create, 'puid_pid', TABLE_PREFIX.$prefix.'thankyoulike');
		$type = 'success';
		flash_message($msg, $type);
		admin_redirect('index.php?module=config-plugins');
		break;
	default:
		break;
	}
}

function tyl_plugins_activate_commit()
{
	global $message, $tyl_plugin_upgrade_message;

	if (!empty($tyl_plugin_upgrade_message))
	{
		$message = $tyl_plugin_upgrade_message;
	}
}

/**
 * Integrate with MyAlerts if possible.
 * @return boolean True if a successful integration was performed. False if not,
 *                 including the case that the plugin was already integrated with MyAlerts.
 */
function tyl_myalerts_integrate()
{
	global $db, $cache;

	$ret = false;

	// Verify that a supported version of MyAlerts is both present and activated.
	if(tyl_have_myalerts(true))
	{
		// Check whether the tyl alert type is registered.
		if(!tyl_have_myalerts(true, true))
		{
			// It isn't, so register it.
			$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::createInstance($db, $cache);
			$alertType = new MybbStuff_MyAlerts_Entity_AlertType();
			$alertType->setCode('tyl');
			$alertType->setEnabled(true);
			$alertTypeManager->add($alertType);
			$ret = true;
		}
		else
		{
			// It is, so check whether it is enabled.
			if(!tyl_have_myalerts(true, true, true))
			{
				// It isn't, so enabled it.
				tyl_myalerts_set_enabled(1);
				$ret = true;
			}
		}
	}

	return $ret;
}

/**
 * Creates the plugin's settings. Assumes the settings do not already exist,
 * i.e., that they have already been deleted if they were preexisting.
 * @param array The existing settings values indexed by their (prefixed) setting names.
 */
function tyl_create_settings($existing_setting_values = array())
{
	global $db, $lang;
	$lang->load('config_thankyoulike');
	$prefix = 'g33k_thankyoulike_';

	$query = $db->query("SELECT disporder FROM ".TABLE_PREFIX."settinggroups ORDER BY `disporder` DESC LIMIT 1");
	$disporder = $db->fetch_field($query, 'disporder')+1;

	// Insert the plugin's settings group into the database.
	$setting_group = array(
		'name'         => $prefix.'settings',
		'title'        => $db->escape_string($lang->tyl_title),
		'description'  => $db->escape_string($lang->tyl_desc),
		'disporder'    => intval($disporder),
		'isdefault'    => 0
	);
	$db->insert_query('settinggroups', $setting_group);
	$gid = $db->insert_id();

	// Now insert each of its settings values into the database...
	$settings = array(
		'enabled'                         => array(
			'title'       => $lang->tyl_enabled_title,
			'description' => $lang->tyl_enabled_desc,
			'optionscode' => 'onoff',
			'value'       => '1'
		),
		'thankslike'                      => array(
			'title'       => $lang->tyl_thankslike_title,
			'description' => $lang->tyl_thankslike_desc,
			'optionscode' => "radio\nthanks={$lang->tyl_thankslike_op_1}\nlike={$lang->tyl_thankslike_op_2}",
			'value'       => 'thanks'
		),
		'firstall'                        => array(
			'title'       => $lang->tyl_firstall_title,
			'description' => $lang->tyl_firstall_desc,
			'optionscode' => "radio\nfirst={$lang->tyl_firstall_op_1}\nall={$lang->tyl_firstall_op_2}",
			'value'       => 'first'
		),
		'firstalloverride'                => array(
			'title'       => $lang->tyl_firstalloverride_title,
			'description' => $lang->tyl_firstalloverride_desc,
			'optionscode' => 'forumselect',
			'value'       => ''
		),
		'likersdisplay'                   => array(
			'title'       => $lang->tyl_likersdisplay_title,
			'description' => $lang->tyl_likersdisplay_desc,
			'optionscode' => "radio\nusernames={$lang->tyl_likersdisplay_op_1}\navatars={$lang->tyl_likersdisplay_op_2}",
			'value'       => 'usernames'
		),
		'removing'                        => array(
			'title'       => $lang->tyl_removing_title,
			'description' => $lang->tyl_removing_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'tylownposts'                     => array(
			'title'       => $lang->tyl_tylownposts_title,
			'description' => $lang->tyl_tylownposts_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'remowntylfroms'                  => array(
			'title'       => $lang->tyl_remowntylfroms_title,
			'description' => $lang->tyl_remowntylfroms_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'remowntylfromc'                  => array(
			'title'       => $lang->tyl_remowntylfromc_title,
			'description' => $lang->tyl_remowntylfromc_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'reputation_add'                  => array(
			'title'       => $lang->tyl_reputation_add_title,
			'description' => $lang->tyl_reputation_add_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'reputation_add_reppoints'        => array(
			'title'       => $lang->tyl_reputation_add_reppoints_title,
			'description' => $lang->tyl_reputation_add_reppoints_desc,
			'optionscode' => 'numeric',
			'value'       => '1'
		),
		'reputation_add_repcomment'       => array(
			'title'       => $lang->tyl_reputation_add_repcomment_title,
			'description' => $lang->tyl_reputation_add_repcomment_desc,
			'optionscode' => 'text',
			'value'       => ''
		),
		'closedthreads'                   => array(
			'title'       => $lang->tyl_closedthreads_title,
			'description' => $lang->tyl_closedthreads_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'exclude'                         => array(
			'title'       => $lang->tyl_exclude_title,
			'description' => $lang->tyl_exclude_desc,
			'optionscode' => 'forumselect',
			'value'       => ''
		),
		'exclude_count'                   => array(
			'title'       => $lang->tyl_exclude_count_title,
			'description' => $lang->tyl_exclude_count_desc,
			'optionscode' => 'forumselect',
			'value'       => ''
		),
		'unameformat'                     => array(
			'title'       => $lang->tyl_unameformat_title,
			'description' => $lang->tyl_unameformat_desc,
			'optionscode' => 'yesno',
			'value'       => '1'
		),
		'hideforgroups'                   => array(
			'title'       => $lang->tyl_hideforgroups_title,
			'description' => $lang->tyl_hideforgroups_desc,
			'optionscode' => 'groupselect',
			'value'       => '1,7'
		),
		'showdt'                          => array(
			'title'       => $lang->tyl_showdt_title,
			'description' => $lang->tyl_showdt_desc,
			'optionscode' => "radio\nnone={$lang->tyl_showdt_op_1}\nnexttoname={$lang->tyl_showdt_op_2}\nastitle={$lang->tyl_showdt_op_3}",
			'value'       => 'astitle'
		),
		'dtformat'                        => array(
			'title'       => $lang->tyl_dtformat_title,
			'description' => $lang->tyl_dtformat_desc,
			'optionscode' => 'text',
			'value'       => 'm-d-Y'
		),
		'sortorder'                       => array(
			'title'       => $lang->tyl_sortorder_title,
			'description' => $lang->tyl_sortorder_desc,
			'optionscode' => "select\nuserasc={$lang->tyl_sortorder_op_1}\nuserdesc={$lang->tyl_sortorder_op_2}\ndtasc={$lang->tyl_sortorder_op_3}\ndtdesc={$lang->tyl_sortorder_op_4}",
			'value'       => 'userasc'
		),
		'collapsible'                     => array(
			'title'       => $lang->tyl_collapsible_title,
			'description' => $lang->tyl_collapsible_desc,
			'optionscode' => 'yesno',
			'value'       => '1'
		),
		'colldefault'                     => array(
			'title'       => $lang->tyl_colldefault_title,
			'description' => $lang->tyl_colldefault_desc,
			'optionscode' => "radio\nopen={$lang->tyl_colldefault_op_1}\nclosed={$lang->tyl_colldefault_op_2}",
			'value'       => 'open'
		),
		'hidelistforgroups'               => array(
			'title'       => $lang->tyl_hidelistforgroups_title,
			'description' => $lang->tyl_hidelistforgroups_desc,
			'optionscode' => 'groupselect',
			'value'       => ''
		),
		'rcvdlikesclassranges'            => array(
			'title'       => $lang->tyl_rcvdlikesclassranges_title,
			'description' => $lang->tyl_rcvdlikesclassranges_desc,
			'optionscode' => 'text',
			'value'       => '1'
		),
		'displaygrowl'                    => array(
			'title'       => $lang->tyl_displaygrowl_title,
			'description' => $lang->tyl_displaygrowl_desc,
			'optionscode' => 'onoff',
			'value'       => '1'
		),
		'limits'                          => array(
			'title'       => $lang->tyl_limits_title,
			'description' => $lang->tyl_limits_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'highlight_popular_posts'         => array(
			'title'       => $lang->tyl_highlight_popular_posts_title,
			'description' => $lang->tyl_highlight_popular_posts_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'highlight_popular_posts_count'   => array(
			'title'       => $lang->tyl_highlight_popular_posts_count_title,
			'description' => $lang->tyl_highlight_popular_posts_count_desc,
			'optionscode' => 'numeric \n min=0',
			'value'       => '0'
		),
		'display_tyl_counter_forumdisplay'   => array(
			'title'       => $lang->tyl_display_tyl_counter_forumdisplay_title,
			'description' => $lang->tyl_display_tyl_counter_forumdisplay_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'display_tyl_counter_search_page'   => array(
			'title'       => $lang->tyl_display_tyl_counter_search_page_title,
			'description' => $lang->tyl_display_tyl_counter_search_page_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'show_memberprofile_box'          => array(
			'title'       => $lang->tyl_show_memberprofile_box_title,
			'description' => $lang->tyl_show_memberprofile_box_desc,
			'optionscode' => 'yesno',
			'value'       => '1'
		),
		'profile_box_post_cutoff'         => array(
			'title'       => $lang->tyl_profile_box_post_cutoff_title,
			'description' => $lang->tyl_profile_box_post_cutoff_desc,
			'optionscode' => 'numeric \n min=0',
			'value'       => '0'
		),
		'profile_box_show_parent_forums'  => array(
			'title'       => $lang->tyl_profile_box_show_parent_forums_title,
			'description' => $lang->tyl_profile_box_show_parent_forums_description,
			'optionscode' => 'yesno',
			'value'       => '1',
		),
		'profile_box_post_allowhtml'      => array(
			'title'       => $lang->tyl_profile_box_post_allowhtml_title,
			'description' => $lang->tyl_profile_box_post_allowhtml_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'profile_box_post_allowmycode'    => array(
			'title'       => $lang->tyl_profile_box_post_allowmycode_title,
			'description' => $lang->tyl_profile_box_post_allowmycode_desc,
			'optionscode' => 'yesno',
			'value'       => '1'
		),
		'profile_box_post_allowsmilies'   => array(
			'title'       => $lang->tyl_profile_box_post_allowsmilies_title,
			'description' => $lang->tyl_profile_box_post_allowsmilies_desc,
			'optionscode' => 'yesno',
			'value'       => '1'
		),
		'profile_box_post_allowimgcode'   => array(
			'title'       => $lang->tyl_profile_box_post_allowimgcode_title,
			'description' => $lang->tyl_profile_box_post_allowimgcode_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'profile_box_post_allowvideocode' => array(
			'title'       => $lang->tyl_profile_box_post_allowvideocode_title,
			'description' => $lang->tyl_profile_box_post_allowvideocode_desc,
			'optionscode' => 'yesno',
			'value'       => '0'
		),
		'show_memberprofile_stats'        => array(
			'title'       => $lang->tyl_show_memberprofile_stats_title,
			'description' => $lang->tyl_show_memberprofile_stats_desc,
			'optionscode' => 'yesno',
			'value'       => '1'
		),
	);

	$x = 1;
	foreach($settings as $name => $setting)
	{
		$value = isset($existing_setting_values[$prefix.$name]) ? $existing_setting_values[$prefix.$name] : $setting['value'];
		$insert_settings = array(
			'name' => $db->escape_string($prefix.$name),
			'title' => $db->escape_string($setting['title']),
			'description' => $db->escape_string($setting['description']),
			'optionscode' => $setting['optionscode'],
			// ...keeping any existing values.
			'value' => $db->escape_string($value),
			'disporder' => $x,
			'gid' => $gid,
			'isdefault' => 0
		);
		$db->insert_query('settings', $insert_settings);
		$x++;
	}

	rebuild_settings();
}

/**
 * Where necessary, create the plugin's tables in the database and
 * add to core MyBB tables those columns needed for this plugin.
 */
function tyl_check_update_db_table_and_cols($from_version = false)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	if(!$db->field_exists('tyl_pnumtyls', 'posts'))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."posts ADD `tyl_pnumtyls` int(100) NOT NULL default '0'");
	}
	if(!$db->field_exists('tyl_last_alerted_tyl_id', 'posts'))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."posts ADD `tyl_last_alerted_tyl_id` int unsigned NOT NULL default '0'");
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

	if(!$db->field_exists('tyl_lastadddeldate', 'users'))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."users ADD `tyl_lastadddeldate` int unsigned NOT NULL default '0'");
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
				KEY uid_dateline (uid, dateline),
				KEY puid_dateline (puid, dateline),
				KEY puid_uid (puid, uid),
				KEY puid_pid (puid, pid),
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
	if(!$db->field_exists("tyl_flood_interval", "usergroups"))
	{
		$db->add_column("usergroups", "tyl_flood_interval", "int unsigned NOT NULL DEFAULT '10'");
	}
}

function tyl_insert_templates()
{
	global $mybb, $db;

	$tyl_templates = array(
		'thankyoulike_postbit' =>
		array(
			'template' => "<div class=\"post_controls tyllist {\$unapproved_shade}\">
	{\$tyl_expcol}
	<span id=\"tyl_title_{\$post['pid']}\" style=\"{\$tyl_title_display}\">{\$lang->tyl_title}</span><span id=\"tyl_title_collapsed_{\$post['pid']}\" style=\"{\$tyl_title_display_collapsed}\">{\$lang->tyl_title_collapsed}</span><br />
	<span id=\"tyl_data_{\$post['pid']}\" style=\"{\$tyl_data_display}\">&nbsp;&nbsp;• {\$post['thankyoulike']}</span>
</div>",
			'version_at_last_change' => '20200',
		),
		'thankyoulike_postbit_classic' =>
		array(
			'template' => "<div class=\"post_controls tyllist_classic {\$unapproved_shade}\">
	{\$tyl_expcol}
	<span id=\"tyl_title_{\$post['pid']}\" style=\"{\$tyl_title_display}\">{\$lang->tyl_title}</span><span id=\"tyl_title_collapsed_{\$post['pid']}\" style=\"{\$tyl_title_display_collapsed}\">{\$lang->tyl_title_collapsed}</span><br />
	<span id=\"tyl_data_{\$post['pid']}\" style=\"{\$tyl_data_display}\">&nbsp;&nbsp;• {\$post['thankyoulike']}</span>
</div>",
			'version_at_last_change' => '20200',
		),
		'thankyoulike_postbit_avatars' =>
		array(
			'template' => "<div class=\"post_controls tyllist {\$unapproved_shade}\">
	{\$tyl_expcol}
	<span id=\"tyl_title_{\$post['pid']}\" style=\"{\$tyl_title_display}\">{\$lang->tyl_title}</span><span id=\"tyl_title_collapsed_{\$post['pid']}\" style=\"{\$tyl_title_display_collapsed}\">{\$lang->tyl_title_collapsed}</span><br />
	<span id=\"tyl_data_{\$post['pid']}\" style=\"{\$tyl_data_display}\">&nbsp;&nbsp; {\$post['thankyoulike']}</span>
</div>",
			'version_at_last_change' => '30307',
		),
		'thankyoulike_postbit_classic_avatars' =>
		array(
			'template' => "<div class=\"post_controls tyllist_classic {\$unapproved_shade}\">
	{\$tyl_expcol}
	<span id=\"tyl_title_{\$post['pid']}\" style=\"{\$tyl_title_display}\">{\$lang->tyl_title}</span><span id=\"tyl_title_collapsed_{\$post['pid']}\" style=\"{\$tyl_title_display_collapsed}\">{\$lang->tyl_title_collapsed}</span><br />
	<span id=\"tyl_data_{\$post['pid']}\" style=\"{\$tyl_data_display}\">&nbsp;&nbsp; {\$post['thankyoulike']}</span>
</div>",
			'version_at_last_change' => '30307',
		),
		'thankyoulike_tyl_counter_forumdisplay_thread' =>
		array(
			'template' => "<span title=\"{\$lang->tyl_firstpost_tyl_count_forumdisplay_thread}\" class=\"tyl_counter\">{\$thread['tyls']}</span>",
			'version_at_last_change' => '30300',
		),
		'thankyoulike_tyl_counter_search_page' =>
		array(
			'template' => "<span title=\"{\$lang->tyl_firstpost_tyl_count_search_page}\" class=\"tyl_counter\">{\$thread['tyls']}</span>",
			'version_at_last_change' => '30308',
		),
		'thankyoulike_expcollapse' =>
		array(
			'template' => "<a href=\"javascript:void(0)\" onclick=\"thankyoulike.tgl({\$post['pid']});return false;\" title=\"{\$tyl_showhide}\" id=\"tyl_a_expcol_{\$post['pid']}\"><img src=\"{\$theme['imgdir']}/{\$tyl_expcolimg}\" alt=\"{\$tyl_showhide}\" id=\"tyl_i_expcol_{\$post['pid']}\" /></a> ",
			'version_at_last_change' => '20000',
		),
		'thankyoulike_button_add' =>
		array(
			'template' => "<a class=\"add_tyl_button\" href=\"thankyoulike.php?action=add&amp;pid={\$post['pid']}&amp;my_post_key={\$mybb->post_code}\" onclick=\"thankyoulike.add({\$post['pid']}, {\$post['tid']}); return false;\" title=\"{\$lang->add_tyl_button_title}\" id=\"tyl_btn_{\$post['pid']}\"><span id=\"tyl_i{\$post['pid']}\">{\$lang->add_tyl}</span></a>",
			'version_at_last_change' => '30302',
		),
		'thankyoulike_button_del' =>
		array(
			'template' => "<a class=\"del_tyl_button\" href=\"thankyoulike.php?action=del&amp;pid={\$post['pid']}&amp;my_post_key={\$mybb->post_code}\" onclick=\"thankyoulike.del({\$post['pid']}, {\$post['tid']}); return false;\" title=\"{\$lang->del_tyl_button_title}\" id=\"tyl_btn_{\$post['pid']}\"><span id=\"tyl_i{\$post['pid']}\">{\$lang->del_tyl}</span></a>",
			'version_at_last_change' => '30302',
		),
		'thankyoulike_users' =>
		array(
			'template' => "<span class=\"smalltext\">{\$comma}</span><a href=\"{\$profile_link}\" class=\"smalltext\" {\$datedisplay_title}>{\$tyl_list}</a>{\$datedisplay_next}",
			'version_at_last_change' => '20000',
		),
		'thankyoulike_users_avatars' =>
		array(
			'template' => "<a href=\"{\$profile_link}\" class=\"smalltext\" title=\"{\$tyl['username']} ({\$datedisplay_plain})\"><img alt=\"\" src=\"{\$avatar['image']}\" class=\"tyl_avatar\" /></a>",
			'version_at_last_change' => '30307',
		),
		'thankyoulike_postbit_author_user' =>
		array(
			'template' => "{\$lang->tyl_rcvd}: {\$post['tyl_unumrcvtyls_fmt']}
<br />
{\$lang->tyl_given}: {\$post['tyl_unumtyls_fmt']}",
			'version_at_last_change' => '30308',
		),
		'thankyoulike_member_profile_rcvd_search' =>
		array(
			'template'            => "<span class=\"smalltext\">(<a href=\"tylsearch.php?action=usertylforthreads&amp;uid={\$uid}\">{\$lang->tyl_find_threads_for}</a> &mdash; <a href=\"tylsearch.php?action=usertylforposts&amp;uid={\$uid}\">{\$lang->tyl_find_posts_for}</a>)</span>",
			'version_at_last_change' => '30307',
		),
		'thankyoulike_member_profile_given_search' =>
		array(
			'template'            => "<span class=\"smalltext\">(<a href=\"tylsearch.php?action=usertylthreads&amp;uid={\$uid}\">{\$lang->tyl_find_threads}</a> &mdash; <a href=\"tylsearch.php?action=usertylposts&amp;uid={\$uid}\">{\$lang->tyl_find_posts}</a>)</span>",
			'version_at_last_change' => '30307',
		),
		'thankyoulike_member_profile' =>
		array(
			'template' => "<tr>
	<td class=\"trow1\"><strong>{\$lang->tyl_total_tyls_rcvd}</strong></td>
	<td class=\"trow1\">{\$memprofile['tyl_unumrcvtyls_fmt']} ({\$tylrcvpd_percent_total})<br />{\$tylrcvdlikessearch}</td>
</tr>
<tr>
	<td class=\"trow2\"><strong>{\$lang->tyl_total_tyls_given}</strong></td>
	<td class=\"trow2\">{\$memprofile['tyl_unumtyls_fmt']} ({\$tylpd_percent_total})<br />{\$tylgivenlikessearch}</td>
</tr>",
			'version_at_last_change' => '30308',
		),
		'thankyoulike_member_profile_box' =>
		array(
			'template' => "<table border=\"0\" cellspacing=\"{\$theme['borderwidth']}\" cellpadding=\"{\$theme['tablespace']}\" width=\"100%\" class=\"tborder\">
<tr>
<td colspan=\"3\" class=\"thead\"><strong>{\$lang->tyl_profile_box_thead}</strong></td>
</tr>
{\$tyl_profile_box_content}
</table>
<br />",
			'version_at_last_change' => '30307',
		),
		'thankyoulike_member_profile_box_continue_reading' =>
		array(
			'template' => "<div style=\"text-align: right;\"><a href=\"{\$postlink}\">{\$lang->tyl_profile_box_continue_reading}</a></div>",
			'version_at_last_change' => '30307',
		),
		'thankyoulike_forum_separator' =>
		array(
			'template' => '&raquo;',
			'version_at_last_change' => '30308',
		),
		'thankyoulike_forum_separator_last' =>
		array(
			'template' => '<br /><img src="images/nav_bit.png" alt="" />',
			'version_at_last_change' => '30308',
		),
		'thankyoulike_forum_link' =>
		array(
			'template' => '<a href="{$forum_url}">{$forum_name}</a>',
			'version_at_last_change' => '30308',
		),
		'thankyoulike_member_profile_box_content' =>
		array(
			'template' => "<tr>
	<td class=\"trow2\"><span class=\"smalltext\">{\$lang->tyl_profile_box_subject}</span></td>
	<td class=\"trow2\"><span class=\"smalltext\">{\$lang->tyl_profile_box_datetime}</span></td>
	<td class=\"trow2\" align=\"center\"><span class=\"smalltext\">{\$lang->tyl_profile_box_number}</span></td>
</tr>
<tr>
	<td class=\"trow1\"><strong>{\$memprofile['tylsubject']}</strong></td>
	<td class=\"trow1\">{\$memprofile['tyldatetime']}</td>
	<td class=\"trow1\"align=\"center\" rowspan=\"3\"><span style=\"font-size: 45px; font-weight: bold;\">{\$memprofile['tylcount']}</span></td>
</tr>
<tr>
	<td class=\"trow2\"><span class=\"smalltext\">{\$lang->tyl_profile_box_thread}</span></td>
	<td class=\"trow2\" style=\"border-right: 1px solid #ddd;\"><span class=\"smalltext\">{\$lang->tyl_profile_box_forum}</span></td>
</tr>
<tr>
	<td class=\"trow1\">{\$memprofile['threadprefix']}{\$memprofile['tylthreadname']}</td>
	<td class=\"trow1\" style=\"border-right: 1px solid #ddd;\">{\$memprofile['tylforums']}</td>
</tr>
<tr>
	<td class=\"trow2\" colspan=\"3\"><span class=\"smalltext\">{\$lang->tyl_profile_box_message}</span></td>
</tr>
<tr>
	<td class=\"trow1 scaleimages\" colspan=\"3\">{\$memprofile['tylmessage']}</td>
</tr>",
			'version_at_last_change' => '30308',
		),
		'thankyoulike_member_profile_box_content_none' =>
		array(
			'template' => "<tr>
<td class=\"trow1\" colspan=\"2\">{\$lang->tyl_profile_box_content_none}</td>
</tr>",
			'version_at_last_change' => '20300',
		),
		'thankyoulike_member_profile_like_stats' =>
		array(
			'template' => "<table border=\"0\" cellspacing=\"{\$theme['borderwidth']}\" cellpadding=\"{\$theme['tablespace']}\" width=\"100%\" class=\"tborder\">
	<tr>
		<td colspan=\"3\" class=\"thead\"><strong>{\$tyl_profile_stats_head}</strong></td>
	</tr>
	<tr>
		<td class=\"trow2\">&nbsp;</td>
		<td class=\"trow2\" style=\"text-align: center;\"><strong><a href=\"tylsearch.php?action=usertylforposts&amp;uid={\$uid}\">{\$lang->tyl_rcvd}</a></strong></td>
		<td class=\"trow2\" style=\"text-align: center;\"><strong><a href=\"tylsearch.php?action=usertylposts&amp;uid={\$uid}\">{\$lang->tyl_given}</a></strong></td>
	</tr>
	<tr>
		<td class=\"trow1\" style=\"text-align: right;\">{\$lang->tyl_last_week}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_received_week}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_given_week}</td>
	</tr>
	<tr>
		<td class=\"trow1\" style=\"text-align: right;\">{\$lang->tyl_last_month}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_received_month}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_given_month}</td>
	</tr>
	<tr>
		<td class=\"trow1\" style=\"text-align: right;\">{\$lang->tyl_last_3months}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_received_3months}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_given_3months}</td>
	</tr>
	<tr>
		<td class=\"trow1\" style=\"text-align: right;\">{\$lang->tyl_last_6months}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_received_6months}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_given_6months}</td>
	</tr>
	<tr>
		<td class=\"trow1\" style=\"text-align: right;\">{\$lang->tyl_last_12months}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_received_12months}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_given_12months}</td>
	</tr>
	<tr>
		<td class=\"trow1\" style=\"text-align: right;\">{\$lang->tyl_all_time}</td>
		<td class=\"trow1\" style=\"text-align: center;\"><strong>{\$tyl_received_all}</strong></td>
		<td class=\"trow1\" style=\"text-align: center;\"><strong>{\$tyl_given_all}</strong></td>
	</tr>
	<tr>
		<td class=\"trow1\" colspan=\"3\">&nbsp;</td>
	</tr>
	<tr>
		<td class=\"trow2\" colspan=\"3\" style=\"text-align: center;\"><strong>{\$lang->tyl_most_tyled_by}</strong></td>
	</tr>
	{\$tyl_most_likedby_users}
	<tr>
		<td class=\"trow1\" colspan=\"3\">&nbsp;</td>
	</tr>
	<tr>
		<td class=\"trow2\" colspan=\"3\" style=\"text-align: center;\"><strong>{\$lang->tyl_most_tyled}</strong></td>
	</tr>
	{\$tyl_most_liked_users}
</table>
<br />
",
			'version_at_last_change' => '30309',
		),
		'thankyoulike_member_profile_like_stats_most_likedby_row' =>
		array(
			'template' => "	<tr>
		<td class=\"trow1\" style=\"text-align: right;\">{\$tyl_member_link}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_cnt}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_cnt_pc}</td>
	</tr>
",
			'version_at_last_change' => '30308',
		),
		'thankyoulike_member_profile_like_stats_most_liked_row' =>
		array(
			'template' => "	<tr>
		<td class=\"trow1\" style=\"text-align: right;\">{\$tyl_member_link}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_cnt}</td>
		<td class=\"trow1\" style=\"text-align: center;\">{\$tyl_cnt_pc}</td>
	</tr>
",
			'version_at_last_change' => '30308',
		),
		'thankyoulike_member_link' =>
		array(
			'template' => "<a href=\"{\$tyl_profilelink}\">{\$tyl_username}</a>",
			'version_at_last_change' => '30308',
		),
		'thankyoulike_member_profile_like_stats_no_most_liked' =>
		array(
			'template' => "	<tr>
		<td class=\"trow1\" colspan=\"3\" style=\"text-align: center;\">{\$lang->tyl_no_most_tyled}</td>
	</tr>
",
			'version_at_last_change' => '30308',
		),
		'thankyoulike_member_profile_like_stats_no_most_likedby' =>
		array(
			'template' => "	<tr>
		<td class=\"trow1\" colspan=\"3\" style=\"text-align: center;\">{\$lang->tyl_no_most_tyledby}</td>
	</tr>
",
			'version_at_last_change' => '30308',
		),
	);

	// Could be zero (false) if we are installing or if upgrading a very old installation.
	$from_version = tyl_get_installed_version();
	foreach($tyl_templates as $template_title => $template_data)
	{
		// First, flag any of this plugin's templates that have been modified in the plugin since
		// the version of the plugin from which we are upgrading, flagging all templates if that
		// version number is not available. This ensures that Find Updated Templates detects them
		// *if* the user has also modified them, and without false positives. The way we flag them
		// is to zero the `version` column of the `templates` table where `sid` is not -2 for this
		// plugin's templates.
		if ($template_data['version_at_last_change'] > $from_version) {
			$db->update_query('templates', array('version' => 0), "title='{$template_title}' and sid <> -2");
		}

		// Now insert/update master templates with SID -2.
		$insert_templates = array(
			'title'    => $db->escape_string($template_title),
			'template' => $db->escape_string($template_data['template']),
			'sid'      => "-2",
			'version'  => '1',
			'dateline' => TIME_NOW
		);
		$db->insert_query('templates', $insert_templates);
	}
}

function tyl_create_templategroup()
{
	global $db;

	// Insert Template elements
	$templateset = array(
		"prefix" => "thankyoulike",
		"title" => "Thank You/Like",
	);
	$db->insert_query("templategroups", $templateset);
}

/**
 * Returns the CSS for the thankyoulike.css stylesheet for the current version of the plugin.
 * @return string The stylesheet's CSS.
 */
function tyl_get_thankyoulike_css()
{
	return "div[id^=tyl_btn_] {
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
}

.tyl_counter{
	border: 1px solid #ccc;
	border-radius: 3px;
	background-color: #ddd;
	color: #333;
	padding: 1px 5px;
	float: right;
	margin: 4px 5px 0px 10px;
	font-weight: bold;
}

.tyl_avatar{
	width: 25px;
	height: 25px;
	border-radius: 12.5px;
	margin-right: -4px;
}

.tyl_rcvdlikesrange_1, .tyl_rcvdlikesrange_1 a{
	font-weight: bold;
	text-decoration: none;
	color: green;
}

.jGrowl .jGrowl-notification.jgrowl_success.tyl_jgrowl,
.jGrowl .jGrowl-notification.jgrowl_error.tyl_jgrowl{
	background: black;
	border: 1px solid black;
	color: white;
}

.jGrowl .jGrowl-notification.jgrowl_success.tyl_jgrowl .jGrowl-close,
.jGrowl .jGrowl-notification.jgrowl_error.tyl_jgrowl .jGrowl-close {
	color: black;
}
";
}

/**
 * Create the thankyoulike.css stylesheet in the Master theme.
 */
function tyl_create_stylesheet()
{
	global $db;

	$css = array(
		"name" => "thankyoulike.css",
		"tid" => 1,
		"attachedto" => "showthread.php|forumdisplay.php|search.php|member.php",
		"stylesheet" => tyl_get_thankyoulike_css(),
		"cachefile" => $db->escape_string(str_replace('/', '', 'thankyoulike.css')),
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
}

/**
 * Perform the tasks in common between installing and upgrading.
 * @param boolean $from_version Set to version from which we are upgrading if upgrading; false if installing.
 */
function tyl_install_upgrade_common($from_version = false)
{
	global $cache, $lang;
	$lang->load('config_thankyoulike');
	$info = thankyoulike_info();

	// Where necessary, create the plugin's tables in the database and
	// add to core MyBB tables those columns needed for this plugin.
	tyl_check_update_db_table_and_cols($from_version);

	// (Re)create the plugin's template group and templates.
	tyl_create_templategroup();
	tyl_insert_templates();

	// (Re)create the thankyoulike.css stylesheet for the Master theme.
	// Does not affect any changes made to the stylesheet for specific themes,
	// so the admin may need to update those after viewing the stylesheet via the
	// "View the Master theme's thankyoulike.css" link in the plugin's entry in
	// the ACP's "Active Plugins" page.
	tyl_create_stylesheet();

	// Now that we've installed or upgraded the plugin, store its installed version into its stats table
	// for use when checking whether to upgrade it.
	tyl_set_installed_version($info['version_code']);

	// Integrate with MyAlerts if possible.
	tyl_myalerts_integrate();

	$cache->update_usergroups();
	$cache->update_forums();
	$cache->update_tasks();
}

function thankyoulike_install()
{
	// Run preinstall cleanup.
	tyl_preinstall_cleanup();

	// Create the plugin's settings.
	tyl_create_settings();

	// Perform the tasks in common between installing and upgrading.
	tyl_install_upgrade_common();
}

function thankyoulike_is_installed()
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	// Keep the check for installation very minimal so that we catch earlier versions and thus can upgrade them via thankyoulike_activate().
	$result = $db->simple_select('settinggroups', 'gid', "name = '".$db->escape_string($prefix.'settings')."'", array('limit' => 1));
	$settinggroup = $db->fetch_array($result);
	if(!empty($settinggroup['gid']))
	{
		return true;
	}
	return false;
}

/**
 * Get the integer form of the installed version of the plugin as derived from
 * thankyoulike_info()['version_code'] and stored in the plugin's stats table.
 * Useful for checking on activate whether or not we need to upgrade.
 *
 * This functionality was not present in versions 2.3.0 and earlier, and this
 * function will return false for those versions.
 * @return integer The currently-installed version of the plugin, or false
 *                 if either the version <= 2.3.0 or the plugin is not
 *                 currently installed.
 */
function tyl_get_installed_version()
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	$options = array(
			"limit" => 1
	);

	$query = $db->simple_select($prefix."stats", "*", "title='version'", $options);
	$version = $db->fetch_array($query);
	if ($version)
	{
		$version = $version['value'];
	}

	return $version;
}

/**
 * Set (by storing into the plugin's stats table) the currently-installed version of the plugin.
 * @param string $version_code The version of the plugin per thankyoulike_info()['version_code'].
 */
function tyl_set_installed_version($version_code)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	// Delete existing stored version (if any).
	$db->delete_query($prefix."stats", "title='version'");
	// Set stored version to that supplied.
	$version_data = array(
		"title" => "version",
		"value" => $version_code
	);
	$db->insert_query($prefix."stats", $version_data);
}

function tyl_upgrade($from_version)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	// Currently, we don't use $from_version, but potentially it will be used
	// in the future either to delete defunct data structures removed since that
	// old version or to update the definitions of columns that have changed
	// since that old version.

	// First, save existing values for the plugin's settings.
	$existing_setting_values = array();
	$result = $db->simple_select('settinggroups', 'gid', "name = '{$prefix}settings'", array('limit' => 1));
	$group = $db->fetch_array($result);
	if(!empty($group['gid']))
	{
		$query = $db->simple_select('settings', 'value, name', "gid='{$group['gid']}'");
		while($setting = $db->fetch_array($query))
		{
			$existing_setting_values[$setting['name']] = $setting['value'];
		}
	}

	// Now, run the cleanup with the $for_upgrade parameter set true. Amongst other things,
	// most notably deleting the plugin's existing Master (sid=-2) templates, this will
	// delete all settings, which is why we save their values above.
	tyl_preinstall_cleanup(/*$for_upgrade=*/true);

	// Now, recreate and rebuild settings, so that any new settings and any rewordings/changes
	// to existing settings take effect, but also ensuring that old values are kept where they exist,
	// saving admins from having to re-enter them.
	tyl_create_settings($existing_setting_values);

	// Delete any existing thankyoulike.css stylesheet in the Master theme (tid=1).
	$db->delete_query("themestylesheets", "name = 'thankyoulike.css' AND tid = 1");

	// Perform the tasks in common between installing and upgrading.
	tyl_install_upgrade_common($from_version);
}

function thankyoulike_activate()
{
	global $db, $lang, $tyl_plugin_upgrade_message;

	$info = thankyoulike_info();
	$from_version = tyl_get_installed_version();
	$to_version   = $info['version_code'];
	if($from_version != $to_version)
	{
		// Do upgrade.
		tyl_upgrade($from_version);
		$tyl_plugin_upgrade_message = $lang->sprintf($lang->tyl_successful_upgrade_msg, $lang->tyl_info_title, $info['version']);
		update_admin_session('tyl_plugin_info_upgrade_message', $lang->sprintf($lang->tyl_successful_upgrade_msg_for_info, $info['version']));
	}
	else
	{
		// (Re)create the thankyoulike.css Master stylesheet.
		//
		// We do this here to make it easy for admins to recreate stylesheets deleted
		// on MyBB core upgrade[1]: it is as easy as deactivating and then reactivating
		// the plugin.
		//
		// [1] See https://community.mybb.com/thread-229633.html

		// First, delete any existing thankyoulike.css stylesheet in the Master theme (tid=1).
		$db->delete_query("themestylesheets", "name = 'thankyoulike.css' AND tid = 1");

		// Now (re)create the Master stylesheet.
		tyl_create_stylesheet();
	}

	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets("showthread", "#".preg_quote('</head>')."#i", '<script type="text/javascript" src="{$mybb->settings[\'bburl\']}/jscripts/thankyoulike.min.js?ver=30309"></script>
<script type="text/javascript">
<!--
	var tylEnabled = "{$mybb->settings[\'g33k_thankyoulike_enabled\']}";
	var tylDisplayGrowl = "{$mybb->settings[\'g33k_thankyoulike_displaygrowl\']}";
	var tylCollapsible = "{$mybb->settings[\'g33k_thankyoulike_collapsible\']}";
	var tylCollDefault = "{$mybb->settings[\'g33k_thankyoulike_colldefault\']}";
	var tylUser = "{$mybb->user[\'uid\']}";
	var tylSend = "{$lang->tyl_send}";
	var tylRemove = "{$lang->tyl_remove}";
// -->
</script>
</head>');

	find_replace_templatesets("postbit","#".preg_quote('<div class="post_content">')."#i","<div class=\"post_content{\$post['styleclass']}\">");
	find_replace_templatesets("postbit_classic","#".preg_quote('<div class="post_content">')."#i","<div class=\"post_content{\$post['styleclass']}\">");
	find_replace_templatesets("postbit_classic","#".preg_quote('<div class="post_controls">')."#i","<div style=\"{\$post['tyl_display']}\" id=\"tyl_{\$post['pid']}\">{\$post['thankyoulike_data']}</div>\n<div class=\"post_controls\">");
	find_replace_templatesets("postbit","#".preg_quote('<div class="post_controls">')."#i","<div style=\"{\$post['tyl_display']}\" id=\"tyl_{\$post['pid']}\">{\$post['thankyoulike_data']}</div>\n<div class=\"post_controls\">");
	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'button_edit\']}')."#i", '{$post[\'button_tyl\']}{$post[\'button_edit\']}');
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'button_edit\']}')."#i", '{$post[\'button_tyl\']}{$post[\'button_edit\']}');
	find_replace_templatesets("postbit_author_user", "#".preg_quote('{$lang->postbit_threads} {$post[\'threadnum\']}<br />')."#i", '{$lang->postbit_threads} {$post[\'threadnum\']}<br />
	%%TYL_NUMTHANKEDLIKED%%<br />');
	find_replace_templatesets("member_profile", '#{\$reputation}(\r?)\n#', "{\$tyl_memprofile}\n\t\t\t\t{\$reputation}\n");
	find_replace_templatesets("member_profile", '#{\$modoptions}(\r?)\n#', "{\$tyl_profile_box}\n\t\t\t{\$tyl_profile_stats}\n\t\t\t{\$modoptions}\n");
	find_replace_templatesets("forumdisplay_thread","#".preg_quote('{$attachment_count}')."#i","{\$tyl_forumdisplay_thread_var}{\$attachment_count}");
	find_replace_templatesets("search_results_threads_thread","#".preg_quote('{$attachment_count}')."#i","{\$tyl_search_page_var}{\$attachment_count}");

	// Enable the tyl alert type if necessary.
	tyl_myalerts_set_enabled(1);
}

function thankyoulike_deactivate()
{
	require_once MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets("showthread", "#".preg_quote('<script type="text/javascript" src="{$mybb->settings[\'bburl\']}/jscripts/thankyoulike.min.js').'(\\?ver=\\d+)?'.preg_quote('"></script>
<script type="text/javascript">
<!--
	var tylEnabled = "{$mybb->settings[\'g33k_thankyoulike_enabled\']}";
	var tylDisplayGrowl = "{$mybb->settings[\'g33k_thankyoulike_displaygrowl\']}";
	var tylCollapsible = "{$mybb->settings[\'g33k_thankyoulike_collapsible\']}";
	var tylCollDefault = "{$mybb->settings[\'g33k_thankyoulike_colldefault\']}";
	var tylUser = "{$mybb->user[\'uid\']}";
	var tylSend = "{$lang->tyl_send}";
	var tylRemove = "{$lang->tyl_remove}";
// -->
</script>
')."#i", '', 0);

	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'styleclass\']}')."#i", '', 0);
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'styleclass\']}')."#i", '', 0);
	find_replace_templatesets("postbit", "#".preg_quote('<div style="{$post[\'tyl_display\']}" id="tyl_{$post[\'pid\']}">{$post[\'thankyoulike_data\']}</div>')."(\r?)\n#", '', 0);
	find_replace_templatesets("postbit_classic", "#".preg_quote('<div style="{$post[\'tyl_display\']}" id="tyl_{$post[\'pid\']}">{$post[\'thankyoulike_data\']}</div>')."(\r?)\n#", '', 0);
	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'button_tyl\']}')."#i", '', 0);
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'button_tyl\']}')."#i", '', 0);
	find_replace_templatesets("postbit_author_user", "#".preg_quote('
	%%TYL_NUMTHANKEDLIKED%%<br />')."#i", '', 0);
	find_replace_templatesets("member_profile", '#(\t*)?{\$tyl_memprofile}(\r?)(\n?)#', "", 0);
	find_replace_templatesets("member_profile", '#(\t*)?{\$tyl_profile_box}(\r?)(\n?)#', "", 0);
	find_replace_templatesets("member_profile", '#(\t*)?{\$tyl_profile_stats}(\r?)(\n?)#', "", 0);
	find_replace_templatesets("forumdisplay_thread", '#{\$tyl_forumdisplay_thread_var}#', "", 0);
	find_replace_templatesets("search_results_threads_thread", '#{\$tyl_search_page_var}#', "", 0);

	// Disable the tyl alert type if necessary.
	tyl_myalerts_set_enabled(0);
}

/**
 * Enables or disables the tyl alert type.
 * When disabling, existing tyl alerts aren't deleted from the database,
 * but become invisible to users unless/until the tyl alert type is re-enabled.
 * @param integer 0 or 1. 0 to disable; 1 to enable.
 */
function tyl_myalerts_set_enabled($enabled)
{
	global $db;

	if ($db->table_exists("alert_types"))
	{
		$db->update_query('alert_types', array('enabled' => $enabled), "code='tyl'");
		if (function_exists('reload_mybbstuff_myalerts_alert_types'))
		{
			reload_mybbstuff_myalerts_alert_types();
		}
	}
}

/**
 * Check whether a version of MyAlerts greater than 2.0.0 is present.
 * Optionally, check that it is activated too.
 * Optionally, check that the tyl alert type is registered too.
 * Optionally, check that any registered tyl alert type is also enabled.
 * @param boolean True iff an activation check should be performed.
 * @param boolean True iff a check for tyl alert type registration should be performed.
 * @param boolean True iff a check that any tyl alert type is enabled should be performed.
 * @return boolean True iff the check(s) succeeded.
 */
function tyl_have_myalerts($check_activated = false, $check_tyl_registered = false, $check_tyl_enabled = false)
{
	$ret = false;

	if(function_exists("myalerts_info")) {
		$myalerts_info = myalerts_info();
		if(version_compare($myalerts_info['version'], "2.0.0") >= 0
		   &&
		   (!$check_activated
		    ||
		    (function_exists("myalerts_is_activated") && myalerts_is_activated())
		   )
		  )
		{
			if (!$check_tyl_registered && !$check_tyl_enabled)
			{
				$ret = true;
			}
			else
			{
				global $cache;

				$alert_types = $cache->read('mybbstuff_myalerts_alert_types');

				if((!$check_tyl_registered || (isset($alert_types['tyl']['code'   ]) && $alert_types['tyl']['code'   ] == 'tyl'))
				   &&
				   (!$check_tyl_enabled    || (isset($alert_types['tyl']['enabled']) && $alert_types['tyl']['enabled'] ==    1 )))
				{
					$ret = true;
				}
			}
		}

	}

	return $ret;
}

/**
 * Fully unintegrate from the MyAlerts system.
 * Warning: deletes ALL alerts of type tyl along with the tyl alert type itself.
 */
function tyl_myalerts_unintegrate()
{
	global $db;

	if(tyl_have_myalerts())
	{
		$alertTypeManager = MybbStuff_MyAlerts_AlertTypeManager::getInstance();
		$alertType = $alertTypeManager->getByCode('tyl');
		if ($alertType !== null)
		{
			$id = $alertType->getId();
			if($id > 0)
			{
				// First delete the tyl alert type.
				$alertTypeManager->deleteById($id);

				if($db->table_exists("alerts") && $id > 0)
				{
					// Then delete all alerts of that type.
					$db->delete_query("alerts", "alert_type_id = '$id'");
				}
			}
		}
	}
}

/**
 * Remove plugin-specific settings.
 * @param boolean Set to true if rebuild_settings() should be run after removing settings.
 */
function tyl_remove_settings($rebuild_settings = true)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	$result = $db->simple_select('settinggroups', 'gid', "name = '{$prefix}settings'", array('limit' => 1));
	$group = $db->fetch_array($result);
	if(!empty($group['gid']))
	{
		$db->delete_query('settinggroups', "gid='{$group['gid']}'");
		$db->delete_query('settings', "gid='{$group['gid']}'");
		if ($rebuild_settings) rebuild_settings();
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

	// Remove plugin-specific settings.
	tyl_remove_settings();

	// Only remove the database tables if the admin has selected not to keep data.
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
		if($db->field_exists('tyl_lastadddeldate', 'users'))
		{
			$db->query("ALTER TABLE ".TABLE_PREFIX."users DROP column `tyl_lastadddeldate`");
		}
		if($db->field_exists('tyl_pnumtyls', 'posts'))
		{
			$db->query("ALTER TABLE ".TABLE_PREFIX."posts DROP column `tyl_pnumtyls`");
		}
		// Was dropped in 3.1.0; will not be present for that version and later.
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

		// Only unintegrate with MyAlerts (deleting all tyl alerts and the tyl alert type)
		// if the admin has selected not to keep data.
		tyl_myalerts_unintegrate();
	}
	else if($db->table_exists($prefix.'stats'))
	{
		// Remove the stored version so that upgrades are properly triggered when a downgrade is performed in between.
		$db->delete_query($prefix.'stats', "title='version'");
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

	// Remove from the usergroups table the fields for rate-limiting tyls.
	if($db->field_exists("tyl_limits_max", "usergroups"))
	{
		$db->drop_column("usergroups", "tyl_limits_max");
	}
	if($db->field_exists("tyl_flood_interval", "usergroups"))
	{
		$db->drop_column("usergroups", "tyl_flood_interval");
	}

	$cache->update_usergroups();
	$cache->update_forums();
	$cache->update_tasks();
}

function thankyoulike_templatelist()
{
	global $mybb, $lang, $templatelist;
	$prefix = 'g33k_thankyoulike_';
	if($mybb->settings[$prefix.'enabled'] == "1")
	{
		$lang->load('thankyoulike', false, true);
		if($mybb->settings[$prefix.'thankslike'] == "like")
		{
			$prelang = $lang->tyl_like;
			$key_tyl_noun_one_sm = 'tyl_like_sm';
		}
		else
		{
			$prelang = $lang->tyl_thankyou;
			$key_tyl_noun_one_sm = 'tyl_thankyou_sm';
		}
		$lang->myalerts_setting_tyl = $lang->sprintf($lang->myalerts_setting_tyl, $lang->$key_tyl_noun_one_sm);

		$lang->tyl_send = $lang->sprintf($lang->tyl_send, $prelang);
		$lang->tyl_remove = $lang->sprintf($lang->tyl_remove, $prelang);

		// Register alert formatter.
		if(tyl_have_myalerts(true, true, true) && $mybb->user['uid'])
		{
			tyl_myalerts_formatter_load();
		}

		// Cache all templates
		$template_list = '';
		if (THIS_SCRIPT == 'showthread.php')
		{
			$template_list = "thankyoulike_users,thankyoulike_postbit_author_user,thankyoulike_postbit,thankyoulike_postbit_classic,thankyoulike_postbit_avatars,thankyoulike_postbit_classic_avatars,thankyoulike_users_avatars,thankyoulike_expcollapse,thankyoulike_button_add,thankyoulike_button_del";
		}
		if (THIS_SCRIPT == 'forumdisplay.php')
		{
			$template_list = "thankyoulike_tyl_counter_forumdisplay_thread";
		}
		if (THIS_SCRIPT == 'search.php')
		{
			$template_list = "thankyoulike_tyl_counter_search_page";
		}
		if (THIS_SCRIPT == 'member.php')
		{
			$template_list = "thankyoulike_member_profile,thankyoulike_member_profile_box,thankyoulike_member_profile_box_content,thankyoulike_member_profile_box_continue_reading,thankyoulike_member_profile_rcvd_search,thankyoulike_member_profile_given_search,thankyoulike_forum_separator,thankyoulike_forum_link,thankyoulike_forum_separator_last,thankyoulike_member_profile_box_content_none,thankyoulike_member_profile_like_stats,thankyoulike_member_profile_like_stats_most_likedby_row,thankyoulike_member_profile_like_stats_most_liked_row,thankyoulike_member_profile_like_stats_no_most_liked,thankyoulike_member_profile_like_stats_no_most_likedby,thankyoulike_member_link";
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

/**
 * Count the number of the user's own posts that s/he has tyled and that
 * nobody else has.
 * @param integer The user's ID.
 * @return integer The post count.
 */
function tyl_get_own_single_tyl_post_count($uid)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	// Cache results as we may need them again
	// e.g. on a thread page where the same user has posted multiple times.
	static $owntylpostcounts = array();

	if(!isset($owntylpostcounts[$uid]))
	{
		$query = $db->query("SELECT
			COUNT(pid) AS owntylposts
			FROM ".TABLE_PREFIX.$prefix."thankyoulike
			WHERE uid='$uid' AND uid=puid AND pid NOT IN (
				SELECT pid FROM ".TABLE_PREFIX.$prefix."thankyoulike
				WHERE puid='$uid'
				GROUP BY pid
				HAVING COUNT(pid) > 1
				)
			");
		$owntylpostcounts[$uid] = $db->fetch_field($query, 'owntylposts');
	}

	return $owntylpostcounts[$uid];
}

/**
 * Removes self-likes from like counts in a post if the applicable plugin setting is enabled.
 *
 * @param array &$post The database row of the post. (Potentially) modified by the function.
 * @param boolean $skip_postcounts Whether, for efficiency when not needed, to skip removal
 *                                 of the count of single-like self-liked posts from
 *                                 the given-likes post count.
 */
function tyl_check_remove_self_likes_from_post_array(&$post, $skip_postcounts = false)
{
	global $mybb;
	$prefix = 'g33k_thankyoulike_';

	$post['tyl_unumrcvtyls_self_rem'] = $post['tyl_unumrcvtyls'];
	$post['tyl_unumtyls_self_rem'   ] = $post['tyl_unumtyls'   ];
	$post['tyl_unumptyls_self_rem'  ] = $post['tyl_unumptyls'  ];
	if($mybb->settings[$prefix.'remowntylfromc'] == 1)
	{
		$owntylusercount = tyl_get_own_tyl_count($post['uid']);
		$post['tyl_unumrcvtyls_self_rem'] -= $owntylusercount;
		$post['tyl_unumtyls_self_rem'   ] -= $owntylusercount;
		if(!$skip_postcounts)
		{
			$post['tyl_unumptyls_self_rem'] -= tyl_get_own_single_tyl_post_count($post['uid']);
		}
	}
}

/**
 * Checks whether tyl functionality is forbidden because the potentially (un)tyled post
 * is not the first post in the thread and in the plugin's ACP settings this has been
 * generally forbidden and not overridden for the post's containing forum.
 * @param integer $fid The ID of the forum containing the thread $thread.
 * @param array $thread The database row of the thread containing the post with ID $pid.
 * @param int $pid The ID of the potentially (un)tyled post.
 * @return boolean True if the tyl functionality is forbidden, false if it is not.
 */
function tyl_is_forbidden_due_to_first_thread_post_restriction($fid, $thread, $pid)
{
	global $mybb;
	$prefix = 'g33k_thankyoulike_';

	$forum_override_for_may_like_all_posts = tyl_in_forums($fid, $mybb->settings[$prefix.'firstalloverride']);
	$may_like_all_posts_in_thread = ($mybb->settings[$prefix.'firstall'] == "all" || $forum_override_for_may_like_all_posts);
	$is_first_post = ($thread['firstpost'] == $pid);

	return (!$is_first_post && !$may_like_all_posts_in_thread);
}

/**
 * Checks whether tyl buttons functionality is forbidden for various reasons, including:
 * 1. The forum is password-protected and the user has not supplied the correct password.
 * 2. The forum has been excluded in the plugin's ACP settings.
 * 3. The thread is closed and the user is not a moderator with edit override permission.
 * 4. The user is trying to (un)tyl his/her own post, but the plugin's ACP settings forbid this.
 * 5. The user is a member of a usergroup whose members have had the tyl functionality
 *    hidden from them in the plugin's ACP settings.
 * 6. The user is trying to (un)tyl a post other than the first post in the thread
 *    but this has been forbidden in the plugin's ACP settings.
 * @param array $thread The database row of the thread of the potentially (un)tyled post.
 * @param int $fid The ID of the forum within which the thread of the potentially (un)tyled post exists.
 * @param int $pid The ID of the potentially (un)tyled post.
 * @param int $post_userid The ID of the author of the potentially (un)tyled post.
 * @param int $tyl_userid The ID of the user who potentially will (un)tyl the post.
 * @param boolean $skip_forum_pw_protect_check Whether, to avoid potential database queries,
 *                and assuming the check is not required, to skip the check for a password-protected forum.
 * @param array &$err_msgs An array of error messages which can be displayed to the user
 *              to explain why the tyl functionality is forbidden to him/her.
 *              Will be filled if and only if the function returns true.
 * @return boolean True if the tyl functionality is forbidden, false if it is not.
 */
function tyl_is_tyling_forbidden($thread, $fid, $pid, $post_userid, $tyl_userid, $skip_forum_pw_protect_check = true, &$err_msgs = array())
{
	global $mybb, $lang;
	$prefix = 'g33k_thankyoulike_';

	$err_msgs = array();
	$pre = ($mybb->settings[$prefix.'thankslike'] == "like" ? $pre = $lang->tyl_like : $lang->tyl_thankyou);

	if (!$skip_forum_pw_protect_check)
	{
		// Check whether this forum is password protected and we have a valid password.
		$forbidden_due_to_forum_pw_protect = check_forum_password($fid, 0, true);
		if ($forbidden_due_to_forum_pw_protect)
		{
			$err_msgs[] = $lang->error_nopermission_user_ajax;
		}
	} else	$forbidden_due_to_forum_pw_protect = false;

	$forbidden_due_to_excluded_forum = tyl_in_forums($fid, $mybb->settings[$prefix.'exclude']);
	if ($forbidden_due_to_excluded_forum) {
		$err_msgs[] = $lang->tyl_error_excluded;
	}

	$forbidden_due_to_thread_closure = ($thread['closed'] == 1 && $mybb->settings[$prefix.'closedthreads'] != "1" && !is_moderator($fid, "caneditposts"));
	if ($forbidden_due_to_thread_closure)
	{
		$err_msgs[] = $lang->sprintf($lang->tyl_error_threadclosed, $pre);
	}

	$may_like_own_posts = ($mybb->settings[$prefix.'tylownposts'] == "1");
	$is_own_post        = ($post_userid == $tyl_userid);
	$forbidden_due_to_unlikeability_of_own_posts = (!$may_like_own_posts && $is_own_post);
	if ($forbidden_due_to_unlikeability_of_own_posts)
	{
		$err_msgs[] = $lang->sprintf($lang->tyl_error_own_post, $pre);
	}

	$forbidden_due_to_group_membership = (is_member($mybb->settings[$prefix.'hideforgroups'], $tyl_userid) || $mybb->settings[$prefix.'hideforgroups'] == "-1");
	if ($forbidden_due_to_group_membership)
	{
		$err_msgs[] = $lang->sprintf($lang->tyl_error_hidden_from_group, $pre);
	}

	$forbidden_due_to_first_thread_post_restriction = tyl_is_forbidden_due_to_first_thread_post_restriction($fid, $thread, $pid);
	if ($forbidden_due_to_first_thread_post_restriction)
	{
		$err_msgs[] = $lang->sprintf($lang->tyl_error_first_post_only, $pre);
	}

	return ($forbidden_due_to_forum_pw_protect           ||
	        $forbidden_due_to_excluded_forum             ||
	        $forbidden_due_to_thread_closure             ||
	        $forbidden_due_to_unlikeability_of_own_posts ||
	        $forbidden_due_to_group_membership           ||
	        $forbidden_due_to_first_thread_post_restriction);
}

/**
 * Determine which, if either, of the add/remove tyl buttons to show for this post.
 * @param array $thread The database row of the thread of the potentially (un)tyled post.
 * @param int $fid The ID of the forum within which the thread of the potentially (un)tyled post exists.
 * @param int $pid The ID of the potentially (un)tyled post.
 * @param int $post_userid The ID of the author of the potentially (un)tyled post.
 * @param int $tyl_userid The ID of the user who potentially will (un)tyl the post.
 * @param boolean $has_tyled_post Whether the user with ID $tyl_userid has already tyled the post.
 * @return string One of 'del', 'add' or ''.
 */
function tyl_get_which_btn($thread, $fid, $pid, $post_userid, $tyl_userid, $has_tyled_post)
{
	global $mybb;
	$prefix = 'g33k_thankyoulike_';

	// Default to not showing a button
	$which_btn = '';

	$liking_is_forbidden = tyl_is_tyling_forbidden($thread, $fid, $pid, $post_userid, $tyl_userid);

	if(!$liking_is_forbidden)
	{
		$may_remove_tyls = ($mybb->settings[$prefix.'removing'] == "1");
		if($has_tyled_post && $may_remove_tyls)
		{
			// Show a remove button.
			$which_btn = 'del';
		}
		else if(!$has_tyled_post)
		{
			// Show an add button.
			$which_btn = 'add';
		}
	}

	return $which_btn;
}

function tyl_get_lang_key_likes_or_thanks($suffix = '')
{
	global $mybb;
	$prefix = 'g33k_thankyoulike_';

	$ret = $mybb->settings[$prefix.'thankslike'];

	if(substr($ret, -1) == 's')
	{
		$ret = substr($ret, 0, -1);
	}

	switch($suffix)
	{
		case 's':
			$ret .= 's';
			break;
		case 'ed';
			if(substr($ret, -1) != 'e')
			{
				$ret .= 'e';
			}
			$ret .= 'd';
			break;
	}

	return $ret; // Either "like", "likes", "liked", "thank", "thanks", or "thanked".
}

function tyl_set_lang_rcvd_given($tok_tyls = '')
{
	global $lang;

	if(!$tok_tyls)
	{
		$tok_tyls = tyl_get_lang_key_likes_or_thanks('s');
	}
	$key_tyls = 'tyl_'.$tok_tyls;
	$lang->tyl_rcvd = $lang->sprintf($lang->tyl_tyls_rcvd, $lang->$key_tyls);
	$lang->tyl_given = $lang->sprintf($lang->tyl_tyls_given, $lang->$key_tyls);
}

/**
 * Insert into the given post's 'user_details' field the user's tyl statistics
 * by replacing the placeholder '%%TYL_NUMTHANKEDLIKED%%'.
 * @param array &$post The database row of the post. Modified by this function.
 */
function tyl_set_up_stats_in_postbit(&$post)
{
	global $mybb, $lang, $templates;
	$prefix = 'g33k_thankyoulike_';

	// If removal of self-likes from counts is enabled, then remove self-likes from counts.
	tyl_check_remove_self_likes_from_post_array($post);

	tyl_set_lang_rcvd_given();

	$post['tyl_unumrcvtyls_fmt'] = $post['tyl_unumrtyls'] = $lang->sprintf($lang->tyl_tyls_rcvd_bit, tyl_fmt_rcvd_tyls_count($post['tyl_unumrcvtyls_self_rem']), my_number_format($post['tyl_unumptyls_self_rem']));
	$post['tyl_unumtyls_fmt'] = my_number_format($post['tyl_unumtyls_self_rem']);
	eval("\$tyl_thankslikes = \"".$templates->get("thankyoulike_postbit_author_user", 1, 0)."\";");

	$post['user_details'] = preg_replace("#".preg_quote('%%TYL_NUMTHANKEDLIKED%%')."#i", $tyl_thankslikes, $post['user_details']);
}

function tyl_fmt_rcvd_tyls_count($count, $link = '')
{
	global $mybb;

	$prefix = 'g33k_thankyoulike_';

	$class = '';
	$intervals = trim($mybb->settings[$prefix.'rcvdlikesclassranges']);
	if ($intervals) {
		$a = explode(',', $intervals);
		for ($i = 0; $i < count($a); $i++) {
			if ($count < $a[$i]) {
				break;
			}
		}
		if ($i > 0) {
			$class = 'tyl_rcvdlikesrange_'.$i;
		}
	}

	return '<span'.($class ? ' class="'.$class.'"' : '').'>'.($link ? "<a href=\"{$link}\"".'>' : '').my_number_format($count).($link ? '</a>' : '').'</span>';
}

/**
 * Remove from the supplied post's 'user_details' field
 * the placeholder for the user's tyl statistics, '%%TYL_NUMTHANKEDLIKED%%'.
 * @param array &$post The database row of the post. Updated by the function.
 */
function tyl_remove_stats_in_postbit(&$post)
{
	$post['user_details'] = preg_replace("#".preg_quote('%%TYL_NUMTHANKEDLIKED%%<br />')."#i", "", $post['user_details']);
}

function tyl_query_post_likes($post, $pids = '')
{
	global $mybb, $db;

	$prefix = 'g33k_thankyoulike_';

	// Use pids if $pids are there, otherwise use $post['pid'] as we're probably in threaded view
	if($pids != '')
	{
		$conds = 'tyl.'.trim($pids);
	}
	else
	{
		$conds = "tyl.pid='".$post['pid']."'";
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

	return $db->query("
		SELECT u.username, u.usergroup, u.displaygroup, u.avatar, tyl.*
		FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
		LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=tyl.uid)
		WHERE ".$conds."
		".$order
	);
}

function tyl_build_likers_list($tyl, &$comma, &$tyls, &$tyled, &$count)
{
	global $mybb, $templates, $lang;

	$prefix = 'g33k_thankyoulike_';

	$profile_link = get_profile_link($tyl['uid']);
	$avatar = format_avatar($tyl['avatar']);
	// Format username... or not
	$tyl_list = $mybb->settings[$prefix.'unameformat'] == "1" ? format_name($tyl['username'], $tyl['usergroup'], $tyl['displaygroup']) : $tyl['username'];
	$datedisplay_plain = my_date($mybb->settings[$prefix.'dtformat'], $tyl['dateline']);
	$datedisplay_next = $mybb->settings[$prefix.'showdt'] == "nexttoname" ? "<span class='smalltext'> (".$datedisplay_plain.")</span>" : "";
	$datedisplay_title = $mybb->settings[$prefix.'showdt'] == "astitle" ? "title='".$datedisplay_plain."'" : "";
	eval("\$thankyoulike_users = \"".$templates->get($mybb->settings[$prefix.'likersdisplay'] == "avatars" ? "thankyoulike_users_avatars" : "thankyoulike_users", 1, 0)."\";");
	$tyls .= trim($thankyoulike_users);
	$comma = $lang->comma;
	// Has this user tyled?
	if($tyl['uid'] == $mybb->user['uid'])
	{
		$tyled = 1;
	}
	$count++;
}

function tyl_build_post_likers_display(&$post, $tyls, $tyled, $count) {
	global $mybb, $templates, $theme, $lang;

	$prefix = 'g33k_thankyoulike_';

	$unapproved_shade = '';

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
		$tyl_like_or_say = $tyl_like;
		$tyl_title_collapsed = $lang->tyl_title_collapsed_l;
		$tyl_title = $lang->tyl_title_l;
	}
	else // $mybb->settings[$prefix.'thankslike'] == "thanks"
	{
		$pre = "ty";
		$lang->add_tyl = $lang->add_ty;
		$lang->del_tyl = $lang->del_ty;
		$lang->add_tyl_button_title = $lang->add_ty_button_title;
		$lang->del_tyl_button_title = $lang->del_ty_button_title;
		$tyl_like_or_say = $tyl_say;
		$tyl_title_collapsed = $lang->tyl_title_collapsed_ty;
		$tyl_title = $lang->tyl_title_ty;
	}
	$tyled_user = get_user($post['uid']);
	if($mybb->settings[$prefix.'unameformat'] == "1")
	{
		$tyled_user['username'] = format_name($tyled_user['username'], $tyled_user['usergroup'], $tyled_user['displaygroup']);
		$tyl_profilelink = build_profile_link($tyled_user['username'], $tyled_user['uid']);
	}
	else
	{
		$tyl_profilelink  = htmlspecialchars_uni($tyled_user['username']);
	}
	$lang->tyl_title = $lang->sprintf($tyl_title, $count, $tyl_user, $tyl_like_or_say, $tyl_profilelink);
	$lang->tyl_title_collapsed = $lang->sprintf($tyl_title_collapsed, $count, $tyl_user, $tyl_like_or_say, $tyl_profilelink);

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

	$thread = get_thread($post['tid']);
	$post['button_tyl'] = '';
	if (($which_btn = tyl_get_which_btn($thread, $post['fid'], $post['pid'], $post['uid'], $mybb->user['uid'], $tyled)))
	{
		eval("\$post['button_tyl'] = \"".$templates->get("thankyoulike_button_$which_btn")."\";");
	}

	$forbidden_due_to_first_thread_post_restriction = tyl_is_forbidden_due_to_first_thread_post_restriction($post['fid'], $thread, $post['pid']);
	$is_member_of_hidden_group = (is_member($mybb->settings[$prefix.'hidelistforgroups']) || $mybb->settings[$prefix.'hidelistforgroups'] == "-1");

	$post['thankyoulike'] = "";
	if($count>0 && !$forbidden_due_to_first_thread_post_restriction && !$is_member_of_hidden_group)
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
		$post['thankyoulike_data'] = $thankyoulike;
		$post['ty_count'] = $count;

		return true;
	}
	else
	{
		return false;
	}
}

function thankyoulike_postbit(&$post)
{
	global $db, $mybb, $templates, $lang, $pids, $g33k_pcache, $theme;
	$prefix = 'g33k_thankyoulike_';

	$lang->load("thankyoulike");

	$unapproved_shade = '';

	if ($mybb->settings[$prefix.'enabled'] == "1")
	{
		// Set up stats in postbit
		tyl_set_up_stats_in_postbit($post);

		// If this post is in an excluded forum, then end right here.
		if (tyl_in_forums($post['fid'], $mybb->settings[$prefix.'exclude']))
		{
			return $post;
		}

		// Get all the thank you/like data for all the posts on this thread
		// Check first is it already fetched/cached?
		if(!is_array($g33k_pcache))
		{
			$g33k_pcache = array();

			$query = tyl_query_post_likes($post, $pids);

			while($t = $db->fetch_array($query))
			{
				$g33k_pcache[$t['pid']][] = $t;
			}
		}

		$tyls = $comma = '';
		$tyled = $count = 0;
		if(isset($g33k_pcache[$post['pid']]))
		{
			foreach($g33k_pcache[$post['pid']] AS $tyl)
			{
				tyl_build_likers_list($tyl, $comma, $tyls, $tyled, $count);
			}
		}

		if (!tyl_build_post_likers_display($post, $tyls, $tyled, $count)) {
			$tyl_title_display = "";
			$tyl_title_display_collapsed = "display: none;";
			$tyl_data_display = "";
			$tyl_expcol = "";
			$lang->tyl_title = '';
			$lang->tyl_title_collapsed = '';
			$post['tyl_display'] = "display: none;";
			if($mybb->settings['postlayout'] == "classic")
			{
				eval("\$thankyoulike = \"".$templates->get($mybb->settings[$prefix.'likersdisplay'] == "avatars" ? "thankyoulike_postbit_classic_avatars" : "thankyoulike_postbit_classic")."\";");
			}
			else
			{
				eval("\$thankyoulike = \"".$templates->get($mybb->settings[$prefix.'likersdisplay'] == "avatars" ? "thankyoulike_postbit_avatars" : "thankyoulike_postbit")."\";");
			}
			$post['thankyoulike_data'] = $thankyoulike;
			$post['ty_count'] = $count;
		}
	}
	else
	{
		// Remove stats in postbit
		tyl_remove_stats_in_postbit($post);
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

/**
 * Add a support for displaying total number of tyl for the first post of the thread in the forumdisplay_thread and/or search_results_threads_thread template.
 */
function thankyoulike_threads_udetails()
{
	global $mybb, $db, $templates, $lang, $thread, $threadcache, $thread_cache, $tyl_forumdisplay_thread_var, $tyl_search_page_var;
	static $tyl_threads_cached = array();
	$prefix = 'g33k_thankyoulike_';
	$lang->load("thankyoulike");

	$display_forum  = ($mybb->settings[$prefix.'display_tyl_counter_forumdisplay'] == "1" && THIS_SCRIPT == "forumdisplay.php");
	$display_search = ($mybb->settings[$prefix.'display_tyl_counter_search_page' ] == "1" && THIS_SCRIPT == "search.php"      );
	if($display_forum || $display_search)
	{
		$page_type = $display_forum ? 'forumdisplay_thread' : 'search_page';
		$tcache    = $display_forum ? $threadcache          : $thread_cache;
		if(!$tyl_threads_cached)
		{
			$pids = array();
			foreach($tcache as $t)
			{
				$pids[] = (int)$t['firstpost'];
			}
			$pids = implode(',', $pids);
			$query = $db->simple_select("posts","tid,tyl_pnumtyls","pid IN ($pids)");
			while($post = $db->fetch_array($query))
			{
				$tyl_threads_cached[$post['tid']] = (int)$post['tyl_pnumtyls'];
			}

			$key_1st_post_tyl_count = 'tyl_firstpost_tyl_count_'.$page_type;
			$key_tyls_sm = 'tyl_'.tyl_get_lang_key_likes_or_thanks('s').'_sm';
			$lang->$key_1st_post_tyl_count = $lang->sprintf($lang->$key_1st_post_tyl_count, $lang->$key_tyls_sm);
		}

		$thread['tyls'] = $tyl_threads_cached[$thread['tid']];
		eval("\$tyl_{$page_type}_var = \"" . $templates->get("thankyoulike_tyl_counter_{$page_type}") . "\";");
	}
}

/**
 * Count the number of self-liked posts for either the given user or for all users.
 * @param mixed The given user's ID as an integer or null to count for all users.
 * @return integer The self-like count.
 */
function tyl_get_own_tyl_count($uid = null) {
	global $db;
	$prefix = 'g33k_thankyoulike_';

	// Cache results as we may need them again
	// e.g. on a thread page where the same user has posted multiple times.
	static $owntylcounts = array();

	if(!isset($owntylcounts[$uid]))
	{
		$where = "uid=puid";
		// $uid of null means to count the self-likes of ALL users
		if(!is_null($uid))
		{
			$where .= " AND uid='$uid'";
		}
		$query = $db->simple_select($prefix."thankyoulike", "COUNT(tlid) AS owntyluser", $where);

		$owntylcounts[$uid] = $db->fetch_field($query, 'owntyluser');
	}

	return $owntylcounts[$uid];
}

function thankyoulike_postbit_udetails(&$post)
{
	global $mybb, $lang;
	$prefix = 'g33k_thankyoulike_';
	$lang->load("thankyoulike");

	if ($mybb->settings[$prefix.'enabled'] == "1")
	{
		// Set up stats in postbit
		tyl_set_up_stats_in_postbit($post);
	}
	else
	{
		// Remove stats in postbit
		tyl_remove_stats_in_postbit($post);
	}
	return $post;
}

/**
 * If this plugin and MyAlerts are both enabled and integrated, then add/update an alert for the tyls of this post.
 */
function tyl_manage_alert_for_added_tyl($post, $from_uid)
{
	global $db, $mybb;
	$prefix = 'g33k_thankyoulike_';

	if($mybb->settings[$prefix.'enabled'] == "1" && tyl_have_myalerts(true, true, true))
	{
		$uid = (int)$post['uid'];
		$tid = (int)$post['tid'];
		$pid = (int)$post['pid'];
		$subject = htmlspecialchars_uni($post['subject']);
		$fid = (int)$post['fid'];

		$alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('tyl');

		$counts = tyl_get_post_tyl_counts($pid, $post['tyl_last_alerted_tyl_id']);

		// Check whether one or more tyl alerts for this post exist,
		// with the latest alert ordered first.
		$query = $db->simple_select(
			'alerts',
			'id,unread,extra_details',
			'object_id = '.$pid.' AND alert_type_id = '.$alertType->getId(),
			array('order_by' => 'id', 'order_dir' => 'DESC')
		);
		$fields = $db->fetch_array($query);
		$db->free_result($query);

		// If there are no existing tyl alerts for this post,
		// or if the latest tyl alert for this post has already been read,
		// then create (another) one.
		if(!$fields || $fields['unread'] < 1)
		{
			$alert = new MybbStuff_MyAlerts_Entity_Alert($uid, $alertType, $pid, $from_uid);
			$alert->setExtraDetails(
				array_merge(
					$counts,
					array(
						'tid'       => $tid,
						'pid'       => $pid,
						't_subject' => $subject,
						'fid'       => $fid
					)
				)
			);
			MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
		}
		else
		// An unread alert for this post already exists - update it.
		{
			$alert_id = $fields['id'];
			unset($fields['id']);
			$extra_details = json_decode($fields['extra_details'], true);
			$extra_details['total_likes_count'] = $counts['total_likes_count'];
			$extra_details['new_likes_count'  ] = $counts['new_likes_count'  ];
			$fields['extra_details'] = $db->escape_string(json_encode($extra_details));
			$fields['from_user_id'] = $from_uid;
			$db->update_query('alerts', $fields, 'id = '.$alert_id);
		}
	}
}

function tyl_manage_alert_for_deleted_tyl($post, $from_uid)
{
	global $mybb, $db;
	$prefix = 'g33k_thankyoulike_';

	if($mybb->settings[$prefix.'enabled'] == "1" && tyl_have_myalerts(true, true, true) && $post && $from_uid)
	{
		$alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('tyl');

		// Check whether an existing unread likes alert for this post exists.
		$query = $db->simple_select(
			'alerts',
			'id,from_user_id,extra_details',
			'object_id = '.$post['pid'].' AND unread = 1 AND alert_type_id = '.$alertType->getId()
		);

		if($db->num_rows($query) > 0)
		{
			// Yes, an existing unread likes alert for this post exists. Manage it given the deleted like.
			$fields = $db->fetch_array($query);
			$db->free_result($query);
			$alert_id = $fields['id'];
			$counts = tyl_get_post_tyl_counts($post['pid'], $post['tyl_last_alerted_tyl_id']);
			$deleted = false;
			if($counts['new_likes_count'] <= 0)
			{
				if($counts['total_likes_count'] <= 0)
				{
					$deleted = true;
					$db->delete_query('alerts', "id={$alert_id}");
				}
				else
				{
					$fields = array('unread' => 0);
				}
			}
			else
			{
				unset($fields['id']);
				$extra_details = json_decode($fields['extra_details'], true);
				$extra_details['total_likes_count'] = $counts['total_likes_count'];
				$extra_details['new_likes_count'  ] = $counts['new_likes_count'  ];
				$fields['extra_details'] = $db->escape_string(json_encode($extra_details));
				if ($from_uid == $fields['from_user_id'])
				{
					$tyl_table = TABLE_PREFIX.$prefix.'thankyoulike';
					$query2 = $db->query("
SELECT uid
FROM {$tyl_table}
WHERE tlid = (SELECT MIN(tlid)
              FROM {$tyl_table}
              WHERE pid = {$post['pid']} AND tlid > {$post['tyl_last_alerted_tyl_id']}
             )");
					$fields['from_user_id'] = $db->fetch_field($query2, 'uid');
				}
			}
			if(!$deleted)
			{
				$db->update_query('alerts', $fields, 'id = '.$alert_id);
			}
		}
	}
}

function tyl_myalerts_alert_manager_mark_read($params)
{
	global $mybb, $db;
	$prefix = 'g33k_thankyoulike_';

	if($mybb->settings[$prefix.'enabled'] == "1" && tyl_have_myalerts(true, true, true))
	{
		$alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('tyl');
		$query = $db->simple_select(
			'alerts',
			'object_id',
			'id in ('.$params['alertIds'].') AND alert_type_id = '.$alertType->getId()
		);
		$pids = array();
		while($pid = $db->fetch_field($query, 'object_id'))
		{
			$pids[] = $pid;
		}
		$db->free_result($query);
		foreach ($pids as $pid)
		{
			$query2 = $db->simple_select($prefix.'thankyoulike', 'MAX(tlid) AS tyl_last_alerted_tyl_id', "pid = {$pid}");
			$tyl_last_alerted_tyl_id = $db->fetch_field($query2, 'tyl_last_alerted_tyl_id');
			if (!$tyl_last_alerted_tyl_id)
			{
				$tyl_last_alerted_tyl_id = 0;
			}
			$db->update_query('posts', array('tyl_last_alerted_tyl_id' => $tyl_last_alerted_tyl_id), "pid = {$pid}");
		}
	}
}

/**
 * Returns an array containing two elements:
 * 1. `total_likes_count`: the total count of likes for the post with pid $pid.
 * 2. `new_likes_count`  : the count of new likes for that post, being those
 *      since the last alert for that post was clicked on by the post's author,
 *      at which point the maximum like ID for that post was $tyl_last_alerted_tyl_id.
 */
function tyl_get_post_tyl_counts($pid, $tyl_last_alerted_tyl_id)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	$tyl_table = TABLE_PREFIX.$prefix.'thankyoulike';

	$query = $db->query("
SELECT COUNT(*) AS total_likes_count, (SELECT COUNT(*)
                                       FROM {$tyl_table}
                                       WHERE pid = {$pid} AND tlid > {$tyl_last_alerted_tyl_id}) AS new_likes_count
FROM   {$tyl_table}
WHERE  pid = {$pid}");

	return $db->fetch_array($query);
}

/**
 * Defines the tyl alert formatter class and registers it with the MyAlerts plugin.
 * Assumes that checks for the presence of and integration with MyAlerts
 * have already been successfully performed.
 */
function tyl_myalerts_formatter_load()
{
	global $mybb, $lang;

	if (class_exists('MybbStuff_MyAlerts_Formatter_AbstractFormatter') &&
	    class_exists('MybbStuff_MyAlerts_AlertFormatterManager'))
	{
		class ThankyouAlertFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
		{
			public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
			{
				global $mybb;
				$prefix = 'g33k_thankyoulike_';

				if($mybb->settings[$prefix.'thankslike'] == "like")
				{
					$key_tyl_you_for_sm  = 'tyl_likeyoufor_sm' ;
					$key_tyls_you_for_sm = 'tyl_likesyoufor_sm';
				}
				else
				{
					$key_tyl_you_for_sm  = 'tyl_thankyoufor_sm' ;
					$key_tyls_you_for_sm = 'tyl_thanksyoufor_sm';
				}

				$alertContent = $alert->getExtraDetails();
				$postLink = $this->buildShowLink($alert);

				switch($alertContent['total_likes_count']) {
				case 1:
					return $this->lang->sprintf(
						$this->lang->tyl_alert_one_tyl,
						$outputAlert['from_user'],
						$this->lang->$key_tyls_you_for_sm,
						$alertContent['t_subject']
					);
				case 2:
					return $this->lang->sprintf(
						$this->lang->tyl_alert_two_tyls,
						$outputAlert['from_user'],
						$alertContent['new_likes_count'],
						$this->lang->$key_tyl_you_for_sm,
						$alertContent['t_subject']
					);
				default: // Three or more tyls
					return $this->lang->sprintf(
						$this->lang->tyl_alert_three_or_more_tyls,
						$outputAlert['from_user'],
						$alertContent['total_likes_count'] - 1,
						$alertContent['new_likes_count'],
						$this->lang->$key_tyl_you_for_sm,
						$alertContent['t_subject']
					);
				}
			}

			public function init()
			{
				if(!$this->lang->thankyoulike) {
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

		$code = 'tyl';
		$formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::getInstance();
		if (!$formatterManager)
		{
		        $formatterManager = MybbStuff_MyAlerts_AlertFormatterManager::createInstance($mybb, $lang);
		}
		if ($formatterManager)
		{
			$formatterManager->registerFormatter(new ThankyouAlertFormatter($mybb, $lang, $code));
		}
	}
}

function thankyoulike_memprofile()
{
	global $db, $mybb, $lang, $memprofile, $templates, $tyl_memprofile, $tyl_profile_stats, $uid, $post;
	$prefix = 'g33k_thankyoulike_';

	$lang->load("thankyoulike");

	if ($mybb->settings[$prefix.'enabled'] == "1")
	{
		$key_tyls  = 'tyl_'.tyl_get_lang_key_likes_or_thanks('s');
		$key_tyled = 'tyl_'.tyl_get_lang_key_likes_or_thanks('ed');
		$lang->tyl_total_tyls_rcvd  = $lang->sprintf($lang->tyl_total_tyls_rcvd       , $lang->$key_tyls);
		$lang->tyl_total_tyls_given = $lang->sprintf($lang->tyl_total_tyls_given      , $lang->$key_tyls);
		$lang->tyl_find_threads     = $lang->sprintf($lang->tyl_find_tyled_threads    , $lang->$key_tyled);
		$lang->tyl_find_posts       = $lang->sprintf($lang->tyl_find_tyled_posts      , $lang->$key_tyled);
		$lang->tyl_find_threads_for = $lang->sprintf($lang->tyl_find_tyled_threads_for, $lang->$key_tyled);
		$lang->tyl_find_posts_for   = $lang->sprintf($lang->tyl_find_tyled_posts_for  , $lang->$key_tyled);

		tyl_check_remove_self_likes_from_post_array($memprofile, true);

		$daysreg = (TIME_NOW - $memprofile['regdate']) / (24*3600);
		$tylpd = $memprofile['tyl_unumtyls_self_rem'] / $daysreg;
		$tylpd = round($tylpd, 2);
		if($tylpd > $memprofile['tyl_unumtyls_self_rem'])
		{
			$tylpd = $memprofile['tyl_unumtyls_self_rem'];
		}
		$tylrcvpd = $memprofile['tyl_unumrcvtyls_self_rem'] / $daysreg;
		$tylrcvpd = round($tylrcvpd, 2);
		if($tylrcvpd > $memprofile['tyl_unumrcvtyls_self_rem'])
		{
			$tylrcvpd = $memprofile['tyl_unumrcvtyls_self_rem'];
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
			$owntyltotalcount = tyl_get_own_tyl_count();
			$totalgiv -= $owntyltotalcount;
			$totalrcv -= $owntyltotalcount;
		}

		if($totalgiv > 0)
		{
			$percent = $memprofile['tyl_unumtyls_self_rem']*100/$totalgiv;
			$percent = round($percent, 2);
		}
		else
		{
			$percent = "0";
		}

		if($totalrcv > 0)
		{
			$percent_rcv = $memprofile['tyl_unumrcvtyls_self_rem']*100/$totalrcv;
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
		if($memprofile['tyl_unumrcvtyls_self_rem'] > 0)
		{
			eval("\$tylrcvdlikessearch = \"".$templates->get("thankyoulike_member_profile_rcvd_search")."\";");
		}
		else
		{
			$tylrcvdlikessearch = '';
		}
		if($memprofile['tyl_unumtyls_self_rem'] > 0)
		{
			eval("\$tylgivenlikessearch = \"".$templates->get("thankyoulike_member_profile_given_search")."\";");
		}
		else
		{
			$tylgivenlikessearch = '';
		}
		$memprofile['tyl_unumtyls_fmt'   ] = my_number_format        ($memprofile['tyl_unumtyls_self_rem'   ]);
		$memprofile['tyl_unumrcvtyls_fmt'] = tyl_fmt_rcvd_tyls_count ($memprofile['tyl_unumrcvtyls_self_rem']);
		$tylpd_percent_total    = $lang->sprintf($lang->tyl_tylpd_percent_total, my_number_format($tylpd   ), $percent    , $totalgiv);
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
					SELECT l.pid, count( * ) AS tylcount, p.subject, p.username, p.message, p.dateline, p.tid, p.fid, f.parentlist, t.poll as threadpoll, t.prefix as threadprefix
					FROM ".TABLE_PREFIX."g33k_thankyoulike_thankyoulike l
					LEFT JOIN ".TABLE_PREFIX."posts p ON (l.pid=p.pid)
					LEFT JOIN ".TABLE_PREFIX."threads t ON (t.tid=p.tid)
					LEFT JOIN ".TABLE_PREFIX."forums f ON (f.fid = t.fid)
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

				$postlink = get_post_link($post['pid'], $post['tid'])."#pid".$post['pid'];

				$thread = get_thread($post['tid']);
				$threadlink = get_thread_link($post['tid']);

				$forum_links = '';
				if ($mybb->settings[$prefix.'profile_box_show_parent_forums'] == 1) {
					$forum_names = array();
					$res = $db->simple_select('forums', 'fid,name', 'fid IN ('.$post['parentlist'].')');
					while (($forum = $db->fetch_array($res))) {
						$forum_names[$forum['fid']] = $forum['name'];
					}

					$fids = explode(',', $post['parentlist']);
					$fid_last = array_pop($fids);
					foreach ($fids as $fid2) {
						if ($forum_links) eval('$forum_links .= "'.$templates->get('thankyoulike_forum_separator').'";');
						$forum_url = get_forum_link($fid2);
						$forum_name = htmlspecialchars_uni($forum_names[$fid2]);
						eval('$forum_links .= "'.$templates->get('thankyoulike_forum_link').'";');
					}
					eval('$forum_links .= "'.$templates->get('thankyoulike_forum_separator_last').'";');
					$forum_url = get_forum_link($fid_last);
					$forum_name = htmlspecialchars_uni($forum_names[$fid_last]);
				}
				else
				{
					$forum_url = get_forum_link($post['fid']);
					$forum = get_forum($post['fid']);
					$forum_name = htmlspecialchars_uni($forum['name']);
				}
				eval('$forum_links .= "'.$templates->get('thankyoulike_forum_link').'";');

				$post['subject'] = htmlspecialchars_uni($post['subject']);
				$memprofile['tylsubject'] = "<a href=\"{$postlink}\"><span>{$parser->parse_badwords($post['subject'])}</span></a>";

				$memprofile['tylcount'] = tyl_fmt_rcvd_tyls_count((int)$post['tylcount'], $postlink);

				$memprofile['tyldatetime'] = my_date('relative', $post['dateline']);

				$parser_options = array(
					"allow_html" => (int)$mybb->settings[$prefix.'profile_box_post_allowhtml'],
					"allow_mycode" => (int)$mybb->settings[$prefix.'profile_box_post_allowmycode'],
					"allow_smilies" => (int)$mybb->settings[$prefix.'profile_box_post_allowsmilies'],
					"allow_imgcode" => (int)$mybb->settings[$prefix.'profile_box_post_allowimgcode'],
					"allow_videocode" => (int)$mybb->settings[$prefix.'profile_box_post_allowvideocode'],
					"nofollow_on" => 1,
					"filter_badwords" => 1
				);
				$post['message'] = strip_tags($parser->text_parse_message($post['message'], $parser_options));

				$cut_post_off = NULL;

				if($mybb->settings[$prefix.'profile_box_post_cutoff'] > 0)
				{
					$cut_post_off = (int)$mybb->settings[$prefix.'profile_box_post_cutoff'];
				}

				if (isset($cut_post_off) && $cut_post_off < 4)
				{
					$cut_post_off = 4;
				}

				if(isset($cut_post_off) && my_strlen($post['message']) > $cut_post_off)
				{
					$cut_post_off = $cut_post_off - 3;
					$memprofile['tylmessage'] = my_substr($post['message'], 0, $cut_post_off)."...";
					eval("\$memprofile['tylmessage'] .= \"".$templates->get("thankyoulike_member_profile_box_continue_reading")."\";");
				}
				else
				{
					$memprofile['tylmessage'] = $post['message'];
				}

				$thread['subject'] = htmlspecialchars_uni($thread['subject']);
				$memprofile['tylthreadname'] = "<a href=\"{$threadlink}\"><span>{$parser->parse_badwords($thread['subject'])}</span></a>";
				$memprofile['tylforums'] = $forum_links;

				$memprofile['threadprefix'] = '';
				if ($post['threadpoll']) {
					$memprofile['threadprefix'] = $lang->poll_prefix.'&nbsp;';
				}
				if ($post['threadprefix'] != 0) {
					$threadprefix_a = build_prefixes($post['threadprefix']);
					if (!empty($threadprefix_a)) {
						$memprofile['threadprefix'] .= $threadprefix_a['displaystyle'].'&nbsp;';
					}
				}

				eval("\$tyl_profile_box_content = \"".$templates->get("thankyoulike_member_profile_box_content")."\";");
			}
			else
			{
				eval("\$tyl_profile_box_content = \"".$templates->get("thankyoulike_member_profile_box_content_none")."\";");
			}

			eval("\$tyl_profile_box = \"".$templates->get("thankyoulike_member_profile_box")."\";");
		}
		// Member Profile Box End

		if($mybb->settings[$prefix.'show_memberprofile_stats'] != 0)
		{
			if($mybb->settings[$prefix.'thankslike'] == 'thanks')
			{
				$key_tyls     = 'tyl_thanks'    ;
				$key_tyled_sm = 'tyl_thanked_sm';
			}
			else
			{
				$key_tyls     = 'tyl_likes'   ;
				$key_tyled_sm = 'tyl_liked_sm';
			}
			tyl_set_lang_rcvd_given();
			$lang->tyl_most_tyled_by   = $lang->sprintf($lang->tyl_most_tyled_by  , $lang->$key_tyled_sm);
			$lang->tyl_most_tyled      = $lang->sprintf($lang->tyl_most_tyled     , $lang->$key_tyled_sm);;
			$lang->tyl_no_most_tyledby = $lang->sprintf($lang->tyl_no_most_tyledby, $memprofile['username'], $lang->$key_tyled_sm);
			$lang->tyl_no_most_tyled   = $lang->sprintf($lang->tyl_no_most_tyled  , $memprofile['username'], $lang->$key_tyled_sm);

			$lang->load('reputation');
			$week = 60*60*24*7;
			$month = round(60*60*24*365/12);
			$threemonths = $month*3;
			$sixmonths = $threemonths*2;
			$twelvemonths = 60*60*24*365;
			$now = TIME_NOW;
			$tyl_table = TABLE_PREFIX.$prefix.'thankyoulike';

			$exclconds = tyl_get_exclude_conds();
			if($exclconds)
			{
				$exclconds = ' AND '.$exclconds;
			}

			$sql_rcv = "
SELECT t.period, COUNT(*) AS cnt
FROM (SELECT
      CASE
        WHEN tyl.dateline >= {$now} - {$week}         THEN 'week'
        WHEN tyl.dateline >= {$now} - {$month}        THEN 'month'
        WHEN tyl.dateline >= {$now} - {$threemonths}  THEN '3months'
        WHEN tyl.dateline >= {$now} - {$sixmonths}    THEN '6months'
        WHEN tyl.dateline >= {$now} - {$twelvemonths} THEN '12months'
        ELSE 'all'
      END period
      FROM            {$tyl_table} tyl
      LEFT OUTER JOIN ".TABLE_PREFIX."posts p
      ON              tyl.pid = p.pid
      WHERE           tyl.puid = {$memprofile['uid']}{$exclconds}
      ) t
GROUP BY t.period";

			$query_rcv = $db->query($sql_rcv);
			$counts_rcv = array();
			while ($row = $db->fetch_array($query_rcv))
			{
				$counts_rcv[$row['period']] = $row['cnt'];
			}
			$db->free_result($query_rcv);
			$tyl_received_week     =                          (!empty($counts_rcv['week'    ]) ? $counts_rcv['week'    ] : 0);
			$tyl_received_month    = $tyl_received_week     + (!empty($counts_rcv['month'   ]) ? $counts_rcv['month'   ] : 0);
			$tyl_received_3months  = $tyl_received_month    + (!empty($counts_rcv['3months' ]) ? $counts_rcv['3months' ] : 0);
			$tyl_received_6months  = $tyl_received_3months  + (!empty($counts_rcv['6months' ]) ? $counts_rcv['6months' ] : 0);
			$tyl_received_12months = $tyl_received_6months  + (!empty($counts_rcv['12months']) ? $counts_rcv['12months'] : 0);
			$tyl_received_all      = $tyl_received_12months + (!empty($counts_rcv['all'     ]) ? $counts_rcv['all'     ] : 0);

			$sql_gv = str_replace('tyl.puid = ', 'tyl.uid = ', $sql_rcv);
			$query_gv = $db->query($sql_gv);
			$counts_gv = array();
			while ($row = $db->fetch_array($query_gv))
			{
				$counts_gv[$row['period']] = $row['cnt'];
			}
			$db->free_result($query_gv);
			$tyl_given_week     =                       (!empty($counts_gv['week'    ]) ? $counts_gv['week'    ] : 0);
			$tyl_given_month    = $tyl_given_week     + (!empty($counts_gv['month'   ]) ? $counts_gv['month'   ] : 0);
			$tyl_given_3months  = $tyl_given_month    + (!empty($counts_gv['3months' ]) ? $counts_gv['3months' ] : 0);
			$tyl_given_6months  = $tyl_given_3months  + (!empty($counts_gv['6months' ]) ? $counts_gv['6months' ] : 0);
			$tyl_given_12months = $tyl_given_6months  + (!empty($counts_gv['12months']) ? $counts_gv['12months'] : 0);
			$tyl_given_all      = $tyl_given_12months + (!empty($counts_gv['all'     ]) ? $counts_gv['all'     ] : 0);

			$max_likers = 5;

			$tyl_most_likedby_users = tyl_get_memprofile_stats_most_liked_tr($max_likers, $memprofile['uid'], $tyl_received_all, 'likedby');
			$tyl_most_liked_users   = tyl_get_memprofile_stats_most_liked_tr($max_likers, $memprofile['uid'], $tyl_given_all   , 'liked'  );

			$tyl_profile_stats_head = $lang->sprintf($lang->tyl_profile_stats_head, $memprofile['username'], $lang->$key_tyls);
			eval('$tyl_profile_stats = "'.$templates->get('thankyoulike_member_profile_like_stats').'";');
		}
	}
}

function tyl_get_memprofile_stats_most_liked_tr($max_likers, $prof_uid, $tot_likes, $which = 'likedby')
{
	global $db, $templates, $lang, $mybb;
	$prefix = 'g33k_thankyoulike_';

	$ret = '';
	$sql = tyl_get_most_tylers_sql($max_likers, $prof_uid, $which);
	$query = $db->query($sql);
	while($row = $db->fetch_array($query))
	{
		$tyl_cnt = $row['cnt'];
		$tyl_cnt_pc = tyl_format_percentage($tot_likes > 0 ? round($row['cnt']*100/$tot_likes) : 0);
		if($row['username'])
		{
			$tyl_username = $mybb->settings[$prefix.'unameformat'] == "1" ? format_name($row['username'], $row['usergroup'], $row['displaygroup']) : $row['username'];
			$tyl_profilelink = get_profile_link($row['uid']);
			eval('$tyl_member_link = "'.$templates->get('thankyoulike_member_link').'";');
		} else {
			$tyl_member_link = $lang->tyl_deleted_member_name;
		}
		eval('$ret .= "'.$templates->get("thankyoulike_member_profile_like_stats_most_{$which}_row").'";');
	}
	if(!$ret)
	{
		eval('$ret .= "'.$templates->get("thankyoulike_member_profile_like_stats_no_most_{$which}").'";');
	}

	return $ret;
}

function tyl_format_percentage($pc)
{
	global $lang;

	return $lang->sprintf($lang->tyl_pc, $pc);
}

function tyl_get_exclude_conds()
{
	global $mybb;
	$prefix = 'g33k_thankyoulike_';

	$conds = '';
	if($mybb->settings[$prefix.'remowntylfromc'] == 1)
	{
		$conds = 'tyl.uid<>tyl.puid';
	}

	$excl_count_forums = trim($mybb->settings[$prefix.'exclude_count']);
	$exclcountcond = '';
	if($excl_count_forums == -1)
	{
		$exclcountcond = '0=1';
	}
	else if($excl_count_forums != '')
	{
		$exclcountcond = "p.fid NOT IN ({$excl_count_forums})";
	}
	if($exclcountcond)
	{
		if($conds)
		{
			$conds .= ' AND ';
		}
		$conds .= $exclcountcond;
	}

	return $conds;
}

function tyl_get_most_tylers_sql($max_likers, $uid, $which = 'likedby')
{
	global $mybb;

	$prefix = 'g33k_thankyoulike_';
	$tyl_table = TABLE_PREFIX.$prefix.'thankyoulike';

	if($which == 'liked')
	{
		$uidkey1 = 'uid';
		$uidkey2 = 'puid';
	}
	else // assume $which == 'likedby'
	{
		$uidkey1 = 'puid';
		$uidkey2 = 'uid';
	}

	$exclconds = tyl_get_exclude_conds();
	if($exclconds)
	{
		$exclconds = ' AND '.$exclconds;
	}

	return "
SELECT          u.uid, u.username, u.usergroup, u.displaygroup, COUNT(*) AS cnt
FROM            {$tyl_table} tyl
LEFT OUTER JOIN ".TABLE_PREFIX."users u
ON              tyl.{$uidkey2} = u.uid
LEFT OUTER JOIN ".TABLE_PREFIX."posts p
ON              tyl.pid = p.pid
WHERE           tyl.{$uidkey1} = {$uid}{$exclconds}
GROUP BY        tyl.{$uidkey2}
ORDER BY        cnt DESC
LIMIT           {$max_likers}
";
}

function thankyoulike_delete_thread($tid)
{
	global $db;
	$prefix = 'g33k_thankyoulike_';

	$thread = get_thread($tid);

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
	foreach($user_tyls as $uid => $subtract)
	{
		$db->write_query("UPDATE ".TABLE_PREFIX."users SET tyl_unumtyls=tyl_unumtyls$subtract WHERE uid='$uid'");
	}
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

	// Delete the tyls
	if($tlids)
	{
		$tlids_count = count($tlids);
		$tlids = implode(',', $tlids);
		$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value-$tlids_count WHERE title='total'");
		$db->delete_query($prefix."thankyoulike", "tlid IN ($tlids)");
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
	// Update overall tyl count and remove old tyls
	if($tlids_remove)
	{
		$tlids_count = count($tlids_remove);
		$tlids_remove = implode(',', $tlids_remove);
		$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=value-$tlids_count WHERE title='total'");
		// Delete the tyls
		$db->delete_query($prefix."thankyoulike", "tlid IN ($tlids_remove)");
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
	global $user, $location;

	$split_loc = explode(".php", $user_activity['location']);
	if(isset($user['location']) && $split_loc[0] == $user['location'])
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
	$prefix = 'g33k_thankyoulike_';
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
			new Peeker($(".setting_'.$prefix.'enabled"), $("#row_setting_'.$prefix.'thankslike, #row_setting_'.$prefix.'firstall, #row_setting_'.$prefix.'firstalloverride, #row_setting_'.$prefix.'removing, #row_setting_'.$prefix.'tylownposts, #row_setting_'.$prefix.'reputation_add, #row_setting_'.$prefix.'remowntylfroms, #row_setting_'.$prefix.'remowntylfromc, #row_setting_'.$prefix.'closedthreads, #row_setting_'.$prefix.'exclude, #row_setting_'.$prefix.'exclude_count, #row_setting_'.$prefix.'unameformat, #row_setting_'.$prefix.'hideforgroups, #row_setting_'.$prefix.'showdt, #row_setting_'.$prefix.'dtformat, #row_setting_'.$prefix.'sortorder, #row_setting_'.$prefix.'collapsible, #row_setting_'.$prefix.'colldefault, #row_setting_'.$prefix.'hidelistforgroups, #row_setting_'.$prefix.'displaygrowl, #row_setting_'.$prefix.'limits, #row_setting_'.$prefix.'highlight_popular_posts, #row_setting_'.$prefix.'show_memberprofile_box, #row_setting_'.$prefix.'likersdisplay, #row_setting_'.$prefix.'rcvdlikesclassranges, #row_setting_'.$prefix.'display_tyl_counter_forumdisplay, #row_setting_'.$prefix.'display_tyl_counter_search_page, #row_setting_'.$prefix.'show_memberprofile_stats"), 1, true),
			new Peeker($(".setting_'.$prefix.'firstall"), $("#row_setting_'.$prefix.'firstalloverride"), "first", true),
			new Peeker($(".setting_'.$prefix.'tylownposts"), $("#row_setting_'.$prefix.'remowntylfroms, #row_setting_'.$prefix.'remowntylfromc"), 1, true),
			new Peeker($(".setting_'.$prefix.'reputation_add"), $("#row_setting_'.$prefix.'reputation_add_reppoints, #row_setting_'.$prefix.'reputation_add_repcomment"), 1, true),
			new Peeker($(".setting_'.$prefix.'showdt"), $("#row_setting_'.$prefix.'dtformat"),/^(?!none)/, true),
			new Peeker($(".setting_'.$prefix.'collapsible"), $("#row_setting_'.$prefix.'colldefault"), 1, true),
			new Peeker($(".setting_'.$prefix.'highlight_popular_posts"), $("#row_setting_'.$prefix.'highlight_popular_posts_count"), 1, true),
			new Peeker($(".setting_'.$prefix.'show_memberprofile_box"), $("#row_setting_'.$prefix.'profile_box_post_cutoff, #row_setting_'.$prefix.'profile_box_post_allowhtml, #row_setting_'.$prefix.'profile_box_post_allowmycode, #row_setting_'.$prefix.'profile_box_post_allowsmilies, #row_setting_'.$prefix.'profile_box_post_allowimgcode, #row_setting_'.$prefix.'profile_box_post_allowvideocode, #row_setting_'.$prefix.'profile_box_show_parent_forums"), 1, true)
		});
		</script>';
	}
}

// Start Thank You/Like Promotions Functions
function thankyoulike_promotion_formcontainer_output_row(&$args)
{
	global $run_module, $form_container, $mybb, $lang, $form, $options, $options_type, $promotion;

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

			$excl_forums = trim($mybb->settings[$prefix.'exclude']);
			if($excl_forums == -1)
			{
				$where = "WHERE 0=1";
			}
			else if($excl_forums == '')
			{
				$where = '';
			}
			else
			{
				$where = "WHERE p.fid NOT IN (".$excl_forums.")";
			}

			$excl_count_forums = trim($mybb->settings[$prefix.'exclude_count']);
			if($excl_count_forums == -1)
			{
				$where_post_owner = ($where ? $where." AND" : "WHERE")." 0=1";
			}
			else if($excl_count_forums != '')
			{
				$where_post_owner = ($where ? $where." AND" : "WHERE")." p.fid NOT IN (".$excl_count_forums.")";
			}
			else
			{
				$where_post_owner = $where;
			}

			if ($page == 1)
			{
				$db->write_query("UPDATE ".TABLE_PREFIX.$prefix."stats SET value=0 WHERE title='total'");
				$db->write_query("UPDATE ".TABLE_PREFIX."posts SET tyl_pnumtyls=0");
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
							JOIN (SELECT puid, COUNT(DISTINCT(t.pid)) AS pidcount
							FROM ".TABLE_PREFIX.$prefix."thankyoulike t
							LEFT JOIN ".TABLE_PREFIX."posts p
							ON p.pid=t.pid
							$where_post_owner
							GROUP BY puid) tyl
							ON ( u.uid=tyl.puid )
							SET u.tyl_unumptyls=tyl.pidcount");
			}

			$query1 = $db->simple_select($prefix."thankyoulike", "COUNT(tlid) AS num_tyls");
			$num_tyls = $db->fetch_field($query1, 'num_tyls');

			$query2 = $db->query("
					SELECT tyl.*, p.tid, p.fid
					FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
					LEFT JOIN ".TABLE_PREFIX."posts p ON (p.pid=tyl.pid)
					$where
					ORDER BY tyl.dateline ASC
					LIMIT $start, $per_page
				");
			$tlids = array();
			$post_tyls = array();
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
				if(!tyl_in_forums($tyl['fid'], $mybb->settings[$prefix.'exclude_count']))
				{
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
			}
			// Update the counts
			if(is_array($post_tyls))
			{
				foreach($post_tyls as $pid => $add)
				{
					$db->write_query("UPDATE ".TABLE_PREFIX."posts SET tyl_pnumtyls=tyl_pnumtyls+$add WHERE pid='$pid'");
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
		else if(isset($mybb->input['do_reinitlasttylid']))
		{
			if($mybb->input['page'] == 1)
			{
				// Log admin action
				log_admin_action($lang->tyl_admin_log_action2);
			}

			if(!$mybb->get_input('lasttylids', MyBB::INPUT_INT))
			{
				$mybb->input['lasttylids'] = 500;
			}

			$next_pid = $mybb->get_input('page', MyBB::INPUT_INT);
			$per_page = $mybb->get_input('lasttylids', MyBB::INPUT_INT);
			if($per_page <= 0)
			{
				$per_page = 500;
			}

			$res = $db->simple_select('posts', 'MAX(pid) AS max_pid', 'tyl_last_alerted_tyl_id = 0');
			$max_pid = $db->fetch_field($res, 'max_pid');
			$db->free_result($res);

			$pids = '';
			$res = $db->simple_select('posts', 'pid', "tyl_last_alerted_tyl_id = 0 AND pid >= {$next_pid}", array(
				'order_by' => 'pid',
				'order_dir' => 'ASC',
				'limit' => $per_page
			));
			while($pid = $db->fetch_field($res, 'pid'))
			{
				if($pids)
				{
					$pids .= ',';
				}
				$pids .= $pid;
				$next_pid = $pid;
			}
			$next_pid++;

			if($pids)
			{
				$alerts_sql = ($db->table_exists('alerts') && $db->table_exists('alert_types')) ? "AND
               NOT EXISTS (
                           SELECT *
                           FROM   ".TABLE_PREFIX."alerts a
                           WHERE  p.pid = a.object_id
                                  AND
                                  alert_type_id = (
                                                   SELECT id
                                                   FROM   ".TABLE_PREFIX."alert_types
                                                   WHERE  code='tyl'
                                                  )
                                  AND
                                  unread=1
                          )" : '';

				$db->query("
UPDATE ".TABLE_PREFIX."posts AS dest,
       (
        SELECT p.pid,
               (SELECT MAX(tyl.tlid) FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl WHERE p.pid = tyl.pid) AS max_tlid
        FROM   ".TABLE_PREFIX."posts p
        WHERE  tyl_last_alerted_tyl_id = 0
               {$alerts_sql}
               AND
               EXISTS (
                       SELECT *
                       FROM   ".TABLE_PREFIX.$prefix."thankyoulike tyl2
                       WHERE  p.pid = tyl2.pid
                      )
               AND p.pid IN ({$pids})
       ) AS src
SET   dest.tyl_last_alerted_tyl_id = src.max_tlid
WHERE dest.pid = src.pid");
			}

			check_proceed($max_pid, $next_pid, $next_pid, $per_page, 'per_page', 'do_reinitlasttylid', $lang->tyl_success_thankyoulike_rebuilt2);
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

	$form_container->output_cell("<label>{$lang->tyl_recount2}</label><div class=\"description\">{$lang->tyl_recount_do_desc2}</div>");
	$form_container->output_cell($form->generate_numeric_field("lasttylids", 500, array('style' => 'width: 150px;', 'min' => 0)));
	$form_container->output_cell($form->generate_submit_button($lang->go, array("name" => "do_reinitlasttylid")));
	$form_container->construct_row();
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
			"{$lang->tyl_limits_permissions_title}<br /><small class=\"input\">{$lang->tyl_limits_permissions_desc}</small><br />".$form->generate_numeric_field('tyl_limits_max', $mybb->input['tyl_limits_max'], array('id' => 'max_tyl_limits', 'class' => 'field50', 'min' => 0)),
			"{$lang->tyl_flood_interval_title}<br /><small class=\"input\">{$lang->tyl_flood_interval_desc}</small><br />".$form->generate_numeric_field('tyl_flood_interval', $mybb->input['tyl_flood_interval'], array('id' => 'max_flood_interval', 'class' => 'field50', 'min' => 0))
			);

			$form_container->output_row($lang->tyl_limits_permissions_system, "", "<div class=\"group_settings_bit\">".implode("</div><div class=\"group_settings_bit\">", $tyl_limits_options)."</div>");
		}
	}
}

function tyl_limits_usergroup_permission_commit()
{
	global $db, $mybb, $updated_group;
	$updated_group['tyl_limits_max'] = $db->escape_string((int)$mybb->input['tyl_limits_max']);
	$updated_group['tyl_flood_interval'] = $db->escape_string((int)$mybb->input['tyl_flood_interval']);
}

function tyl_preinstall_cleanup($for_upgrade = false)
{
	global $db, $cache;

	$prefix = 'g33k_thankyoulike_';

	//delete old unnecessary files
	if(file_exists(MYBB_ROOT."/admin/modules/tools/thankyoulike_recount.php"))
	{
		@unlink(MYBB_ROOT."/admin/modules/tools/thankyoulike_recount.php");
	}
	if(file_exists(MYBB_ROOT."/inc/languages/english/admin/tools_thankyoulike_recount.lang.php"))
	{
		@unlink(MYBB_ROOT."/inc/languages/english/admin/tools_thankyoulike_recount.lang.php");
	}

	// This column was dropped in 3.1.0
	if($db->field_exists('tyl_tnumtyls', 'threads'))
	{
		$db->query("ALTER TABLE ".TABLE_PREFIX."threads DROP column `tyl_tnumtyls`");
	}


	// Remove old templates, except, when we are upgrading, for user-modified templates.
	$and_where = ($for_upgrade ? ' AND sid=-2' : '');
	$db->delete_query("templates", "title LIKE 'thankyoulike%'".$and_where);
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
		if(!$for_upgrade)
		{
			rebuild_settings();
		}
	}

	$cache->update_usergroups();
	$cache->update_forums();
	$cache->update_tasks();
}

/**
 * Checks whether the value of a forumselect setting is inclusive of
 * the forum with ID $fid.
 *
 * Note: Always returns true when the forum setting is "All" (-1),
 * regardless of whether or not the forum with the supplied ID exists.
 *
 * @param int The ID of the forum for which to check for inclusion.
 * @param mixed The value of the forumselect setting to check within.
 * @return boolean True if inclusive; false if not.
 */
function tyl_in_forums($fid, $forums)
{
	if($forums == -1)
	{
		return true;
	}
	else
	{
		$forums = explode(',', $forums);
		foreach($forums as $forum_id)
		{
			if(trim($forum_id) == $fid)
			{
				return true;
			}
		}
	}

	return false;
}

function tyl_user_users_merge_commit()
{
	global $db, $source_user, $destination_user;
	$prefix = 'g33k_thankyoulike_';

	$source_uid = $source_user['uid'];
	$dest_uid = $destination_user['uid'];

	$pids_liked_by_both = array();
	$pids_liked_by_both_sql = 'SELECT pid FROM '.TABLE_PREFIX.$prefix."thankyoulike WHERE uid IN ($source_uid, $dest_uid) GROUP BY pid HAVING count(pid) > 1";
	$result = $db->query($pids_liked_by_both_sql);
	while(($row = $db->fetch_array($result)))
	{
		$pids_liked_by_both[] = $row['pid'];
	}

	// Subtract from the count of tyls given by the source user the number of posts liked by both users
	// and add the result to the count of tyls given by the destination user.
	$dest_tyl_unumtyls = $destination_user['tyl_unumtyls'] + $source_user['tyl_unumtyls'] - count($pids_liked_by_both);

	$result = $db->query('SELECT pid FROM '.TABLE_PREFIX.$prefix."thankyoulike WHERE puid IN ($source_uid, $dest_uid) AND uid IN ($source_uid, $dest_uid) GROUP BY pid HAVING count(pid) > 1");
	$count_of_own_posts_liked_by_both = $db->num_rows($result);

	// Subtract from the count of tyls received by the source user the number of own posts (of either user) liked by both users
	// and add the result to the count of tyls received by the dest user.
	$dest_tyl_unumrcvtyls = $destination_user['tyl_unumrcvtyls'] + $source_user['tyl_unumrcvtyls'] - $count_of_own_posts_liked_by_both;

	// Add to the count of liked posts of the destination user the count of liked posts of the source user.
	$dest_tyl_unumptyls = $destination_user['tyl_unumptyls'] + $source_user['tyl_unumptyls'];

	// Now update those tyl counts for the destination user.
	$db->update_query('users', array(
		'tyl_unumtyls'    => $dest_tyl_unumtyls,
		'tyl_unumrcvtyls' => $dest_tyl_unumrcvtyls,
		'tyl_unumptyls'   => $dest_tyl_unumptyls
	), "uid='$dest_uid'");

	// Delete duplicated tyls from the (prefixed) thankyoulike table if there are any.
	if($pids_liked_by_both)
	{
		$dup_likes_of_source_user_sql = 'DELETE FROM '.TABLE_PREFIX.$prefix."thankyoulike WHERE uid = $source_uid AND puid = $dest_uid AND pid IN (".implode(',', $pids_liked_by_both).")";
		$db->write_query($dup_likes_of_source_user_sql);
	}

	// Finally, update in the thankyoulike table the uids and puids matching the source user to the user ID of the dest user.
	$db->update_query($prefix.'thankyoulike', array('uid' => $dest_uid ), "uid='$source_uid'");
	$db->update_query($prefix.'thankyoulike', array('puid' => $dest_uid), "puid='$source_uid'");
}
