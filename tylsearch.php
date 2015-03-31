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
 *
 ****************************************************
 * This is a modified search.php file 
 * to use for thank you/like system
 ****************************************************
 * $Id: tylsearch.php 53 2011-10-26 08:17:45Z - G33K - $
 */


define("IN_MYBB", 1);
define("IGNORE_CLEAN_VARS", "sid");
define('THIS_SCRIPT', 'tylsearch.php');

$templatelist = "search,forumdisplay_thread_gotounread,search_results_threads_thread,search_results_threads,search_results_posts,search_results_posts_post";
$templatelist .= ",multipage_nextpage,multipage_page_current,multipage_page,multipage_start,multipage_end,multipage,forumdisplay_thread_multipage_more,forumdisplay_thread_multipage_page,forumdisplay_thread_multipage";
$templatelist .= ",search_results_posts_inlinecheck,search_results_posts_nocheck,search_results_threads_inlinecheck,search_results_threads_nocheck,search_results_inlinemodcol,search_results_posts_inlinemoderation_custom_tool,search_results_posts_inlinemoderation_custom,search_results_posts_inlinemoderation,search_results_threads_inlinemoderation_custom_tool,search_results_threads_inlinemoderation_custom,search_results_threads_inlinemoderation,search_orderarrow,search_moderator_options";
$templatelist .= ",forumdisplay_thread_attachment_count,forumdisplay_threadlist_inlineedit_js,search_threads_inlinemoderation_selectall";

require_once "./global.php";
require_once MYBB_ROOT."inc/functions_post.php";
require_once MYBB_ROOT."inc/functions_search.php";
require_once MYBB_ROOT."inc/class_parser.php";
$parser = new postParser;

$prefix = "g33k_thankyoulike_";

// Load global language phrases
$lang->load("search");
$lang->load("thankyoulike");

// Access to this file only if plugin is existant and active
if (!$mybb->settings[$prefix.'enabled'])
{
	error($lang->sprintf($lang->tyl_error_disabled, "This"));
}

add_breadcrumb($lang->nav_search, "search.php");

switch($mybb->input['action'])
{
	case "results":
		add_breadcrumb($lang->nav_results);
		break;
	default:
		break;
}

if($mybb->usergroup['cansearch'] == 0)
{
	error_no_permission();
}

if ($mybb->settings[$prefix.'thankslike'] == "like")
{
	$pre = $lang->tyl_like;
	$pre1 = $lang->tyl_liked;
}
else
{
	$pre = $lang->tyl_thankyou;
	$pre1 = $lang->tyl_thanked;
}

if($mybb->settings[$prefix.'enabled'] != "1")
{
	error($lang->sprintf($lang->tyl_error_disabled, $pre));
}

$tyl_uid = 	intval($mybb->input['uid']);

$now = TIME_NOW;

$limitsql = "";
if(intval($mybb->settings['searchhardlimit']) > 0)
{
	$limitsql = "LIMIT ".intval($mybb->settings['searchhardlimit']);
}

if($mybb->input['action'] == "results")
{
	$sid = $db->escape_string($mybb->input['sid']);
	$query = $db->simple_select("searchlog", "*", "sid='$sid'");
	$search = $db->fetch_array($query);

	if(!$search['sid'])
	{
		error($lang->error_invalidsearch);
	}

	// Decide on our sorting fields and sorting order.
	$order = my_strtolower(htmlspecialchars($mybb->input['order']));
	$sortby = my_strtolower(htmlspecialchars($mybb->input['sortby']));

	switch($sortby)
	{
		case "replies":
			$sortfield = "t.replies";
			break;
		case "views":
			$sortfield = "t.views";
			break;
		case "subject":
			if($search['resulttype'] == "threads")
			{
				$sortfield = "t.subject";
			}
			else
			{
				$sortfield = "p.subject";
			}
			break;
		case "forum":
			$sortfield = "t.fid";
			break;
		case "starter":
			if($search['resulttype'] == "threads")
			{
				$sortfield = "t.username";
			}
			else
			{
				$sortfield = "p.username";
			}
			break;
		case "dateline":
		default:
			if($search['resulttype'] == "threads")
			{
				$sortfield = "t.lastpost";
				$sortby = "lastpost";
			}
			else
			{
				$sortfield = "tyl.dateline";
				$sortby = "dateline";
			}
			break;
	}
	
	if($order != "asc")
	{
		$order = "desc";
		$oppsortnext = "asc";
		$oppsort = $lang->asc;
	}
	else
	{
		$oppsortnext = "desc";
		$oppsort = $lang->desc;		
	}
	
	if(!$mybb->settings['threadsperpage'])
	{
		$mybb->settings['threadsperpage'] = 20;
	}

	// Work out pagination, which page we're at, as well as the limits.
	$perpage = $mybb->settings['threadsperpage'];
	$page = intval($mybb->input['page']);
	if($page > 0)
	{
		$start = ($page-1) * $perpage;
	}
	else
	{
		$start = 0;
		$page = 1;
	}
	$end = $start + $perpage;
	$lower = $start+1;
	$upper = $end;
	
	// Work out if we have terms to highlight
	$highlight = "";
	
	$sorturl = "tylsearch.php?action=results&amp;sid={$sid}&amp;uid={$tyl_uid}";
	$thread_url = "";
	$post_url = "";
	
	eval("\$orderarrow['$sortby'] = \"".$templates->get("search_orderarrow")."\";");

	// Read some caches we will be using
	$forumcache = $cache->read("forums");
	$icon_cache = $cache->read("posticons");

	$threads = array();

	if($mybb->user['uid'] == 0)
	{
		// Build a forum cache.
		$query = $db->query("
			SELECT fid
			FROM ".TABLE_PREFIX."forums
			WHERE active != 0
			ORDER BY pid, disporder
		");
		
		$forumsread = unserialize($mybb->cookies['mybb']['forumread']);
	}
	else
	{
		// Build a forum cache.
		$query = $db->query("
			SELECT f.fid, fr.dateline AS lastread
			FROM ".TABLE_PREFIX."forums f
			LEFT JOIN ".TABLE_PREFIX."forumsread fr ON (fr.fid=f.fid AND fr.uid='{$mybb->user['uid']}')
			WHERE f.active != 0
			ORDER BY pid, disporder
		");
	}
	while($forum = $db->fetch_array($query))
	{
		if($mybb->user['uid'] == 0)
		{
			if($forumsread[$forum['fid']])
			{
				$forum['lastread'] = $forumsread[$forum['fid']];
			}
		}
		$readforums[$forum['fid']] = $forum['lastread'];
	}
	$fpermissions = forum_permissions();
	
	// Inline Mod Column for moderators
	$inlinemodcol = $inlinecookie = '';
	$is_mod = $is_supermod = false;
	if($mybb->usergroup['issupermod'])
	{
		$is_supermod = true;
	}
	if($is_supermod || is_moderator())
	{
		eval("\$inlinemodcol = \"".$templates->get("search_results_inlinemodcol")."\";");
		$inlinecookie = "inlinemod_search".$sid;
		$inlinecount = 0;
		$is_mod = true;
		$return_url = 'tylsearch.php?'.htmlspecialchars_uni($_SERVER['QUERY_STRING']);
	}

	// Show search results as 'threads'
	if($search['resulttype'] == "threads")
	{
		$threadcount = 0;
		
		// Moderators can view unapproved threads
		if($mybb->version_code < 1600)
		{
			$query = $db->simple_select("moderators", "fid", "uid='{$mybb->user['uid']}'");
		}
		else
		{
			$query = $db->simple_select("moderators", "fid", "(id='{$mybb->user['uid']}' AND isgroup='0') OR (id='{$mybb->user['usergroup']}' AND isgroup='1')");
		}
		if($mybb->usergroup['issupermod'] == 1)
		{
			// Super moderators (and admins)
			$unapproved_where = "t.visible>-1";
		}
		elseif($db->num_rows($query))
		{
			// Normal moderators
			$moderated_forums = '0';
			while($forum = $db->fetch_array($query))
			{
				$moderated_forums .= ','.$forum['fid'];
			}
			$unapproved_where = "(t.visible>0 OR (t.visible=0 AND t.fid IN ({$moderated_forums})))";
		}
		else
		{
			// Normal users
			$unapproved_where = 't.visible>0';
		}
		
		// If we have saved WHERE conditions, execute them
		if($search['querycache'] != "")
		{
			$where_conditions = $search['querycache'];
			$query = $db->simple_select("threads t", "t.tid", $where_conditions. " AND {$unapproved_where} AND t.closed NOT LIKE 'moved|%' {$limitsql}");
			while($thread = $db->fetch_array($query))
			{
				$threads[$thread['tid']] = $thread['tid'];
				$threadcount++;
			}
			// Build our list of threads.
			if($threadcount > 0)
			{
				$search['threads'] = implode(",", $threads);
			}
			// No results.
			else
			{
				error($lang->error_nosearchresults);
			}
			$where_conditions = "t.tid IN (".$search['threads'].")";
		}
		// This search doesn't use a query cache, results stored in search table.
		else
		{
			$where_conditions = "t.tid IN (".$search['threads'].")";
			$query = $db->simple_select("threads t", "COUNT(t.tid) AS resultcount", $where_conditions. " AND {$unapproved_where} AND t.closed NOT LIKE 'moved|%' {$limitsql}");
			$count = $db->fetch_array($query);

			if(!$count['resultcount'])
			{
				error($lang->error_nosearchresults);
			}
			$threadcount = $count['resultcount'];
		}
		
		// Begin selecting matching threads, cache them.
		$sqlarray = array(
			'order_by' => $sortfield,
			'order_dir' => $order,
			'limit_start' => $start,
			'limit' => $perpage
		);
		if($mybb->version_code < 1600)
		{
			$query = $db->query("
				SELECT t.*, u.username AS userusername
				FROM ".TABLE_PREFIX."threads t
				LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=t.uid)
				WHERE $where_conditions AND {$unapproved_where} AND t.closed NOT LIKE 'moved|%'
				ORDER BY $sortfield $order
				LIMIT $start, $perpage
			");
		}
		else
		{
			$query = $db->query("
				SELECT t.*, u.username AS userusername, p.displaystyle AS threadprefix
				FROM ".TABLE_PREFIX."threads t
				LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=t.uid)
				LEFT JOIN ".TABLE_PREFIX."threadprefixes p ON (p.pid=t.prefix)
				WHERE $where_conditions AND {$unapproved_where} AND t.closed NOT LIKE 'moved|%'
				ORDER BY $sortfield $order
				LIMIT $start, $perpage
			");
		}
		$thread_cache = array();
		while($thread = $db->fetch_array($query))
		{
			$thread_cache[$thread['tid']] = $thread;
		}
		$thread_ids = implode(",", array_keys($thread_cache));
		
		if($mybb->version_code >= 1600)
		{
			if(empty($thread_ids))
			{
				error($lang->error_nosearchresults);
			}
		}

		// Fetch dot icons if enabled
		if($mybb->settings['dotfolders'] != 0 && $mybb->user['uid'] && $thread_cache)
		{
			$query = $db->simple_select("posts", "DISTINCT tid,uid", "uid='".$mybb->user['uid']."' AND tid IN(".$thread_ids.")");
			while($thread = $db->fetch_array($query))
			{
				$thread_cache[$thread['tid']]['dot_icon'] = 1;
			}
		}

		// Fetch the read threads.
		if($mybb->user['uid'] && $mybb->settings['threadreadcut'] > 0)
		{
			$query = $db->simple_select("threadsread", "tid,dateline", "uid='".$mybb->user['uid']."' AND tid IN(".$thread_ids.")");
			while($readthread = $db->fetch_array($query))
			{
				$thread_cache[$readthread['tid']]['lastread'] = $readthread['dateline'];
			}
		}

		foreach($thread_cache as $thread)
		{
			$bgcolor = alt_trow();
			$folder = '';
			$prefix = '';
			
			// Unapproved colour
			if(!$thread['visible'])
			{
				$bgcolor = 'trow_shaded';
			}

			if($thread['userusername'])
			{
				$thread['username'] = $thread['userusername'];
			}
			$thread['profilelink'] = build_profile_link($thread['username'], $thread['uid']);
			
			if($mybb->version_code >= 1600)
			{
				// If this thread has a prefix, insert a space between prefix and subject
				if($thread['prefix'] != 0)
				{
					$thread['threadprefix'] .= '&nbsp;';
				}
			}
			
			$thread['subject'] = $parser->parse_badwords($thread['subject']);
			$thread['subject'] = htmlspecialchars_uni($thread['subject']);

			if($icon_cache[$thread['icon']])
			{
				$posticon = $icon_cache[$thread['icon']];
				$icon = "<img src=\"".$posticon['path']."\" alt=\"".$posticon['name']."\" />";
			}
			else
			{
				$icon = "&nbsp;";
			}
			if($thread['poll'])
			{
				$prefix = $lang->poll_prefix;
			}
				
			// Determine the folder
			$folder = '';
			$folder_label = '';
			if($thread['dot_icon'])
			{
				$folder = "dot_";
				$folder_label .= $lang->icon_dot;
			}
			$gotounread = '';
			$isnew = 0;
			$donenew = 0;
			$last_read = 0;
			
			if($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'])
			{
				$forum_read = $readforums[$thread['fid']];
			
				$read_cutoff = TIME_NOW-$mybb->settings['threadreadcut']*60*60*24;
				if($forum_read == 0 || $forum_read < $read_cutoff)
				{
					$forum_read = $read_cutoff;
				}
			}
			else
			{
				$forum_read = $forumsread[$thread['fid']];
			}
			
			if($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'] && $thread['lastpost'] > $forum_read)
			{
				if($thread['lastread'])
				{
					$last_read = $thread['lastread'];
				}
				else
				{
					$last_read = $read_cutoff;
				}
			}
			else
			{
				$last_read = my_get_array_cookie("threadread", $thread['tid']);
			}
	
			if($forum_read > $last_read)
			{
				$last_read = $forum_read;
			}

			if($thread['lastpost'] > $last_read && $last_read)
			{
				$folder .= "new";
				$new_class = "subject_new";
				$folder_label .= $lang->icon_new;
				$thread['newpostlink'] = get_thread_link($thread['tid'], 0, "newpost").$highlight;
				eval("\$gotounread = \"".$templates->get("forumdisplay_thread_gotounread")."\";");
				$unreadpost = 1;
			}
			else
			{
				$new_class = '';
				if($mybb->version_code >= 1600)
				{
					$new_class = 'subject_old';
				}
				$folder_label .= $lang->icon_no_new;
			}

			if($thread['replies'] >= $mybb->settings['hottopic'] || $thread['views'] >= $mybb->settings['hottopicviews'])
			{
				$folder .= "hot";
				$folder_label .= $lang->icon_hot;
			}
			if($thread['closed'] == 1)
			{
				$folder .= "lock";
				$folder_label .= $lang->icon_lock;
			}
			$folder .= "folder";
			
			if(!$mybb->settings['postsperpage'])
			{
				$mybb->settings['postperpage'] = 20;
			}

			$thread['pages'] = 0;
			$thread['multipage'] = '';
			$threadpages = '';
			$morelink = '';
			$thread['posts'] = $thread['replies'] + 1;
			if($mybb->version_code >= 1600)
			{
				if(is_moderator($thread['fid']))
				{
					$thread['posts'] += $thread['unapprovedposts'];
				}
			}
			if($thread['posts'] > $mybb->settings['postsperpage'])
			{
				$thread['pages'] = $thread['posts'] / $mybb->settings['postsperpage'];
				$thread['pages'] = ceil($thread['pages']);
				if($thread['pages'] > 4)
				{
					$pagesstop = 4;
					$page_link = get_thread_link($thread['tid'], $thread['pages']).$highlight;
					eval("\$morelink = \"".$templates->get("forumdisplay_thread_multipage_more")."\";");
				}
				else
				{
					$pagesstop = $thread['pages'];
				}
				for($i = 1; $i <= $pagesstop; ++$i)
				{
					$page_link = get_thread_link($thread['tid'], $i).$highlight;
					eval("\$threadpages .= \"".$templates->get("forumdisplay_thread_multipage_page")."\";");
				}
				eval("\$thread['multipage'] = \"".$templates->get("forumdisplay_thread_multipage")."\";");
			}
			else
			{
				$threadpages = '';
				$morelink = '';
				$thread['multipage'] = '';
			}
			$lastpostdate = my_date($mybb->settings['dateformat'], $thread['lastpost']);
			$lastposttime = my_date($mybb->settings['timeformat'], $thread['lastpost']);
			$lastposter = $thread['lastposter'];
			$thread['lastpostlink'] = get_thread_link($thread['tid'], 0, "lastpost");
			$lastposteruid = $thread['lastposteruid'];
			$thread_link = get_thread_link($thread['tid']);

			// Don't link to guest's profiles (they have no profile).
			if($lastposteruid == 0)
			{
				$lastposterlink = $lastposter;
			}
			else
			{
				$lastposterlink = build_profile_link($lastposter, $lastposteruid);
			}

			$thread['replies'] = my_number_format($thread['replies']);
			$thread['views'] = my_number_format($thread['views']);

			if($forumcache[$thread['fid']])
			{
				$thread['forumlink'] = "<a href=\"".get_forum_link($thread['fid'])."\">".$forumcache[$thread['fid']]['name']."</a>";
			}
			else
			{
				$thread['forumlink'] = "";
			}

			// If this user is the author of the thread and it is not closed or they are a moderator, they can edit
			if(($thread['uid'] == $mybb->user['uid'] && $thread['closed'] != 1 && $mybb->user['uid'] != 0 && $fpermissions[$thread['fid']]['caneditposts'] == 1) || is_moderator($fid, "caneditposts"))
			{
				$inline_edit_class = "subject_editable";
			}
			else
			{
				$inline_edit_class = "";
			}
			$load_inline_edit_js = 1;

			// If this thread has 1 or more attachments show the papperclip
			if($thread['attachmentcount'] > 0)
			{
				if($thread['attachmentcount'] > 1)
				{
					$attachment_count = $lang->sprintf($lang->attachment_count_multiple, $thread['attachmentcount']);
				}
				else
				{
					$attachment_count = $lang->attachment_count;
				}

				eval("\$attachment_count = \"".$templates->get("forumdisplay_thread_attachment_count")."\";");
			}
			else
			{
				$attachment_count = '';
			}

			$inline_edit_tid = $thread['tid'];
			
			// Inline thread moderation
			$inline_mod_checkbox = '';
			if($is_supermod || is_moderator($thread['fid']))
			{
				eval("\$inline_mod_checkbox = \"".$templates->get("search_results_threads_inlinecheck")."\";");
			}
			elseif($is_mod)
			{
				eval("\$inline_mod_checkbox = \"".$templates->get("search_results_threads_nocheck")."\";");
			}

			eval("\$results .= \"".$templates->get("search_results_threads_thread")."\";");
		}
		if(!$results)
		{
			error($lang->error_nosearchresults);
		}
		else
		{
			if($load_inline_edit_js == 1)
			{
				eval("\$inline_edit_js = \"".$templates->get("forumdisplay_threadlist_inlineedit_js")."\";");
			}
		}
		$multipage = multipage($threadcount, $perpage, $page, "tylsearch.php?action=results&amp;sid=$sid&amp;sortby=$sortby&amp;order=$order&amp;uid=".$mybb->input['uid']);
		if($upper > $threadcount)
		{
			$upper = $threadcount;
		}
		
		// Inline Thread Moderation Options
		if($is_mod)
		{
			if($mybb->version_code >= 1600)
			{
				// If user has moderation tools available, prepare the Select All feature
				$lang->page_selected = $lang->sprintf($lang->page_selected, count($thread_cache));
				$lang->all_selected = $lang->sprintf($lang->all_selected, intval($threadcount));
				$lang->select_all = $lang->sprintf($lang->select_all, intval($threadcount));
				eval("\$selectall = \"".$templates->get("search_threads_inlinemoderation_selectall")."\";");
			}
			
			$customthreadtools = '';
			if($mybb->version_code < 1600)
			{
				switch($db->type)
				{
					case "pgsql":
					case "sqlite3":
					case "sqlite2":
						$query = $db->simple_select("modtools", "tid, name", "type='t' AND (','||forums||',' LIKE '%,-1,%' OR forums='')");
						break;
					default:
						$query = $db->simple_select("modtools", "tid, name", "type='t' AND (CONCAT(',',forums,',') LIKE '%,-1,%' OR forums='')");
				}
			}
			else
			{
				switch($db->type)
				{
					case "pgsql":
					case "sqlite":
						$query = $db->simple_select("modtools", "tid, name", "type='t' AND (','||forums||',' LIKE '%,-1,%' OR forums='')");
						break;
					default:
						$query = $db->simple_select("modtools", "tid, name", "type='t' AND (CONCAT(',',forums,',') LIKE '%,-1,%' OR forums='')");
				}
			}
			
			while($tool = $db->fetch_array($query))
			{
				eval("\$customthreadtools .= \"".$templates->get("search_results_threads_inlinemoderation_custom_tool")."\";");
			}
			// Build inline moderation dropdown
			if(!empty($customthreadtools))
			{
				eval("\$customthreadtools = \"".$templates->get("search_results_threads_inlinemoderation_custom")."\";");
			}
			eval("\$inlinemod = \"".$templates->get("search_results_threads_inlinemoderation")."\";");
		}
		
		eval("\$searchresults = \"".$templates->get("search_results_threads")."\";");
		output_page($searchresults);
	}
	else // Displaying results as posts
	{
		if(!$search['posts'])
		{
			error($lang->error_nosearchresults);
		}
		
		if ($mybb->settings[$prefix.'thankslike'] == "like")
		{
			$lang->posted = $lang->tyl_liked;
		}
		else
		{
			$lang->posted = $lang->tyl_thanked;
		}
		
		$postcount = 0;
		
		// Moderators can view unapproved threads
		if($mybb->version_code < 1600)
		{
			$query = $db->simple_select("moderators", "fid", "uid='{$mybb->user['uid']}'");
		}
		else
		{
			$query = $db->simple_select("moderators", "fid", "(id='{$mybb->user['uid']}' AND isgroup='0') OR (id='{$mybb->user['usergroup']}' AND isgroup='1')");
		}
		if($mybb->usergroup['issupermod'] == 1)
		{
			// Super moderators (and admins)
			$p_unapproved_where = "visible >= 0";
			$t_unapproved_where = "visible < 0";
		}
		elseif($db->num_rows($query))
		{
			// Normal moderators
			$moderated_forums = '0';
			while($forum = $db->fetch_array($query))
			{
				$moderated_forums .= ','.$forum['fid'];
				$test_moderated_forums[$forum['fid']] = $forum['fid'];
			}
			$p_unapproved_where = "visible >= 0";
			$t_unapproved_where = "visible < 0 AND fid NOT IN ({$moderated_forums})";
		}
		else
		{
			// Normal users
			$p_unapproved_where = 'visible=1';
			$t_unapproved_where = 'visible < 1';
		}	
		
		if($mybb->version_code < 1600)
		{
			$post_cache_options = array('LIMIT' => intval($mybb->settings['searchhardlimit']));
		}
		else
		{
			$post_cache_options = array();
			if(intval($mybb->settings['searchhardlimit']) > 0)
			{
				$post_cache_options['limit'] = intval($mybb->settings['searchhardlimit']);
			}
		}
		
		if(strpos($sortfield, 'p.') !== false)
		{
			$post_cache_options['order_by'] = str_replace('p.', '', $sortfield);
			$post_cache_options['order_dir'] = $order;
		}

		$tids = array();
		$pids = array();
		// Make sure the posts we're viewing we have permission to view.
		$query = $db->simple_select("posts", "pid, tid", "pid IN(".$db->escape_string($search['posts']).") AND {$p_unapproved_where}", $post_cache_options);
		while($post = $db->fetch_array($query))
		{
			$pids[$post['pid']] = $post['tid'];
			$tids[$post['tid']][$post['pid']] = $post['pid'];
		}
		
		if(!empty($pids))
		{
			$temp_pids = array();

			// Check the thread records as well. If we don't have permissions, remove them from the listing.
			$query = $db->simple_select("threads", "tid", "tid IN(".$db->escape_string(implode(',', $pids)).") AND ({$t_unapproved_where} OR closed LIKE 'moved|%')");
			while($thread = $db->fetch_array($query))
			{
				if(array_key_exists($thread['tid'], $tids) != false)
				{
					$temp_pids = $tids[$thread['tid']];
					foreach($temp_pids as $pid)
					{
						unset($pids[$pid]);
						unset($tids[$thread['tid']]);
					}
				}
			}
			unset($temp_pids);
		}
	
		// Declare our post count
		$postcount = count($pids);
		
		if(!$postcount)
		{
			error($lang->error_nosearchresults);
		}
		
		// And now we have our sanatized post list
		$search['posts'] = implode(',', array_keys($pids));
		
		$tids = implode(",", array_keys($tids));
		
		// Read threads
		if($mybb->user['uid'] && $mybb->settings['threadreadcut'] > 0)
		{
			$query = $db->simple_select("threadsread", "tid, dateline", "uid='".$mybb->user['uid']."' AND tid IN(".$db->escape_string($tids).")");
			while($readthread = $db->fetch_array($query))
			{
				$readthreads[$readthread['tid']] = $readthread['dateline'];
			}
		}

		$dot_icon = array();
		if($mybb->settings['dotfolders'] != 0 && $mybb->user['uid'] != 0)
		{
			$query = $db->simple_select("posts", "DISTINCT tid,uid", "uid='".$mybb->user['uid']."' AND tid IN(".$db->escape_string($tids).")");
			while($post = $db->fetch_array($query))
			{
				$dot_icon[$post['tid']] = true;
			}
		}

		if ($search['keywords'] == 'for')
		{
			$tyl_puid = "tyl.puid=".$tyl_uid;
		}
		else
		{
			$tyl_puid = "tyl.uid=".$tyl_uid;
		}
		$query = $db->query("
			SELECT p.*, u.username AS userusername, t.subject AS thread_subject, t.replies AS thread_replies, t.views AS thread_views, t.lastpost AS thread_lastpost, t.closed AS thread_closed, t.uid AS thread_uid, tyl.dateline AS tyl_dateline
			FROM ".TABLE_PREFIX."posts p
			LEFT JOIN ".TABLE_PREFIX."threads t ON (t.tid=p.tid)
			LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=p.uid)
			LEFT JOIN ".TABLE_PREFIX.$prefix."thankyoulike tyl ON (tyl.pid=p.pid AND $tyl_puid)
			WHERE p.pid IN (".$db->escape_string($search['posts']).")
			ORDER BY $sortfield $order
			LIMIT $start, $perpage
		");

		while($post = $db->fetch_array($query))
		{
			$bgcolor = alt_trow();
			if(!$post['visible'])
			{
				$bgcolor = 'trow_shaded';
			}
			if($post['userusername'])
			{
				$post['username'] = $post['userusername'];
			}
			$post['profilelink'] = build_profile_link($post['username'], $post['uid']);
			$post['subject'] = $parser->parse_badwords($post['subject']);
			$post['thread_subject'] = $parser->parse_badwords($post['thread_subject']);
			$post['thread_subject'] = htmlspecialchars_uni($post['thread_subject']);

			if($icon_cache[$post['icon']])
			{
				$posticon = $icon_cache[$post['icon']];
				$icon = "<img src=\"".$posticon['path']."\" alt=\"".$posticon['name']."\" />";
			}
			else
			{
				$icon = "&nbsp;";
			}

			if($forumcache[$thread['fid']])
			{
				$post['forumlink'] = "<a href=\"".get_forum_link($post['fid'])."\">".$forumcache[$post['fid']]['name']."</a>";
			}
			else
			{
				$post['forumlink'] = "";
			}
			// Determine the folder
			$folder = '';
			$folder_label = '';
			$gotounread = '';
			$isnew = 0;
			$donenew = 0;
			$last_read = 0;
			$post['thread_lastread'] = $readthreads[$post['tid']];
			if($mybb->settings['threadreadcut'] > 0 && $mybb->user['uid'] && $post['thread_lastpost'] > $forumread)
			{
				$cutoff = TIME_NOW-$mybb->settings['threadreadcut']*60*60*24;
				if($post['thread_lastpost'] > $cutoff)
				{
					if($post['thread_lastread'])
					{
						$last_read = $post['thread_lastread'];
					}
					else
					{
						$last_read = 1;
					}
				}
			}

			if($dot_icon[$post['tid']])
			{
				$folder = "dot_";
				$folder_label .= $lang->icon_dot;
			}

			if(!$last_read)
			{
				$readcookie = $threadread = my_get_array_cookie("threadread", $post['tid']);
				if($readcookie > $forumread)
				{
					$last_read = $readcookie;
				}
				elseif($forumread > $mybb->user['lastvisit'])
				{
					$last_read = $forumread;
				}
				else
				{
					$last_read = $mybb->user['lastvisit'];
				}
			}

			if($post['thread_lastpost'] > $last_read && $last_read)
			{
				$folder .= "new";
				$folder_label .= $lang->icon_new;
				eval("\$gotounread = \"".$templates->get("forumdisplay_thread_gotounread")."\";");
				$unreadpost = 1;
			}
			else
			{
				$folder_label .= $lang->icon_no_new;
			}

			if($post['thread_replies'] >= $mybb->settings['hottopic'] || $post['thread_views'] >= $mybb->settings['hottopicviews'])
			{
				$folder .= "hot";
				$folder_label .= $lang->icon_hot;
			}
			if($thread['thread_closed'] == 1)
			{
				$folder .= "lock";
				$folder_label .= $lang->icon_lock;
			}
			$folder .= "folder";

			$post['thread_replies'] = my_number_format($post['thread_replies']);
			$post['thread_views'] = my_number_format($post['thread_views']);

			if($forumcache[$post['fid']])
			{
				$post['forumlink'] = "<a href=\"".get_forum_link($post['fid'])."\">".$forumcache[$post['fid']]['name']."</a>";
			}
			else
			{
				$post['forumlink'] = "";
			}

			if(!$post['subject'])
			{
				$post['subject'] = $post['message'];
			}
			if(my_strlen($post['subject']) > 50)
			{
				$post['subject'] = htmlspecialchars_uni(my_substr($post['subject'], 0, 50)."...");
			}
			else
			{
				$post['subject'] = htmlspecialchars_uni($post['subject']);
			}
			// What we do here is parse the post using our post parser, then strip the tags from it
			$parser_options = array(
				'allow_html' => 0,
				'allow_mycode' => 1,
				'allow_smilies' => 0,
				'allow_imgcode' => 0,
				'filter_badwords' => 1
			);
			$post['message'] = strip_tags($parser->parse_message($post['message'], $parser_options));
			if(my_strlen($post['message']) > 200)
			{
				$prev = my_substr($post['message'], 0, 200)."...";
			}
			else
			{
				$prev = $post['message'];
			}
			$posted = my_date($mybb->settings['dateformat'], $post['tyl_dateline']).", ".my_date($mybb->settings['timeformat'], $post['tyl_dateline']);
			
			$thread_url = get_thread_link($post['tid']);
			$post_url = get_post_link($post['pid'], $post['tid']);
			
			// Inline post moderation
			$inline_mod_checkbox = '';
			if($is_supermod || is_moderator($post['fid']))
			{
				eval("\$inline_mod_checkbox = \"".$templates->get("search_results_posts_inlinecheck")."\";");
			}
			elseif($is_mod)
			{
				eval("\$inline_mod_checkbox = \"".$templates->get("search_results_posts_nocheck")."\";");
			}

			eval("\$results .= \"".$templates->get("search_results_posts_post")."\";");
		}
		if(!$results)
		{
			error($lang->error_nosearchresults);
		}
		$multipage = multipage($postcount, $perpage, $page, "tylsearch.php?action=results&amp;sid=".htmlspecialchars_uni($mybb->input['sid'])."&amp;sortby=$sortby&amp;order=$order&amp;uid=".$mybb->input['uid']);
		if($upper > $postcount)
		{
			$upper = $postcount;
		}
		
		// Inline Post Moderation Options
		if($is_mod)
		{
			if($mybb->version_code >= 1600)
			{
				// If user has moderation tools available, prepare the Select All feature
				$num_results = $db->num_rows($query);
				$lang->page_selected = $lang->sprintf($lang->page_selected, intval($num_results));
				$lang->select_all = $lang->sprintf($lang->select_all, intval($postcount));
				$lang->all_selected = $lang->sprintf($lang->page_selected, intval($postcount));
				eval("\$selectall = \"".$templates->get("search_posts_inlinemoderation_selectall")."\";");
			}
			
			$customthreadtools = $customposttools = '';
			
			if($mybb->version_code < 1600)
			{
				switch($db->type)
				{
					case "pgsql":
					case "sqlite3":
					case "sqlite2":
						$query = $db->simple_select("modtools", "tid, name, type", "type='p' AND (','||forums||',' LIKE '%,-1,%' OR forums='')");
						break;
					default:
						$query = $db->simple_select("modtools", "tid, name, type", "type='p' AND (CONCAT(',',forums,',') LIKE '%,-1,%' OR forums='')");
				}
			}
			else
			{
				switch($db->type)
				{
					case "pgsql":
					case "sqlite":
						$query = $db->simple_select("modtools", "tid, name, type", "type='p' AND (','||forums||',' LIKE '%,-1,%' OR forums='')");
						break;
					default:
						$query = $db->simple_select("modtools", "tid, name, type", "type='p' AND (CONCAT(',',forums,',') LIKE '%,-1,%' OR forums='')");
				}
			}
			
			while($tool = $db->fetch_array($query))
			{
				eval("\$customposttools .= \"".$templates->get("search_results_posts_inlinemoderation_custom_tool")."\";");
			}
			// Build inline moderation dropdown
			if(!empty($customposttools))
			{
				eval("\$customposttools = \"".$templates->get("search_results_posts_inlinemoderation_custom")."\";");
			}
			eval("\$inlinemod = \"".$templates->get("search_results_posts_inlinemoderation")."\";");
		}

		eval("\$searchresults = \"".$templates->get("search_results_posts")."\";");
		output_page($searchresults);
	}
}
elseif($mybb->input['action'] == "usertylthreads" && $tyl_uid)
{
	$where_sql = "t.tid IN (SELECT p.tid 
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
	
	if($mybb->version_code >= 1600)
	{
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
	redirect("tylsearch.php?action=results&uid=".$tyl_uid."&sid=".$sid, $lang->redirect_searchresults);
}
elseif($mybb->input['action'] == "usertylposts" && $tyl_uid)
{
	$where_sql = "pid IN (SELECT tyl.pid 
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
	
	if($mybb->version_code >= 1600)
	{
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
	}
	
	if($mybb->version_code < 1600)
	{
		$options = array(
			'LIMIT' => intval($mybb->settings['searchhardlimit']),
			'order_by' => 'dateline',
			'order_dir' => 'DESC'
		);
	}
	else
	{
		$options = array(
			'order_by' => 'dateline',
			'order_dir' => 'desc'
		);

		// Do we have a hard search limit?
		if($mybb->settings['searchhardlimit'] > 0)
		{
			$options['limit'] = intval($mybb->settings['searchhardlimit']);
		}
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
	redirect("tylsearch.php?action=results&uid=".$tyl_uid."&sid=".$sid, $lang->redirect_searchresults);
}
elseif($mybb->input['action'] == "usertylforthreads" && $tyl_uid)
{
	$where_sql = "t.tid IN (
				SELECT p.tid 
				FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
				LEFT JOIN ".TABLE_PREFIX."posts p ON ( p.pid = tyl.pid )
				WHERE tyl.puid = $tyl_uid)";

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
	
	if($mybb->version_code >= 1600)
	{
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
	redirect("tylsearch.php?action=results&uid=".$tyl_uid."&sid=".$sid, $lang->redirect_searchresults);
}
elseif($mybb->input['action'] == "usertylforposts" && $tyl_uid)
{
	
	$where_sql = "pid IN (
				SELECT tyl.pid 
				FROM ".TABLE_PREFIX.$prefix."thankyoulike tyl
				WHERE tyl.puid = $tyl_uid)";
	
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
	
	if($mybb->version_code >= 1600)
	{
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
			$where_sql .= "AND ((fid IN(".implode(',', $onlyusfids).") AND t.uid='{$mybb->user['uid']}') OR t.fid NOT IN(".implode(',', $onlyusfids)."))";
		}
	}
	
	if($mybb->version_code < 1600)
	{
		$options = array(
			'LIMIT' => intval($mybb->settings['searchhardlimit']),
			'order_by' => 'dateline',
			'order_dir' => 'DESC'
		);
	}
	else
	{
		$options = array(
			'order_by' => 'dateline',
			'order_dir' => 'desc'
		);
		
		// Do we have a hard search limit?
		if($mybb->settings['searchhardlimit'] > 0)
		{
			$options['limit'] = intval($mybb->settings['searchhardlimit']);
		}
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
		"keywords" => 'for'
	);
	$db->insert_query("searchlog", $searcharray);
	redirect("tylsearch.php?action=results&uid=".$tyl_uid."&sid=".$sid, $lang->redirect_searchresults);
}
else
{
	redirect("search.php");
}
