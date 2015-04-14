BP XProfile WordPress User Sync
===============================

The *BP XProfile WordPress User Sync* plugin is useful when you have a BuddyPress network in which you want users to enter values for *First Name* and *Last Name* rather than rely on the more freeform default *Name* field that BuddyPress provides.

The plugin replaces the default BuddyPress XProfile *Name* field with two fields called (surprisingly) *First Name* and *Last Name*. These field values are kept in sync with the corresponding WordPress user profile fields as well as the BuddyPress XProfile *Name* field itself.

It has further compatibility with WP FB AutoConnect Premium logins, so that - like BuddyPress - a WordPress user's nickname and display name are also updated when their XProfile is updated.

If you're a developer, I'd welcome your contributions. If not, it's probably better to install this plugin from the [WordPress Plugin Directory](http://wordpress.org/plugins/bp-xprofile-wp-user-sync/).

## Installation ##

### GitHub ###

There are two ways to install from GitHub:

#### ZIP Download ####

If you have downloaded *BP XProfile WordPress User Sync* as a ZIP file from the GitHub repository, do the following to install and activate the plugin and theme:

1. Unzip the .zip file and, if needed, rename the enclosing folder so that the plugin's files are located directly inside `/wp-content/plugins/bp-xprofile-wp-user-sync`
2. Activate the plugin on the main BuddyPress site
3. You are done!

#### git clone ####

If you have cloned the code from GitHub, it is assumed that you know what you're doing.

## Changelogs ##

### 0.5.3 ###

Respect existing excluded fields during xProfile query process

### 0.5.2 ###

Pre-filter profile query when BP is sufficiently recent

### 0.5.1 ###

Fix translation (props flegmatiq)

### 0.5 ###

* Reconnect field data to field definitions when plugin is reactivated
* Hides default name field from Profile Edit admin screen in BP 2.1+

### 0.4 ###

Tested compatibility with WP 3.8

### 0.3 ###

Compatibility with 'WP FB AutoConnect Premium' and 'CiviCRM WordPress Profile Sync' plugins

### 0.2 ###

Initial release

### 0.1 ###

Initial commit
