<?php
/*
--------------------------------------------------------------------------------
Plugin Name: BP XProfile WordPress User Sync
Description: Map BuddyPress XProfile fields to WordPress User fields. <strong>Note:</strong> because there is no way to hide XProfile fields, all data associated with this plugin will be lost when it is deactivated.
Version: 0.2
Author: Christian Wach
Author URI: http://haystack.co.uk
Plugin URI: http://haystack.co.uk
--------------------------------------------------------------------------------
*/



// set our version here
define( 'BP_XPROFILE_WP_USER_SYNC_VERSION', '0.2' );




/*
--------------------------------------------------------------------------------
BpXProfileWordPressUserSync Class
--------------------------------------------------------------------------------
*/

class BpXProfileWordPressUserSync {

	/** 
	 * properties
	 */
	
	// plugin options
	public $options = array();
	
	
	
	/** 
	 * @description: initialises this object
	 * @return object
	 */
	function __construct() {
	
		// get options array, if it exists
		$this->options = get_option( 'bp_xp_wp_sync_options', array() );
		
		// add action for plugin init
		add_action( 'bp_init', array( $this, 'register_hooks' ) );

		// --<
		return $this;

	}
	
	
	
	/**
	 * @description: PHP 4 constructor
	 * @return object
	 */
	function BpXProfileWordPressUserSync() {
		
		// is this php5?
		if ( version_compare( PHP_VERSION, "5.0.0", "<" ) ) {
		
			// call php5 constructor
			$this->__construct();
			
		}
		
		// --<
		return $this;

	}
	
	
	
	//##########################################################################
	
	
	
	/**
	 * @description: insert xprofile fields for first and last name
	 * @return array
	 */
	public function activate() {
	
		// are we re-activating?
		if ( get_option( 'bp_xp_wp_sync_installed', 'false' ) === 'true' ) {
		
			// yes, kick out
			return;
			
		}
		
		
		
		// insert first_name field if it doesn't exist
		if ( !isset( $this->options[ 'first_name_field_id' ] ) ) {
		
			// set field name
			$name = __( 'First Name', 'bp-xprofile-wordpress-user-sync' );
		
			// get id of field
			$first_name_field_id = $this->_create_field( $name );
			
			// add to options
			$this->options[ 'first_name_field_id' ] = $first_name_field_id;
		
		}
		
		
		
		// insert last_name field if it doesn't exist
		if ( !isset( $this->options[ 'last_name_field_id' ] ) ) {
		
			// set field name
			$name = __( 'Last Name', 'bp-xprofile-wordpress-user-sync' );
		
			// get id of field
			$last_name_field_id = $this->_create_field( $name );
		
			// add to options
			$this->options[ 'last_name_field_id' ] = $last_name_field_id;
		
		}
		
		
		
		// save options array
		add_option( 'bp_xp_wp_sync_options', $this->options );
		
		// set installed flag - redundant, given that we can't retain data when
		// the plugin is deactivated
		add_option( 'bp_xp_wp_sync_installed', 'true' );

	}
	
	
	
	/**
	 * @description: actions to perform on plugin deactivation (NOT deletion)
	 * @return nothing
	 */
	public function deactivate() {
		
		// there seems to be no way to hide the xprofile fields once they have
		// been created, so we're left with no option but to lose the data when
		// we deactivate the plugin :-(

		// we can't use the API because we can't set 'can_delete' in BP 1.7
		// so we bypass it and manipulate the field directly

		// delete first_name xprofile field
		$field = new BP_XProfile_Field( $this->options[ 'first_name_field_id' ] );
		$field->can_delete = 1;
		$field->delete();
	
		// delete last_name xprofile field
		$field = new BP_XProfile_Field( $this->options[ 'last_name_field_id' ] );
		$field->can_delete = 1;
		$field->delete();
	
		// now delete options
		delete_option( 'bp_xp_wp_sync_options' );
		delete_option( 'bp_xp_wp_sync_installed' );

	}
	
	
		
	/**
	 * @description: actions to perform on plugin init
	 * @return nothing
	 */
	public function register_hooks() {
	
		// exclude the default name field type on proflie edit and registration 
		// screens and exclude our fields on proflie view screen
		add_filter( 'bp_has_profile', array( $this, 'intercept_profile_query' ), 30, 2 );
		
		// populate our fields on user registration and update by admins
		add_action( 'user_register', array( $this, 'intercept_wp_user_update' ), 30, 1 );
		add_action( 'profile_update', array( $this, 'intercept_wp_user_update' ), 30, 1 );
		
		// update the default name field before xprofile_sync_wp_profile is called
		add_action( 'xprofile_updated_profile', array( $this, 'intercept_wp_profile_sync' ), 9, 3 );
		add_action( 'bp_core_signup_user', array( $this, 'intercept_wp_profile_sync' ), 9, 3 );
		add_action( 'bp_core_activated_user', array( $this, 'intercept_wp_profile_sync' ), 9, 3 );
		
	}
	
	
		
	/**
	 * @description: intercept xprofile query process and manage display of fields
	 * @param boolean $has_groups
	 * @param object $profile_template
	 * @return boolean $has_groups
	 */
	public function intercept_profile_query( $has_groups, $profile_template ) {
		
		// init args
		$args = array();

		// if on profile view screen
		if ( bp_is_user_profile() ) {
		
			// exclude our xprofile fields
			$args[ 'exclude_fields' ] = implode( ',', $this->options );
		
		}
		
		// if on profile edit screen or registration page
		if ( bp_is_user_profile_edit() OR bp_is_register_page() ) {
		
			// profile edit or registration screen
		
			// get field id from name
			$fullname_field_id = xprofile_get_field_id_from_name( bp_xprofile_fullname_field_name() );
		
			// exclude name field
			$args[ 'exclude_fields' ] = $fullname_field_id;
		
		}
		
		// do we need to recreate query?
		if ( count( $args ) > 0 ) {

			// ditch our filter so we don't create an endless loop
			remove_filter( 'bp_has_profile', array( $this, 'intercept_profile_query' ), 30 );

			// recreate profile_template
			$has_groups = bp_has_profile( $args );
	
			// add our filter again in case there are any other calls to bp_has_profile
			add_filter( 'bp_has_profile', array( $this, 'intercept_profile_query' ), 30, 2 );
			
		}
			
		// --<
		return $has_groups;
	
	}
	
	
	
	/**
	 * @description: intercept WP user registration and update process and populate our fields.
	 * However, BuddyPress updates the "Name" field before wp_insert_user or wp_update_user get
	 * called - it hooks into 'user_profile_update_errors' instead. So, there are two options:
	 * either hook into the same action or call the same function below. Until I raise this as
	 * an issue (ie, why do database operations via an action designed to collate errors) I'll
	 * temporarily call the same function.
	 * @param integer $user_id
	 * @return nothing
	 */
	public function intercept_wp_user_update( $user_id ) {

		// only map data when the site admin is adding users, not on registration.
		if ( !is_admin() ) { return false; }

		// populate the user's first and last names
		if ( bp_is_active( 'xprofile' ) ) {
			
			// get first name
			$first_name = bp_get_user_meta( $user_id, 'first_name', true );
			
			// get last name
			$last_name = bp_get_user_meta( $user_id, 'last_name', true );
			
			// if nothing set...
			if ( empty( $first_name ) AND empty( $last_name ) ) {
				
				// get nickname instead
				$nickname = bp_get_user_meta( $user_id, 'nickname', true );
				
				// does it have a space in it? (use core BP logic)
				$space = strpos( $nickname, ' ' );
				if ( false === $space ) {
					$first_name = $nickname;
					$last_name = '';
				} else {
					$first_name = substr( $nickname, 0, $space );
					$last_name = trim( substr( $nickname, $space, strlen( $nickname ) ) );
				}
				
			}
			
			// update first_name field
			xprofile_set_field_data( 
				$this->options[ 'first_name_field_id' ], 
				$user_id, 
				$first_name
			);
			
			// update last_name field
			xprofile_set_field_data( 
				$this->options[ 'last_name_field_id' ],
				$user_id,
				$last_name
			);
			
			// When XProfiles are updated, BuddyPress sets user nickname and display name 
			// so we should too...

			// construct full name
			$full_name = $first_name.' '.$last_name;
			
			// set user nickname
			bp_update_user_meta( $user_id, 'nickname', $full_name );
			
			// access db
			global $wpdb;

			// set user display name - see xprofile_sync_wp_profile()
			$wpdb->query( 
				$wpdb->prepare( 
					"UPDATE {$wpdb->users} SET display_name = %s WHERE ID = %d", 
					$full_name, 
					$user_id 
				)
			);
			
			// see notes above regarding when BuddyPress updates the "Name" field

			// update BuddyPress "Name" field directly
			xprofile_set_field_data( 
				bp_xprofile_fullname_field_name(), 
				$user_id, 
				$full_name 
			);
			
		}
		
	}



	/**
	 * @description: intercept BP core's attempt to sync to WP user profile
	 * @param integer $user_id
	 * @param array $posted_field_ids
	 * @param boolean $errors
	 * @return nothing
	 */
	public function intercept_wp_profile_sync( $user_id = 0, $posted_field_ids, $errors ) {
		
		// we're hooked in before BP core
		$bp = buddypress();
		
		if ( !empty( $bp->site_options['bp-disable-profile-sync'] ) && (int) $bp->site_options['bp-disable-profile-sync'] )
			return true;
		
		if ( empty( $user_id ) )
			$user_id = bp_loggedin_user_id();
		
		if ( empty( $user_id ) )
			return false;
		
		// get our user's first name
		$first_name = xprofile_get_field_data( 
			$this->options[ 'first_name_field_id' ], 
			$user_id
		);

		// get our user's last name
		$last_name = xprofile_get_field_data( 
			$this->options[ 'last_name_field_id' ], 
			$user_id
		);
		
		// concatenate as per BP core
		$name = $first_name.' '.$last_name;
		//print_r( array( 'name' => $name ) ); die();

		// set default name field for this user - setting it now ensures that 
		// when xprofile_sync_wp_profile() is called, BuddyPress has the correct
		// data to perform its updates with
		xprofile_set_field_data( bp_xprofile_fullname_field_name(), $user_id, $name );
		
	}



	//##########################################################################
	
	
	
	/**
	 * @description: create a field with a given name
	 * @param string $field_name
	 * @return integer $field_id on success, false on failure
	 */
	private function _create_field( $field_name ) {
	
		// common field attributes

		// default group
		$field_group_id = 1;
		
		// no parent
		$parent_id = 0;
		
		// text
		$type = 'textbox';
		
		// name from passed value
		$name = $field_name;
		
		// description
		$description = '';
		
		// required (note: super admins can always edit)
		$is_required = 1;
		
		// cannot be deleted
		$can_delete = 0;
		
		// construct data to save
		$data = compact( 
			array( 'field_group_id', 'parent_id', 'type', 'name', 'description', 'is_required', 'can_delete' ) 
		);
		
		// use bp function to get new field ID
		$field_id = xprofile_insert_field( $data );
		
		// die if unsuccessful
		if ( !is_numeric( $field_id ) ) {
			
			// construct message
			$msg = __( 
				'BP XProfile WordPress User Sync plugin: Could not create XProfile field', 
				'bp-xprofile-wordpress-user-sync'
			);
			
			// use var_dump as this seems to display in the iframe
			var_dump( $msg ); die();
			
		}
		
		// disable custom visibility
		bp_xprofile_update_field_meta( 
			$field_id, 
			'allow_custom_visibility', 
			'disabled'
		);
		
		// BuddyPress 1.7 seems to overlook our 'can_delete' setting
		$field = new BP_XProfile_Field( $field_id );
		
		// let's see if our new field is correctly set
		if ( $field->can_delete !== 0 ) {
			
			// we'll need these to manually update, because the API can't do it
			global $wpdb, $bp;
			
			// construct query
			$sql = $wpdb->prepare( 
				"UPDATE {$bp->profile->table_name_fields} SET can_delete = %d WHERE id = %d", 
				0,
				$field_id
			);
			
			// we must have one row affected
			if ( $wpdb->query( $sql ) !== 1 ) {
			
				// construct message
				$msg = __( 
					'BP XProfile WordPress User Sync plugin: Could not set "can_delete" for XProfile field', 
					'bp-xprofile-wordpress-user-sync'
				);
			
				// use var_dump as this seems to display in the iframe
				var_dump( $msg ); die();
			
			}
			
		}
		
		// --<
		return $field_id;
		
	}
	
	
	
} // class ends





// declare as global
global $bp_xprofile_wordpress_user_sync;

// init plugin
$bp_xprofile_wordpress_user_sync = new BpXProfileWordPressUserSync;

// activation
register_activation_hook( __FILE__, array( $bp_xprofile_wordpress_user_sync, 'activate' ) );

// deactivation
register_deactivation_hook( __FILE__, array( $bp_xprofile_wordpress_user_sync, 'deactivate' ) );

// uninstall will use the 'uninstall.php' method when XProfile fields can be "deactivated"
// see: http://codex.wordpress.org/Function_Reference/register_uninstall_hook





