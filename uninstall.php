<?php /*
================================================================================
BP XProfile WordPress User Sync Uninstaller Version 1.0
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====


--------------------------------------------------------------------------------
*/



// kick out if uninstall not called from WordPress
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit(); }



/** 
 * @description: remove XProfile fields
 * @return nothing
 */
function bp_xprofile_wordpress_user_sync_restore() {
	
	// get options with non-array default
	$opts = get_option( 'bp_xp_wp_sync_options', 'false' );
	
	// skip if we don't get any
	if ( is_array( $opts ) ) {
		
		// delete first_name xprofile field
		xprofile_delete_field( $opts['first_name_field_id'] );
		
		// delete last_name xprofile field
		xprofile_delete_field( $opts['last_name_field_id'] );
		
	}
	
	// remove our options
	delete_option( 'bp_xp_wp_sync_options' );
	delete_option( 'bp_xp_wp_sync_installed' );
	
}

// remove XProfile fields
bp_xprofile_wordpress_user_sync_restore();


