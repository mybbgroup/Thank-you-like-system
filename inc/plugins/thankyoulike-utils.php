<?php

/**
 * Check whether a forum ID is included in the value of a forum setting.
 * Note: Always returns true when the forum setting is "All", regardless of
 * whether or not the supplied ID exists.
 *
 * @param int The forum ID to check for.
 * @param mixed The value of the forum setting to check within.
 * @return boolean True if the forum ID is included; false if not.
 */
function is_forum_id_in_setting($fid, $forums)
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
