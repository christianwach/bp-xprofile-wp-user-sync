<?php /*
================================================================================
BP xProfile WordPress User Sync Uninstaller
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====


--------------------------------------------------------------------------------
*/



// Kick out if uninstall not called from WordPress.
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}



// Delete options.
delete_option( 'bp_xp_wp_sync_options' );
delete_option( 'bp_xp_wp_sync_options_store' );
delete_option( 'bp_xp_wp_sync_installed' );
delete_option( 'bp_xp_wp_sync_migration' );


