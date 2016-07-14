=== BP XProfile WordPress User Sync ===
Contributors: needle
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8MZNB9D3PF48S
Tags: buddypress, xprofile, profile, sync
Requires at least: 3.5
Tested up to: 4.2
Stable tag: 0.6.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Replaces the default BuddyPress xProfile Name field with First Name and Last Name fields and keeps these in sync with WordPress user profile fields.



== Description ==

The BP xProfile WordPress User Sync plugin is useful when you have a BuddyPress network in which you want to make sure that users enter values for First Name and Last Name rather than rely on the more freeform default Name field that BuddyPress provides.

The plugin replaces the default BuddyPress xProfile Name field with two fields called (surprisingly) First Name and Last Name. These field values are kept in sync with the corresponding WordPress user profile fields as well as the BuddyPress xProfile Name field itself.

### Plugin Updates

**The best way to update this plugin is to replace the folder with the latest version via FTP or similar. This avoids the deactivate-reactivate process.**

### Plugin Deactivation

**Please note:** because there is no way to hide xProfile fields, all field definitions associated with this plugin are deleted when it is deactivated. The field data itself is not deleted and the plugin makes an attempt to reconnect the existing data to the new field definitions when it is reactivated. **Always back up your database before deactivating this plugin.**

### Plugin Development

This plugin is in active development. For feature requests and bug reports (or if you're a plugin author and want to contribute) please visit the plugin's [GitHub repository](https://github.com/christianwach/bp-xprofile-wp-user-sync).



== Installation ==

1. Extract the plugin archive
1. Upload plugin files to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress



== Changelog ==

= 0.6.3 =

Fix profile sync from multisite subsites

= 0.6.2 =

* More detailed plugin update warnings
* Prevent "can_delete" being incorrectly updated

= 0.6.1 =

Damage limitation

= 0.6 =

Broken release :(

= 0.5.3 =

Respect existing excluded fields during xProfile query process

= 0.5.2 =

Pre-filter profile query when BP is sufficiently recent

= 0.5.1 =

Fix translation (props flegmatiq)

= 0.5 =

* Reconnect field data to field definitions when plugin is reactivated
* Hides default name field from Profile Edit admin screen in BP 2.1+

= 0.4.5 =

Allow field substitution in registration form regardless of location

= 0.4.4 =

Update plugin compatibility flags

= 0.4.3 =

Fix warning when BP_Members_Admin object is not present

= 0.4.2 =

Profile queries fixed when editing multiple profile groups - props WIBeditor

= 0.4.1 =

Profile queries fixed when there are multiple profile groups - props WIBeditor

= 0.4 =

Tested compatibility with WP 3.8

= 0.3 =

Compatibility with 'WP FB AutoConnect Premium' and 'CiviCRM WordPress Profile Sync' plugins

= 0.2 =

Initial release

= 0.1 =

Initial commit



== Upgrade Notice ==

= 0.6.3 =

When upgrading this plugin, it's best to simply replace the plugin directory directly. This is preferable to using the WordPress updater because it avoids the deactivate-activate process.
