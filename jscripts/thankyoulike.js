var tylDataList = new Array();
var tylExpIdsByUser  = new Array();
var tylCollIdsByUser = new Array();
var tylOurUidIdxExp  = -1;
var tylOurUidIdxColl = -1;
var tylAddDoneHooks = new Array();
var tylDelDoneHooks = new Array();

var thankyoulike = {
	init: function()
	{
		$("[id^='tyl_data_']").each(function(){
			tylDataList.push(parseInt($(this).attr('id').match(/\d+/)),10);
		});

		var res = thankyoulike.parseExpCollCookie('tylexpids' , tylUser);
		tylOurUidIdxExp  = res.ouruididx;
		tylExpIdsByUser  = res.array;
		res = thankyoulike.parseExpCollCookie('tylcollids', tylUser);
		tylOurUidIdxColl = res.ouruididx;
		tylCollIdsByUser = res.array;

		var i = 0;
		for(i = 0; i < tylDataList.length; ++i) {
			var expIdx  = $.inArray(tylDataList[i], tylExpIdsByUser [tylOurUidIdxExp ].pids);
			var collIdx = $.inArray(tylDataList[i], tylCollIdsByUser[tylOurUidIdxColl].pids);
			if(expIdx !== -1 && collIdx !== -1) {
				// Inconsistency: post is in both stored collapse and stored expand lists.
				// Remove it from both.
				tylExpIdsByUser [tylOurUidIdxExp ].pids.splice(expIdx , 1);
				tylCollIdsByUser[tylOurUidIdxColl].pids.splice(collIdx, 1);
				expIdx = collIdx = -1;
			}
			// Collapse the TYL list for this post if and only if the collapsible setting is true AND
			// either the default collapse setting is set to collapse and this is not overridden by the expanded state,
			// or the default collapse setting is set to uncollapsed but this IS overridden by the collapsed state.
			if(tylCollapsible == 1 && ((tylCollDefault == 'closed' && expIdx === -1) || tylCollDefault == 'open' && collIdx !== -1))
			{
				thankyoulike.fleece(tylDataList[i]);
			}
			else
			{
				thankyoulike.display(tylDataList[i]);
			}
		}
	},

	parseExpCollCookie: function(cookieName, tylUser)
	{
		var haveOurUid = false;
		var ourUidIdx = -1;
		var expCollIdsByUser = new Array();
		var expCollCookie = Cookie.get(cookieName);
		var idx = 0;
		if (expCollCookie) {
			var usersSplitCookie = expCollCookie.split(/;/);
			for(var i = 0; i < usersSplitCookie.length; ++i)
			{
				splitArr = usersSplitCookie[i].split(/:/);
				// Via the conditional, discard states which are not associated with a user ID.
				if(splitArr.length >= 2)
				{
					if(splitArr[0] == tylUser)
					{
						haveOurUid = true;
						ourUidIdx = idx;
					}
					expCollIdsByUser[idx++] = {
						uid : parseInt(splitArr[0], 10),
						pids: splitArr[1] ? $.uniqueSort(splitArr[1].split(/,/).map(function(pid){return parseInt(pid, 10);})) : new Array()
					}
				}
			}
		}
		if(!haveOurUid)
		{
			expCollIdsByUser[idx] = {
				uid : tylUser,
				pids: new Array()
			}
			ourUidIdx = idx;
		}

		return {ouruididx: ourUidIdx, array: expCollIdsByUser};
	},

	saveExpCollCookie: function(cookieName, expCollIdsByUser)
	{
		var cookieData = '';
		var i;
		for(i = 0; i < expCollIdsByUser.length; i++)
		{
			if(cookieData) cookieData += ';';
			cookieData += expCollIdsByUser[i].uid + ':' + expCollIdsByUser[i].pids.join(',');
		}

		Cookie.set(cookieName, cookieData);
	},

	tgl: function(pid)
	{
		if(tylCollapsible == 1)
		{
			var expIdx  = $.inArray(pid, tylExpIdsByUser [tylOurUidIdxExp ].pids);
			var collIdx = $.inArray(pid, tylCollIdsByUser[tylOurUidIdxColl].pids);

			if($('#tyl_data_'+pid).is(':visible'))
			{
				thankyoulike.fleece(pid);

				// Remove this pid from the expanded state (if it's in it) and
				// add it to the collapsed state (if it's not already there).
				if(expIdx !== -1)
				{
					tylExpIdsByUser [tylOurUidIdxExp ].pids.splice(expIdx, 1);
				}
				if(collIdx === -1) {
					tylCollIdsByUser[tylOurUidIdxColl].pids.push(pid);
				}
			}
			else
			{
				thankyoulike.display(pid);
				// Remove this pid from the collapsed state (if it's in it) and
				// add it to the expanded state (if it's not already there).
				if(collIdx !== -1)
				{
					tylCollIdsByUser[tylOurUidIdxColl].pids.splice(collIdx, 1);
				}
				if(expIdx === -1) {
					tylExpIdsByUser [tylOurUidIdxExp ].pids.push(pid);
				}
			}
			thankyoulike.saveExpCollCookie('tylexpids' , tylExpIdsByUser );
			thankyoulike.saveExpCollCookie('tylcollids', tylCollIdsByUser);
		}
	},

	fleece: function(pid)
	{
		$('#tyl_data_'+pid+',#tyl_title_'+pid).hide();
		$('#tyl_title_collapsed_'+pid).show();
		if($('#tyl_i_expcol_'+pid).attr('src'))
		{
			$('#tyl_i_expcol_'+pid).attr('src', $('#tyl_i_expcol_'+pid).attr('src').replace("collapse.png", "collapse_collapsed.png"));
		}
		$('#tyl_i_expcol_'+pid).attr('alt', "[+]");
		$('#tyl_a_expcol_'+pid).attr('title', "[+]");
	},

	display: function(pid)
	{
		$('#tyl_data_'+pid+',#tyl_title_'+pid).show();
		$('#tyl_title_collapsed_'+pid).hide();
		if($('#tyl_i_expcol_'+pid).attr('src'))
		{
			$('#tyl_i_expcol_'+pid).attr('src', $('#tyl_i_expcol_'+pid).attr('src').replace("collapse_collapsed.png", "collapse.png"));
		}
		$('#tyl_i_expcol_'+pid).attr('alt', "[-]");
		$('#tyl_a_expcol_'+pid).attr('title', "[-]");
	},
	
	add: function(pid)
	{
		if(use_xmlhttprequest == 1 && tylEnabled == 1)
		{
			if(tylUser == 0)
			{
				return true;
			}
			$.ajax('thankyoulike.php?ajax=1&action=add&pid='+pid+'&my_post_key='+my_post_key,
			{
				type: 'post',
				success: function (data)
				{
					thankyoulike.addDone(data, pid);	
				}
			});
			document.body.style.cursor = 'wait';
			return false;
		}
		else
		{
			return true;
		}
	},
	
	addDone: function(data, pid)
	{
		if(typeof data === 'string')
		{
			var result = $.parseJSON(data);
			if(result)
			{
				if(tylDisplayGrowl == 1)
				{
					$.jGrowl(result, {theme:'jgrowl_error', group:'tyl_jgrowl'});
				}
				else
				{
					alert(result);
				}
			}
			document.body.style.cursor = 'default';
		}
		else
		{
			if (data.errors)
			{
				$.jGrowl(data.errors.join(' '), {theme:'jgrowl_error', group:'tyl_jgrowl'});
			}
			else
			{
				if(tylDisplayGrowl == 1)
				{
					var msg = tylSend;
					var options = {theme:'jgrowl_success', group:'tyl_jgrowl'};
					if (data.tylMsgNumLeft)
					{
						msg += "<br />\n<br />\n" + data.tylMsgNumLeft;
						if (data.tylMsgLife)
						{
							options.life = data.tylMsgLife;
						}
					}
					$.jGrowl(msg, options);
				}
				$("#tyl_"+pid).html(data.tylData);
				$("#tyl_"+pid).css('display', "");
				$("#tyl_btn_"+pid).before(data.tylButton).remove();
				if (tylAddDoneHooks)
				{
					for (var i = 0; i < tylAddDoneHooks.length; i++)
					{
						tylAddDoneHooks[i](data, pid);
					}
				}
			}
		}
		document.body.style.cursor = 'default';
	},
	
	del: function(pid)
	{
		if(use_xmlhttprequest == 1 && tylEnabled == 1)
		{
			if(tylUser == 0)
			{
				return true;
			}
			$.ajax('thankyoulike.php?ajax=1&action=del&pid='+pid+'&my_post_key='+my_post_key,
			{
				type: 'post',
				success: function (data)
				{
					thankyoulike.delDone(data, pid);	
				}
			});
			document.body.style.cursor = 'wait';
			return false;
		}
		else
		{
			return true;
		}
	},
	
	delDone: function(data, pid)
	{
		if(typeof data === 'string')
		{
			var result = $.parseJSON(data);
			if(result){
				if(tylDisplayGrowl == 1)
				{
					$.jGrowl(result, {theme:'jgrowl_error', group:'tyl_jgrowl'});
				}
				else
				{
					alert(result);
				}
			}
			document.body.style.cursor = 'default';
		}
		else
		{
			if (data.errors)
			{
				$.jGrowl(data.errors.join(' '), {theme:'jgrowl_error', group:'tyl_jgrowl'});
			}
			else
			{
				if(tylDisplayGrowl == 1)
				{
					$.jGrowl(tylRemove, {theme:'jgrowl_success', group:'tyl_jgrowl'});
				}
				$("#tyl_"+pid).html(data.tylData);
				$("#tyl_"+pid).css('display', "");
				$("#tyl_btn_"+pid).before(data.tylButton).remove();
				if (tylDelDoneHooks)
				{
					for (var i = 0; i < tylDelDoneHooks.length; i++)
					{
						tylDelDoneHooks[i](data, pid);
					}
				}
			}
		}
		document.body.style.cursor = 'default';
	}
};

$(function(){
	thankyoulike.init();
});
