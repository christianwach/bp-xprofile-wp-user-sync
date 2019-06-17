BP xProfile WordPress User Sync
===============================

Please note: this is the development repository for *BP xProfile WordPress User Sync*. It can be found in [the WordPress Plugin Directory](https://wordpress.org/plugins/bp-xprofile-wp-user-sync/), which is the best place to get it from if you're not a developer.

The *BP xProfile WordPress User Sync* plugin is useful when you have a BuddyPress network in which you want users to enter values for *First Name* and *Last Name* rather than rely on the more freeform default *Name* field that BuddyPress provides.

The plugin replaces the default BuddyPress xProfile *Name* field with two fields called (surprisingly) *First Name* and *Last Name*. These field values are kept in sync with the corresponding WordPress user profile fields as well as the BuddyPress xProfile *Name* field itself.

## Installation ##

### GitHub ###

There are two ways to install from GitHub:

#### ZIP Download ####

If you have downloaded *BP xProfile WordPress User Sync* as a ZIP file from the GitHub repository, do the following to install and activate the plugin:

1. Unzip the .zip file and, if needed, rename the enclosing folder so that the plugin's files are located directly inside `/wp-content/plugins/bp-xprofile-wp-user-sync`
2. Activate the plugin on the main BuddyPress site
3. You are done!

#### git clone ####

If you have cloned the code from GitHub, it is assumed that you know what you're doing.

## Upgrades ##

When upgrading this plugin, it is *strongly recommended* that you run `git pull` to replace the plugin directory directly. This is preferable because it avoids the deactivate-activate process that happens when upgrading via the WordPress updater.
