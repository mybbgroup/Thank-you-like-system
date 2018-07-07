The Thank You/Like System plugin for MyBB 1.8.x
===============================================

This is one of the best plugins for MyBB. It needed some love, so its current maintainers decided to adopt it to fix known bugs and make it even better. We have added many useful features, including MyAlerts support. Give it a try and let us know what you think!

For more information, see the plugin's:

- [Extend MyBB page](http://community.mybb.com/mods.php?action=view&pid=360).
- [MyBB Community Forums topic](http://community.mybb.com/thread-169382.html).

Credits
-------

This plugin was originally created by <strong>-G33K-</strong> and modified for MyBB 1.8.x by <strong>ATofighi</strong> (the JavaScript part). It is currently maintained by <strong>Eldenroot</strong>, <strong>SvePu</strong>, <strong>Whiteneo</strong>, and <strong>Laird</strong>!

Full credit goes to the original author, <strong>-G33K-</strong>. Without you, this plugin would not have been possible. :)

Contributing
------------

Feel free to contribute, report a bug, or tell us your ideas!

FAQ
---

<strong>How do I completely uninstall the Thank You/Like System plugin and remove all of its data from the database?</strong>

Click the "Uninstall" button beside the Thank You/Like System plugin in the "Plugins" module of the ACP and choose "YES" to drop all plugin data from database. This cannot be reverted. <strong>All</strong> data will be lost, including received and given thanks/likes, per-user counts of thanks/likes, overall thanks/likes statistics, plugin settings including per-usergroup settings, and any changes made to the plugin's CSS file.

<strong>How do I update the plugin to a newer version without losing data?</strong>

If the updated version of the plugin does not contain new settings, then:

1. Click the "Deactivate" button beside the Thank You/Like System plugin in the ACP "Plugins" module.
2. Copy the new version's files, overwriting the old ones.
3. Activate the plugin again via the ACP. All data will be retained: this includes received and given thanks/likes, per-user counts of thanks/likes, overall thanks/likes statistics, the plugin's settings including per-usergroup settings, and any changes made to the plugin's CSS file.

If the updated version of the plugin <strong>does</strong> contain new settings, then:

1. Make a note of your ACP settings for this plugin including per-usergroup settings (applicable when the plugin's "Usergroup-based thanks/likes limits" setting is enabled) and any changes you have made to the plugin's CSS file ("thankyoulike.css", accessible via the ACP under Templates & Style -> Themes).
2. Click the "Uninstall" button beside the Thank You/Like System plugin in the ACP "Plugins" module and choose "NO" when asked whether to drop plugin data from the database.
3. Copy the new version's files, overwriting the old ones.
4. Install and activate the plugin again via the ACP.
5. Reenter your settings for the plugin including any per-usergroup settings and any changes you had made to the plugin's CSS file (this is why it is important to have made a note of them before uninstalling the plugin).

<strong>How do I change the icons of the buttons to add and remove thanks/likes?</strong>

Replace the files thx_add.png and thx_del.png in the images/thankyoulike directory.
