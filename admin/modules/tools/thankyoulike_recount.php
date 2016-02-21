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

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$prefix = "g33k_thankyoulike_";

$page->add_breadcrumb_item($lang->tyl_recount, "index.php?module=tools-thankyoulike_recount");

function acp_tyl_recount()
{
	global $db, $mybb, $lang, $prefix;
	
	$page = $mybb->get_input('page', MyBB::INPUT_INT);
	$per_page = $mybb->get_input('tyls', MyBB::INPUT_INT);
	if($per_page <= 0)
	{
		$per_page = 500;
	}
	$start = ($page-1) * $per_page;
	$end = $start + $per_page;
	
	// On the first run, reset all totals and remove any orphaned tyls
	// Orphaned tyls are those with no linked posts or users
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

function check_proceed($current, $finish, $next_page, $per_page, $name, $name2, $message)
{
	global $page, $lang;
	
	if($finish >= $current)
	{
		flash_message($message, 'success');
		admin_redirect("index.php?module=tools-thankyoulike_recount");
	}
	else
	{
		$page->output_header();
		
		$form = new Form("index.php?module=tools-thankyoulike_recount", 'post');
		
		echo $form->generate_hidden_field("page", $next_page);
		echo $form->generate_hidden_field($name, $per_page);
		echo $form->generate_hidden_field($name2, $lang->go);
		echo "<div class=\"confirm_action\">\n";
		echo "<p>{$lang->tyl_confirm_proceed_rebuild}</p>\n";
		echo "<br />\n";
		echo "<script type=\"text/javascript\">$(function() { var button = $(\"#proceed_button\"); if(button.length > 0) { button.val(\"{$lang->tyl_automatically_redirecting}\"); button.attr(\"disabled\", true); button.css(\"color\", \"#aaa\"); button.css(\"borderColor\", \"#aaa\"); document.forms[0].submit(); }})</script>";
		echo "<p class=\"buttons\">\n";
		echo $form->generate_submit_button($lang->proceed, array('class' => 'button_yes', 'id' => 'proceed_button'));
		echo "</p>\n";
		echo "</div>\n";
		
		$form->end();
		
		$page->output_footer();
		exit;
	}
}

if(!$mybb->input['action'])
{	
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
				log_admin_action("Recounted ThankYou/Likes");
			}
			if(!$mybb->get_input('tyls', MyBB::INPUT_INT))
			{
				$mybb->input['tyls'] = 500;
			}
			
			acp_tyl_recount();
		}
	}	
	
	$page->output_header($lang->tyl_recount);
	
	$sub_tabs['thankyoulike_recount'] = array(
		'title' => $lang->tyl_recount,
		'link' => "index.php?module=tools-thankyoulike_recount",
		'description' => $lang->tyl_recount_desc
	);
	
	$page->output_nav_tabs($sub_tabs, 'thankyoulike_recount');

	$form = new Form("index.php?module=tools-thankyoulike_recount", "post");
	
	$form_container = new FormContainer($lang->tyl_recount);
	$form_container->output_row_header($lang->tyl_name);
	$form_container->output_row_header($lang->tyl_data_per_page, array('width' => 50));
	$form_container->output_row_header("&nbsp;");
	
	$form_container->output_cell("<label>{$lang->tyl_recount}</label><div class=\"description\">{$lang->tyl_recount_do_desc}</div>");
	$form_container->output_cell($form->generate_text_box("tyls", 500, array('style' => 'width: 150px;')));
	$form_container->output_cell($form->generate_submit_button($lang->go, array("name" => "do_recounttyls")));
	$form_container->construct_row();
	
	$form_container->end();

	$form->end();
		
	$page->output_footer();
}
