=== BP XProfile WordPress User Sync ===
Contributors: needle
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8MZNB9D3PF48S
Tags: buddypress, xprofile, profile, sync
Requires at least: 3.5
Tested up to: 4.0
Stable tag: 0.5.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

BP XProfile WordPress User Sync replaces the default BuddyPress XProfile 'Name' field with 'First Name' and 'Last Name' fields and keeps these in sync with the corresponding WordPress user profile fields.



== Description ==

The BP XProfile WordPress User Sync plugin is useful when you have a BuddyPress network in which you want to make sure that users enter values for First Name and Last Name rather than rely on the more freeform default Name field that BuddyPress provides.

The plugin replaces the default BuddyPress XProfile Name field with two fields called (surprisingly) First Name and Last Name. These field values are kept in sync with the corresponding WordPress user profile fields as well as the BuddyPress XProfile Name field itself.

**Please note:** because there is no way to hide XProfile fields, all field definitions associated with this plugin are deleted when it is deactivated. The field data itself is not deleted and the plugin makes an attempt to reconnect the existing data to the new field definitions when it is reactivated. Always back up your database before deactivating this plugin.



== Installation ==

1. Extract the plugin archive
1. Upload plugin files to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress



== Changelog ==

= 0.5.3 =

* Respect existing excluded fields during xProfile query process

= 0.5.2 =

* Pre-filter profile query when BP is sufficiently recent

= 0.5.1 =

* Fix translation (props flegmatiq)

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
