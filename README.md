The Thank You/Like System plugin for MyBB 1.8.x
===============================================

This is one of the best plugins for MyBB. It needed some love, so its current maintainers decided to adopt it to fix known bugs and make it even better. We have added many useful features, including MyAlerts support. Give it a try and let us know what you think!

For more information, see the plugin's:

- [Extend MyBB page](http://community.mybb.com/mods.php?action=view&pid=360).
- [MyBB Community Forums topic](http://community.mybb.com/thread-169382.html).

Installing
----------

1. Download an archive of the plugin's files from its Extend MyBB page linked to above.
2. Extract the files in that archive to a temporary location, and then copy them into the root of your MyBB installation. That is to say that "tylsearch.php" should be copied to your MyBB root, "jscripts/thankyoulike.js" should be copied to your MyBB root's "jscripts/" directory, etc.
3. In a web browser, open the "Plugins" module in the Admin Control Panel (ACP) of your MyBB installation. You should see "Thank You/Like System" under "Inactive Plugins".
4. Click "Install & Activate" next to it. You should then see the plugin listed under "Active Plugins" on the reloaded page.
5. Click "Configure Settings" below the plugin and set up the plugin to your preferences.
6. If you want to, you can also edit the plugin's stylesheet ("thankyoulike.css", accessible via the ACP under Templates & Style -> Themes) and its templates (under "Thank You/Like Templates", accessible via the ACP under Templates & Style -> Templates).

Upgrading
---------

If you are upgrading to version 2.4.0 or above, then follow these steps:

1. Click the "Deactivate" button beside the Thank You/Like System plugin in the ACP "Plugins" module.
2. Copy the new version's files, overwriting the old ones (per steps #1 and #2 of Installing above).
3. Activate the plugin again via the ACP. This will upgrade the plugin, retaining all data, including received and given thanks/likes, per-user counts of thanks/likes, overall thanks/likes statistics, the plugin's settings including per-usergroup settings, any changes made to the plugin's stylesheet, and any changes made to the plugin's templates.
4. If you had modified any of the plugin's templates, then, as when upgrading MyBB core, go to "Find Updated Templates" in the ACP to see whether you need to perform any updates.
5. If you had modified the plugin's stylesheet (thankyoulike.css) for any themes, then you might need to apply any changes to this file that came with the new version of the plugin. You can check whether there are any changes in the new Master version of thankyoulike.css that need to be applied by clicking "View the Master theme's thankyoulike.css" under the plugin's entry in the ACP "Plugins" module and comparing it with your existing thankyoulike.css theme files (via the ACP under Templates & Style -> Themes).

If you are upgrading to version 2.3.0 or below, then:

If the updated version of the plugin does not contain new settings or changes to its templates or stylesheet, then:

1. Click the "Deactivate" button beside the Thank You/Like System plugin in the ACP "Plugins" module.
2. Copy the new version's files, overwriting the old ones (per steps #1 and #2 of Installing above).
3. Activate the plugin again via the ACP. All data will be retained, including received and given thanks/likes, per-user counts of thanks/likes, overall thanks/likes statistics, the plugin's settings including per-usergroup settings, any changes made to the plugin's stylesheet, and any changes made to the plugin's templates.

If the updated version of the plugin <strong>does</strong> contain new settings or changes to its templates or stylesheet, then:

1. Make a note of your ACP settings for this plugin including per-usergroup settings (applicable when the plugin's "Usergroup-based thanks/likes limits" setting is enabled), any changes you have made to the plugin's stylesheet file ("thankyoulike.css", accessible via the ACP under Templates & Style -> Themes) and any changes you have to to the plugin's templates (under "Thank You/Like Templates", accessible via the ACP under Templates & Style -> Templates).
2. Click the "Uninstall" button beside the Thank You/Like System plugin in the ACP "Plugins" module and choose "NO" when asked whether to drop plugin data from the database.
3. Copy the new version's files, overwriting the old ones (per steps #1 and #2 of Installing above).
4. Install and activate the plugin again via the ACP.
5. Re-enter your settings for the plugin including any per-usergroup settings and any changes you had made to the plugin's stylesheet and templates (this is why it is important to have made a note of them before uninstalling the plugin).

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

Click the "Uninstall" button beside the Thank You/Like System plugin in the "Plugins" module of the ACP and choose "YES" to drop all plugin data from database. This cannot be reverted. <strong>All</strong> data will be lost, including received and given thanks/likes, per-user counts of thanks/likes, overall thanks/likes statistics, plugin settings including per-usergroup settings, and any changes made to the plugin's stylesheet and templates.

<strong>How do I change the icons of the buttons to add and remove thanks/likes?</strong>

Replace the files tyl_add.png and tyl_del.png in the images/thankyoulike directory.
