<?php
/**
 * Thank You / Like System Config Language Pack - English 
 * 
 */

$l['tyl_info_title'] = "Thank You/Like System";
$l['tyl_info_desc'] = "Adds an option for users to thank users for posts or to like posts.";
$l['tyl_info_desc_url'] ="<br />*Edited and maintained for MyBB 1.8.x by: {1}, {2}, {3}, {4}, {5} and {6}<br />*Sources: {7}<br />*{8}";
$l['tyl_info_desc_recount'] = "Recount thanks/likes";
$l['tyl_info_desc_configsettings'] = "Configure settings";
$l['tyl_view_master_thankyoulike_css'] = "View the master theme's thankyoulike.css";
$l['tyl_use_this_css_for'] = "(use this after plugin upgrade to update this stylesheet in <a href=\"index.php?module=style-themes\">any themes</a> for which you have modified it).";
$l['tyl_successful_upgrade_msg'] = "The {1} has been activated successfully and upgraded to version {2}.";
$l['tyl_successful_upgrade_msg_for_info'] = "Successfully upgraded to version {1}.";
$l['tyl_view_changelog'] = "View changelog";
$l['tyl_info_desc_alerts_integrate'] = "<b>Click <u>HERE</u> to integrate the Thank You/Like System with MyAlerts</b>";
$l['tyl_info_desc_alerts_integrated'] = "<b>The Thank You/Like System is currently integrated with MyAlerts.</b>";
$l['tyl_alerts_integration_success_msg'] = 'The Thank You/Like System was successfully integrated with MyAlerts!';
$l['tyl_alerts_integration_failure_msg'] = 'Failed to integrate the Thank You/Like System with MyAlerts. Is MyAlerts version 2.0.0 or above installed and activated? If so, is it already integrated with the Thank You/Like System?';
$l['tyl_info_desc_alerts_registeralerttype'] = "Register a new alert type into MyAlerts";
$l['tyl_myalerts_version_under_2_0_4'] = "Your MyAlerts version ({1}) is less than the required version (2.0.4) for full support: until you upgrade MyAlerts, alerts of thanks/likes for a post will incorrectly <em>always</em> show <em>all</em> thanks/likes as \"new\".";

$l['tyl_title'] = "Thank You/Like System";
$l['tyl_desc'] = "Settings to customize the Thank You/Like System plugin.";

$l['tyl_enabled_title'] = "Enable/Disable";
$l['tyl_enabled_desc'] = "Enable/Disable the Thank You/Like System.";

$l['tyl_thankslike_title'] = "Thank you or like";
$l['tyl_thankslike_desc'] = "Choose whether you want to use the thank you system or the like system (affects only wordings; does not affect functionality).";
$l['tyl_thankslike_op_1'] = "Use thank you system";
$l['tyl_thankslike_op_2'] = "Use like system";

$l['tyl_firstall_title'] = "First post only or all posts";
$l['tyl_firstall_desc'] = "Do you want to allow thanks/likes to be given to the first post of a thread only or to all posts?";
$l['tyl_firstall_op_1'] = "First post only";
$l['tyl_firstall_op_2'] = "All posts";

$l['tyl_firstalloverride_title'] = "'First post only' forums override";
$l['tyl_firstalloverride_desc'] = "Select any forums for which you want to override the 'First post only' setting above so that thanks/likes may be given for ALL posts in those forums (select only forums - selecting categories will have no effect!).";

$l['tyl_likersdisplay_title'] = "Display of likers list";
$l['tyl_likersdisplay_desc'] = "Choose which form you would like the list of likers at the bottom of each post to be displayed in.";
$l['tyl_likersdisplay_op_1'] = "As usernames";
$l['tyl_likersdisplay_op_2'] = "As avatars";

$l['tyl_removing_title'] = "Allow removing";
$l['tyl_removing_desc'] = "Do you want to allow the removing of a thank/like from a post already thanked/liked?";

$l['tyl_tylownposts_title'] = "Allow self thanks/likes";
$l['tyl_tylownposts_desc'] = "Do you want to allow users to give thanks/likes to their own posts?";

$l['tyl_remowntylfroms_title'] = "Hide self-given thanks/likes from search results";
$l['tyl_remowntylfroms_desc'] = "Choose \"Yes\" to hide self-given thanks/likes from the result listings of searches for any given user's thanks/likes.";

$l['tyl_remowntylfromc_title'] = "Remove self-given thanks/likes from counters";
$l['tyl_remowntylfromc_desc'] = "Choose \"Yes\" to hide self-given thanks/likes from counters of thanks/likes.";

$l['tyl_reputation_add_title'] = "Add reputation points for thanks/likes";
$l['tyl_reputation_add_desc'] = "Do you want to add reputation points when thanks/likes are given?";

$l['tyl_reputation_add_reppoints_title'] = "Number of reputation points to add";
$l['tyl_reputation_add_reppoints_desc'] = "Enter the number of reputation points you want to add for a thank/like (default value: 1).";

$l['tyl_reputation_add_repcomment_title'] = "Reputation message";
$l['tyl_reputation_add_repcomment_desc'] = "Enter the message to be displayed on the thanked/liked user's reputation overview (default value: empty).";

$l['tyl_closedthreads_title'] = "Allow in closed threads";
$l['tyl_closedthreads_desc'] = "Do you want to allow thanks/likes to be given in closed threads?";

$l['tyl_exclude_title'] = "Excluded forums";
$l['tyl_exclude_desc'] = "Select forums in which giving thanks/likes is not permitted (select only forums - selecting categories will have no effect!).<br />Note: Thanks/likes are not recounted automatically when this setting is changed: you can recount them manually through <a href=\"index.php?module=tools-recount_rebuild\">Recount & Rebuild in the ACP</a> after you change and save this setting.";

$l['tyl_exclude_count_title'] = "Forums excluded from counts";
$l['tyl_exclude_count_desc'] = "Select forums in which you do not want any thanks/likes to count towards any member's publicly visible count of given and received thanks/likes (select only forums - selecting categories will have no effect!).<br />Note: Thanks/likes are not recounted automatically when this setting is changed: you can recount them manually through <a href=\"index.php?module=tools-recount_rebuild\">Recount & Rebuild in the ACP</a> after you change and save this setting.";

$l['tyl_unameformat_title'] = "Format usernames";
$l['tyl_unameformat_desc'] = "Do you want to format according to their usergroups the usernames of the post's author and its likers in the list of thanks/likes displayed under each post?";

$l['tyl_hideforgroups_title'] = "Hide thank/like buttons per usergroup";
$l['tyl_hideforgroups_desc'] = "Select usergroups to whose members the thank/like buttons should not be shown.";

$l['tyl_showdt_title'] = "Show date/time";
$l['tyl_showdt_desc'] = "How should the dates/times of thanks/likes in the list below each post be displayed?";
$l['tyl_showdt_op_1'] = "Do not display";
$l['tyl_showdt_op_2'] = "Display next to user name";
$l['tyl_showdt_op_3'] = "Display on mouse hover over username";

$l['tyl_dtformat_title'] = "Date/time format";
$l['tyl_dtformat_desc'] = "Set the format you want to use to show the date/time in the thanks/likes list.<br />The format is as for PHP's <a href=\"http://php.net/manual/en/function.date.php\">date</a>() function.<br />Example format: <em>m-d-Y h:i A</em> &lt;&lt;will show&gt;&gt; <em>12-31-2009 12:01 PM</em>";

$l['tyl_sortorder_title'] = "Sort order";
$l['tyl_sortorder_desc'] = "Select the sort order for the thanks/likes lists.";
$l['tyl_sortorder_op_1'] = "Username ascending";
$l['tyl_sortorder_op_2'] = "Username descending";
$l['tyl_sortorder_op_3'] = "Date/time added ascending";
$l['tyl_sortorder_op_4'] = "Date/time added descending";

$l['tyl_collapsible_title'] = "Thanks/likes list collapsibility";
$l['tyl_collapsible_desc'] = "Do you want the thanks/likes lists to be collapsible (with the ability to show/hide them)?";

$l['tyl_colldefault_title'] = "Default collapsible state";
$l['tyl_colldefault_desc'] = "If you want the lists to be collapsible, in which default state do you want them to be when the page loads?";
$l['tyl_colldefault_op_1'] = "Open (shown)";
$l['tyl_colldefault_op_2'] = "Closed (hidden/collapsed)";

$l['tyl_hidelistforgroups_title'] = "Hide thanks/likes lists per usergroup";
$l['tyl_hidelistforgroups_desc'] = "Select usergroups to whose members the thanks/likes lists should not be shown.";

$l['tyl_rcvdlikesclassranges_title'] = "Received likes styling ranges";
$l['tyl_rcvdlikesclassranges_desc'] = "Supports styling of the display of members' received likes counts in post metadata and on their profiles based on the size of the count. Here, provide a comma-separated list of numbers of received likes. Where received likes are shown, they will be given a class as follows, by which they can be styled in thankyoulike.css: when a members' received likes fall into the range from (and including) the first comma-separated number to (but not including) the second comma-separated number, the given class is 'tyl_rcvdlikesrange_1'; for the second range, it is 'tyl_rcvdlikesrange_2'; etc";

$l['tyl_displaygrowl_title'] = "Display of add/remove notification popup";
$l['tyl_displaygrowl_desc'] = "Choose \"On\" to enable popup notifications when a user clicks on a button to add/remove a thank/like. These pop up in the top right corner of the page and disappear automatically after a delay (they can also be closed manually). They indicate success or failure along with any error messages and any remaining limits.";

$l['tyl_limits_title'] = "Enable usergroup-based thanks/likes limits?";
$l['tyl_limits_desc'] = "Choose \"Yes\" to enable this feature. Settings for each usergroup can then be customised in the ACP under Users & Groups -> <a href=\"index.php?module=user-groups\">Groups</a> -> [Selected group] -> Users and Permissions [tab] -> Thanks/likes limits [heading]. The available settings are \"Flood prevention interval\" and \"Maximum thanks/likes allowed per 24 hours\".";
$l['tyl_limits_permissions_system'] = "Thanks/likes limits";
$l['tyl_limits_permissions_title'] = "Maximum thanks/likes allowed per 24 hours:";
$l['tyl_limits_permissions_desc'] = "The maximum number of thanks/likes that users in this group may give per day (24 hours). To allow unlimited thanks/likes per hour, enter 0.";
$l['tyl_flood_interval_title'] = "Flood prevention interval";
$l['tyl_flood_interval_desc'] = "The minimum number of seconds a member must wait between adding/removing thanks/likes. To disable the requirement to wait at all, enter zero.";

$l['tyl_highlight_popular_posts_title'] = "Highlight posts with a given number of thanks/likes?";
$l['tyl_highlight_popular_posts_desc'] = "Choose \"Yes\" to enable this feature. You can then customize the highlighted posts' style via the CSS class selector <strong>.popular_post</strong> in thankyoulike.css in your <a href\"index.php?module=style-themes\">theme</a>.";
$l['tyl_highlight_popular_posts_count_title'] = "Minimum count for highlight posts";
$l['tyl_highlight_popular_posts_count_desc'] = "Posts with at least this many thanks/likes will be highlighted.";

$l['tyl_display_tyl_counter_forumdisplay_title'] = "Enable thanks/likes count for first posts on forum pages?";
$l['tyl_display_tyl_counter_forumdisplay_desc'] = "Selecting \"Yes\" will display on forum pages the number of thanks/likes that the first post of each thread received.";

$l['tyl_display_tyl_counter_search_page_title'] = "Enable thanks/likes count for first posts in search results?";
$l['tyl_display_tyl_counter_search_page_desc'] = "Selecting \"Yes\" will display on search results pages the number of thanks/likes that the first post of each thread received.";

$l['tyl_show_memberprofile_box_title'] = "Enable trophy posts in member profiles?";
$l['tyl_show_memberprofile_box_desc'] = "When enabled (choose \"Yes\"), each member's trophy post (most thanked/liked post) will be shown on his/her profile page.";
$l['tyl_profile_box_show_parent_forums_title'] = "Show parent forums?";
$l['tyl_profile_box_show_parent_forums_description'] = "Selecting \"Yes\" will show the parent forums of the trophy post's forum.";
$l['tyl_profile_box_post_cutoff_title'] = "Trophy post character cut-off";
$l['tyl_profile_box_post_cutoff_desc'] = "Set the maximum number of (Unicode) characters to display in profile page trophy posts (0 for no limit).";
$l['tyl_profile_box_post_allowhtml_title'] = "Allow HTML in trophy posts?";
$l['tyl_profile_box_post_allowhtml_desc'] = "Selecting \"Yes\" will enable the rendering of HTML in profile page trophy posts.";
$l['tyl_profile_box_post_allowmycode_title'] = "Allow MyCode";
$l['tyl_profile_box_post_allowmycode_desc'] = "Selecting \"Yes\" will enable the rendering of MyCodes in profile page trophy posts.";
$l['tyl_profile_box_post_allowsmilies_title'] = "Allow Smilies";
$l['tyl_profile_box_post_allowsmilies_desc'] = "Selecting \"Yes\" will enable the rendering of Smilies in profile page trophy posts.";
$l['tyl_profile_box_post_allowimgcode_title'] = "Allow [img] Code";
$l['tyl_profile_box_post_allowimgcode_desc'] = "Selecting \"Yes\" will enable the rendering of [img] MyCodes in profile page trophy posts.";
$l['tyl_profile_box_post_allowvideocode_title'] = "Allow [video] Code";
$l['tyl_profile_box_post_allowvideocode_desc'] = "Selecting \"Yes\" will enable the rendering of [video] MyCodes in profile page trophy posts.";

$l['tyl_show_memberprofile_stats_title'] = "Enable stats in member profiles?";
$l['tyl_show_memberprofile_stats_desc'] = "When enabled (choose \"Yes\"), a statistical breakdown of each member's likes will be shown on his/her profile page.";

$l['tyl_uninstall'] = 'Thank You/Like System - uninstallation';
$l['tyl_uninstall_message'] = "Do you wish to drop ALL plugin data from the database? Selecting \"No\" will leave untouched:<ul><li>Entries for thanks/likes given and received.</li>\n<li>Per-user counts of thanks/likes.</li>\n<li>Overall thanks/likes statistics.</li>\n<li>The Thanks/Likes alert type and any of its alerts (only applies if MyAlerts integration is enabled).</li>\n</ul>\nSelecting \"No\" will <em>not</em>, however, prevent the removal of:<ul><li>Plugin settings (including any per-usergroup settings).</li>\n<li>The plugin's stylesheet, \"thankyoulike.css\" (including any changes you've made to it), accessible for each theme via the ACP's Templates & Style -> <a href=\"index.php?module=style-themes\">Themes</a> module.</li>\n<li>The plugin's templates (including any changes you've made to them), accessible  under \"Thank You/Like Templates\" for each template set via the ACP's Templates & Style -> <a href=\"index.php?module=style-templates\">Templates</a> module.</li>\n</ul>";

$l['setting_thankyoulike_promotion_rcv'] = 'Thanks/likes received';
$l['setting_thankyoulike_promotion_rcv_desc'] = 'Enter the number of received thanks/likes required. Thanks/Likes count must be selected as a required value for this to be included. Select the type of comparison for thanks/likes.';

$l['setting_thankyoulike_promotion_gvn'] = 'Thanks/likes given';
$l['setting_thankyoulike_promotion_gvn_desc'] = 'Enter the number of given thanks/likes required. Thanks/likes count must be selected as a required value for this to be included. Select the type of comparison for thanks/likes.';

$l['tyl_recount'] = "Recount thanks/likes";
$l['tyl_recount_do_desc'] = "When this is run, the thanks/likes count for each user and post will be updated to reflect its current live value based on the data in the database.";
$l['tyl_success_thankyoulike_rebuilt'] = "The thanks/likes have been recounted successfully.";

$l['tyl_admin_log_action'] = "Thanks/likes successfully recounted.";

$l['tyl_recount2'] = "(Re)initialise last alerted thank/like for each post";
$l['tyl_recount_do_desc2'] = "When this is run, the ID of the last alerted thank/like for each post for which all alerts for its thanks/likes have already been viewed will be updated so that subsequent alerts show the correct count of new thanks/likes.";
$l['tyl_success_thankyoulike_rebuilt2'] = "The ID of the last alerted thank/like for each post has been (re)initialised successfully.";

$l['tyl_admin_log_action2'] = "ID of last alerted thank/like for each post successfully (re)initialised.";

$l['tyl_missing_index'] = "The `{1}` index is missing from the `{2}` database table. {3} Click <a href=\"index.php?module=config-plugins&amp;action={4}\">here</a> to create it. If the `{2}` database table has a lot of entries, the creation of this index might take some time.";
$l['tyl_missing_index_purpose1'] = "This index speeds up the generation of the tabulated display of thank you / like statistics in member profiles.";
$l['tyl_missing_index_purpose2'] = "This index speeds up the determination of the trophy post in member profiles.";
$l['tyl_success_index_create'] = 'Successfully created the `{1}` index on the `{2}` table.';
