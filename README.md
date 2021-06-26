# The Thank You/Like System plugin for MyBB 1.8.x

This is one of the best plugins for MyBB. It needed some love, so its current maintainers decided to adopt it to fix known bugs and make it even better. We have added many useful features, including MyAlerts support. Give it a try and let us know what you think!

For more information, see the plugin's:

- [Extend MyBB page](http://community.mybb.com/mods.php?action=view&pid=360).
- [MyBB Community Forums topic](http://community.mybb.com/thread-169382.html).

## Installing

1. *Download*.

   Download an archive of the plugin's files from its Extend MyBB page linked to above.

2. *Copy files*.

   Extract the files in that archive to a temporary location, and then copy everything in the "upload" directory into the root of your MyBB installation (note that in earlier versions, there is no "upload" directory, and the files and directories to be uploaded are in the root extracted directory). That is to say that "tylsearch.php" should be copied to your MyBB root, "jscripts/thankyoulike.js" should be copied to your MyBB root's "jscripts/" directory, etc.

3. *Install via the ACP*.

   In a web browser, open the "Plugins" module in the Admin Control Panel (ACP) of your MyBB installation. You should see "Thank You/Like System" under "Inactive Plugins". Click "Install & Activate" next to it. You should then see the plugin listed under "Active Plugins" on the reloaded page.

4. *Configure settings*.

   Click "Configure Settings" below the plugin and set up the plugin to your preferences.

5. *Configure style and templates*.

   If you want to, you can also edit the plugin's stylesheet ("thankyoulike.css", accessible via the ACP under Templates & Style -> Themes) and its templates (under "Thank You/Like Templates", accessible via the ACP under Templates & Style -> Templates).

## Upgrading

### Upgrading to version 3.0.0 or above

N.B. If you are upgrading **from** version 2.3.0 or earlier *and* MyAlerts is integrated *and* you don't want to lose any existing thanks/likes alerts, then see "Workaround to avoid loss of alerts" below.

1. *Deactivate*.

   Click the "Deactivate" button beside the Thank You/Like System plugin in the ACP "Plugins" module.

2. *Download, extract, and copy files*.

   Copy the new version's files, overwriting the old ones (per steps #1 and #2 of "Installing" above).

3. *Reactivate*.

   Activate the plugin again via the ACP. This will upgrade the plugin, retaining all data, including received and given thanks/likes, per-user counts of thanks/likes, overall thanks/likes statistics, the plugin's settings including per-usergroup settings, any changes made to the plugin's stylesheet, any changes made to the plugin's templates, and, if integrated with MyAlerts, all alerts of thanks/likes.

4. *Update templates*.

   If you had modified any of the plugin's templates, then, as when upgrading MyBB core, go to "Find Updated Templates" in the ACP to see whether you need to perform any updates.

5. *Update the stylesheet*.

   If you had modified the plugin's stylesheet (thankyoulike.css) for any themes, then you might need to apply any changes to this file that came with the new version of the plugin. You can check whether there are any changes in the new Master version of thankyoulike.css that need to be applied by clicking "View the Master theme's thankyoulike.css" under the plugin's entry in the ACP "Plugins" module and comparing it with your existing thankyoulike.css theme files (via the ACP under Templates & Style -> Themes).

#### Workaround to avoid loss of alerts

In version 2.3.0 and earlier, deactivating this plugin causes the thanks/likes MyAlerts alert type to be deleted from the database, thus orphaning any existing alerts of that type. When reactivating the plugin, including during an upgrade, the thanks/likes MyAlerts alert type is recreated with a different database ID, so the orphaned alerts remain invisible to your users.

To work around this, assuming you have direct access to the database, simply take the following action:

Prior to the first step of any upgrade procedure (deactivation/uninstallation), run this database query, taking note of the returned id (where 'mybb_' should, if necessary, be replaced with your table prefix):

```sql
SELECT id FROM mybb_alert_types WHERE code='tyl';
```

After (upgrading and) reactivating the plugin, run this database query, replacing "x" with the id returned by the previous query (and, again, replacing 'mybb_' if necessary):

```sql
UPDATE mybb_alert_types SET id=x WHERE code='tyl';
```

Then reload the 'mybbstuff_myalerts_alert_types' cache in the ACP under Tools & Maintenance -> Cache Manager.

### Upgrading to version 2.3.0 or below

#### When the updated version of the plugin does not contain new settings or changes to its templates or stylesheet

N.B. If MyAlerts is integrated *and* you don't want to lose any existing thanks/likes alerts, then see "Workaround to avoid loss of alerts" above.

1. *Deactivate*.

   Click the "Deactivate" button beside the Thank You/Like System plugin in the ACP "Plugins" module.

2. *Download, extract, and copy files*.

   Copy the new version's files, overwriting the old ones (per steps #1 and #2 of "Installing" above).

3. *Reactivate*.

   Activate the plugin again via the ACP. All data except (unless you use the above workaround) for thanks/likes alerts will be retained, including received and given thanks/likes, per-user counts of thanks/likes, overall thanks/likes statistics, the plugin's settings including per-usergroup settings, any changes made to the plugin's stylesheet, and any changes made to the plugin's templates.

#### When the updated version of the plugin **does** contain new settings or changes to its templates or stylesheet

N.B. If MyAlerts is integrated *and* you don't want to lose any existing thanks/likes alerts, then see "Workaround to avoid loss of alerts" above.

1. *Record changes*.

   Make a note of your ACP settings for this plugin including per-usergroup settings (applicable when the plugin's "Usergroup-based thanks/likes limits" setting is enabled), any changes you have made to the plugin's stylesheet file ("thankyoulike.css", accessible via the ACP under Templates & Style -> Themes) and any changes you have made to the plugin's templates (under "Thank You/Like Templates", accessible via the ACP under Templates & Style -> Templates).

2. *Uninstall*.

   Click the "Uninstall" button beside the Thank You/Like System plugin in the ACP "Plugins" module and choose "NO" when asked whether to drop plugin data from the database.

3. *Download, extract, and copy files*.

   Copy the new version's files, overwriting the old ones (per steps #1 and #2 of "Installing" above).

4. *Reinstall and reactivate*.

   Install and activate the plugin again via the ACP.

5. *Recreate changes*.

   Re-enter your settings for the plugin including any per-usergroup settings and any changes you had made to the plugin's stylesheet and templates (this is why it is important to have made a note of them before uninstalling the plugin).

## Migrating from other thanks/likes plugins

A conversion script, "tyl-converter.php", is included in the "scripts" directory for this purpose. For directions on its use, see the comments at the top of that file.

## FAQ

**How do I completely uninstall the Thank You/Like System plugin and remove all of its data from the database?**

Click the "Uninstall" button beside the Thank You/Like System plugin in the "Plugins" module of the ACP and choose "YES" to drop all plugin data from database. This cannot be reverted. **All** data will be lost, including received and given thanks/likes, per-user counts of thanks/likes, overall thanks/likes statistics, plugin settings including per-usergroup settings, any changes made to the plugin's stylesheet and templates, and, if integrated with MyAlerts, all alerts of thanks/likes.

**How do I change the icons of the buttons to add and remove thanks/likes?**

Replace the files tyl_add.png and tyl_del.png in the images/thankyoulike directory.

## Contributing

Feel free to contribute, report a bug, or tell us your ideas!

## Credits

This plugin was originally created by **-G33K-** and modified for MyBB 1.8.x by **ATofighi** (the JavaScript part). It is currently maintained by **Eldenroot**, **SvePu**, **Whiteneo**, **Laird** and **effone**!

Full credit goes to the original author, **-G33K-**. Without you, this plugin would not have been possible. :)
