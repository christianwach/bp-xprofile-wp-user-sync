<?php /*
================================================================================
BP XProfile WordPress User Sync Uninstaller
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====


--------------------------------------------------------------------------------
*/



// kick out if uninstall not called from WordPress
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit(); }



// delete options
delete_site_option( 'bp_xp_wp_sync_options' );
delete_site_option( 'bp_xp_wp_sync_options_store' );
delete_site_option( 'bp_xp_wp_sync_installed' );


