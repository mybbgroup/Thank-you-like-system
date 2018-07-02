<?php
/**
 * Thank You / Like System Config Language Pack - English 
 * 
 */

$l['tyl_info_title'] = "Thank You/Like System";
$l['tyl_info_desc'] = "Adds option for users to thank the user for the post or like the post.";
$l['tyl_info_desc_url'] ="<br />*Edited and maintained for MyBB 1.8.x by: {1}, {2}, {3}, {4} and {5}<br />*Sources: {6}";
$l['tyl_info_desc_recount'] = "Recount thanks/likes";
$l['tyl_info_desc_configsettings'] = "Configure Settings";
$l['tyl_info_desc_alerts_error'] = "<b>Thank You/Like System is uninstalled or deactivated</b>";
$l['tyl_info_desc_alerts_integrate'] = "<b>Click <u>HERE</u> to integrate Thank You/Like System with MyAlerts</b>";
$l['tyl_info_desc_alerts_integrated'] = "<b>Thank You/Like System and MyAlerts were integrated successfully!</b>";
$l['tyl_info_desc_alerts_registeralerttype'] = "Register a new alert type into MyAlerts";

$l['tyl_title'] = "Thank You/Like System";
$l['tyl_desc'] = "Settings to customize Thank You/Like System plugin.";

$l['tyl_enabled_title'] = "Enable/Disable";
$l['tyl_enabled_desc'] = "Enable/Disable the Thank You/Like System.";

$l['tyl_thankslike_title'] = "Thank you or like";
$l['tyl_thankslike_desc'] = "Choose if you want to use thank you or like system.";
$l['tyl_thankslike_op_1'] = "Use thank you system";
$l['tyl_thankslike_op_2'] = "Use like system";

$l['tyl_firstall_title'] = "First post only or all posts";
$l['tyl_firstall_desc'] = "Do you want the thanks/likes to be given on the first post of a thread only or on all the posts of a thread?";
$l['tyl_firstall_op_1'] = "First post only";
$l['tyl_firstall_op_2'] = "All posts";

$l['tyl_firstalloverwrite_title'] = "Special option for display thank/like buttons in ALL posts";
$l['tyl_firstalloverwrite_desc'] = "Overwrite the above selected option All in certain forums (choose only forums, no categories!).";

$l['tyl_removing_title'] = "Allow removing";
$l['tyl_removing_desc'] = "Do you want to allow the removing of thank/like from a post already thanked/liked?";

$l['tyl_tylownposts_title'] = "Allow giving thanks/likes to own posts";
$l['tyl_tylownposts_desc'] = "Do you want to allow users to give thanks/likes to own posts?";

$l['tyl_remowntylfroms_title'] = "Remove own given thanks/likes from search results";
$l['tyl_remowntylfroms_desc'] = "Choose YES to hide own given Thanks/Likes on search list.";

$l['tyl_remowntylfromc_title'] = "Remove numbers of own given thanks/likes in counters";
$l['tyl_remowntylfromc_desc'] = "Choose YES to hide own given thanks/likes in post and member profile counters.";

$l['tyl_reputation_add_title'] = "Reputation points for given thanks/likes";
$l['tyl_reputation_add_desc'] = "Do you want to add reputation points for given thanks/likes?";

$l['tyl_reputation_add_reppoints_title'] = "Number of reputation points to add";
$l['tyl_reputation_add_reppoints_desc'] = "Enter the number of reputation points you want to add for a thank/like (default value: 1).";

$l['tyl_reputation_add_repcomment_title'] = "Comment of given reputation";
$l['tyl_reputation_add_repcomment_desc'] = "Enter a text which will be displayed on thanked/liked users reputation overview (default value: empty).";

$l['tyl_closedthreads_title'] = "Allow in closed threads";
$l['tyl_closedthreads_desc'] = "Do you want to allow to give thanks/likes in closed threads?";

$l['tyl_exclude_title'] = "Excluded forums";
$l['tyl_exclude_desc'] = "Select forums where you do not want the threads and posts to use the thanks/likes system (only forums - no categories!).<br />Note: Recount of thanks/likes is not automatic, you have to do it manually through <a href=\"index.php?module=tools-recount_rebuild\">Recount & Rebuild in ACP</a> after you change and save this setting.";

$l['tyl_exclude_count_title'] = "Forums excluded from counts";
$l['tyl_exclude_count_desc'] = "Select forums in which you do not want any thanks/likes to count towards any member's publicly visible count of given and received thanks/likes (only forums - no categories!).<br />Note: Recount of thanks/likes is not automatic, you have to do it manually through <a href=\"index.php?module=tools-recount_rebuild\">Recount & Rebuild in ACP</a> after you change and save this setting.";

$l['tyl_unameformat_title'] = "Format usernames";
$l['tyl_unameformat_desc'] = "Do you want to format the author's username and usernames in the thank/like list under the post according to their usergroups?";

$l['tyl_hideforgroups_title'] = "Hide thank/like button";
$l['tyl_hideforgroups_desc'] = "Select usergroups which cannot see thank/like button.";

$l['tyl_showdt_title'] = "Show date/time";
$l['tyl_showdt_desc'] = "Do you want to show date/time for thanks/likes in the list?";
$l['tyl_showdt_op_1'] = "Not display";
$l['tyl_showdt_op_2'] = "Display next to user name";
$l['tyl_showdt_op_3'] = "Display on mouse hover over username";

$l['tyl_dtformat_title'] = "Date/time format";
$l['tyl_dtformat_desc'] = "Set the format you want to use to show the date/time in the thanks/likes list.<br />Format is same as the one used by PHP\'s date() function.<br />Example format: m-d-Y h:i A &lt;&lt;will show&gt;&gt; 12-31-2009 12:01 PM";

$l['tyl_sortorder_title'] = "Sort order";
$l['tyl_sortorder_desc'] = "Select the sort order for the thanks/likes list.";
$l['tyl_sortorder_op_1'] = "Username ascending";
$l['tyl_sortorder_op_2'] = "Username descending";
$l['tyl_sortorder_op_3'] = "Date/time added ascending";
$l['tyl_sortorder_op_4'] = "Date/time added descending";

$l['tyl_collapsible_title'] = "Thank/like list collapsible";
$l['tyl_collapsible_desc'] = "Do you want the thank/like list to be collapsible (show/hide ability)?";

$l['tyl_colldefault_title'] = "Default collapsible state";
$l['tyl_colldefault_desc'] = "If you want the list to be collapsible, what is the default state you want it in when the page loads, open or closed (hidden)?";
$l['tyl_colldefault_op_1'] = "List shown";
$l['tyl_colldefault_op_2'] = "List hidden (collapsed)";

$l['tyl_hidelistforgroups_title'] = "Hide thanks/likes list";
$l['tyl_hidelistforgroups_desc'] = "Select usergroups which cannot see thanks/likes list.";

$l['tyl_displaygrowl_title'] = "Display add/remove notification popup";
$l['tyl_displaygrowl_desc'] = "Choose ON to enable the Ajax notification popup window.";

$l['tyl_limits_title'] = "Enable usergroup based thanks/likes limits?";
$l['tyl_limits_desc'] = "Choose YES to enable this feature and check the usergroup settings.";
$l['tyl_highlight_popular_posts_title'] = "Highlight posts with X numbers of thanks/likes?";
$l['tyl_highlight_popular_posts_desc'] = "Choose YES to enable this feature and define your style of CSS class <strong>.popular_post</strong> in thankyoulike.css (in your theme).";
$l['tyl_highlight_popular_posts_count_title'] = "Numbers of thanks/likes to highlight posts";
$l['tyl_highlight_popular_posts_count_desc'] = "Set the number of thanks/likes the post will be highlighted.";

$l['tyl_limits_permissions_system'] = "Thanks/likes limits";
$l['tyl_limits_permissions_title'] = "Maximum thanks/likes allowed per 24 hours:";
$l['tyl_limits_permissions_desc'] = "Here you can enter the maximum number of thanks/likes that users in this group can give per day (24 hours). To allow unlimited reputations per hour, enter 0.";

$l['tyl_show_memberprofile_box_title'] = "Enable member profile box?";
$l['tyl_show_memberprofile_box_desc'] = "Shows a box in a member profile page with user most thanked/liked post - Choose YES to enable it.";
$l['tyl_profile_box_post_cutoff_title'] = "Cut off post message";
$l['tyl_profile_box_post_cutoff_desc'] = "Set how many vars (symbols) you want to display in a post message (set 0 for no limit)";
$l['tyl_profile_box_post_allowhtml_title'] = "Allow HTML";
$l['tyl_profile_box_post_allowhtml_desc'] = "Selecting YES will allow HTML to be used in a post message.";
$l['tyl_profile_box_post_allowmycode_title'] = "Allow MyCode";
$l['tyl_profile_box_post_allowmycode_desc'] = "Selecting YES will allow MyCode to be used in a post message.";
$l['tyl_profile_box_post_allowsmilies_title'] = "Allow Smilies";
$l['tyl_profile_box_post_allowsmilies_desc'] = "Selecting YES will allow Smilies to be used in a post message.";
$l['tyl_profile_box_post_allowimgcode_title'] = "Allow [img] Code";
$l['tyl_profile_box_post_allowimgcode_desc'] = "Selecting YES will allow [img] Code to be used in a post message.";
$l['tyl_profile_box_post_allowvideocode_title'] = "Allow [video] Code";
$l['tyl_profile_box_post_allowvideocode_desc'] = "Selecting YES will allow [video] Code to be used in a post message.";

$l['tyl_uninstall'] = 'Thank You/Like System - uninstallation';
$l['tyl_uninstall_message'] = 'Do you wish to drop ALL plugin data entries from the database?';

$l['setting_thankyoulike_promotion_rcv'] = 'Thanks/likes received';
$l['setting_thankyoulike_promotion_rcv_desc'] = 'Enter the number of received thanks/likes required. Thanks/Likes count must be selected as a required value for this to be included. Select the type of comparison for thanks/likes.';

$l['setting_thankyoulike_promotion_gvn'] = 'Thanks/likes given';
$l['setting_thankyoulike_promotion_gvn_desc'] = 'Enter the number of given thanks/likes required. Thanks/likes count must be selected as a required value for this to be included. Select the type of comparison for thanks/likes.';

$l['tyl_recount'] = "Recount thanks/likes";
$l['tyl_recount_do_desc'] = "When this is run, the thanks/likes count for each user and post will be updated to reflect its current live value based on the data in the database.";
$l['tyl_success_thankyoulike_rebuilt'] = "The thanks/likes have been recounted successfully.";

$l['tyl_admin_log_action'] = "Thanks/likes successfully recounted.";
