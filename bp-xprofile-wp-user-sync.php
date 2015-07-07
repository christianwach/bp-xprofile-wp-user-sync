<?php
/*
--------------------------------------------------------------------------------
Plugin Name: BP XProfile WordPress User Sync
Plugin URI: https://github.com/christianwach/bp-xprofile-wp-user-sync
Description: Map BuddyPress xProfile fields to WordPress User fields. <strong>Note:</strong> because there is no way to hide xProfile fields, all field definitions are deleted when it is deactivated. The plugin tries to reconnect on reactivation, but always backup before deactivating. <strong>The best way to update this plugin is to replace the folder with the latest version via FTP or similar. This avoids the deactivate-reactivate process.</strong>
Author: Christian Wach
Version: 0.6.3
Author URI: http://haystack.co.uk
Text Domain: bp-xprofile-wp-user-sync
Domain Path: /languages
--------------------------------------------------------------------------------
*/



// set our version here
define( 'BP_XPROFILE_WP_USER_SYNC_VERSION', '0.6.3' );

// store reference to this file
if ( ! defined( 'BP_XPROFILE_WP_USER_SYNC_FILE' ) ) {
	define( 'BP_XPROFILE_WP_USER_SYNC_FILE', __FILE__ );
}

// store URL to this plugin's directory
if ( ! defined( 'BP_XPROFILE_WP_USER_SYNC_URL' ) ) {
	define( 'BP_XPROFILE_WP_USER_SYNC_URL', plugin_dir_url( BP_XPROFILE_WP_USER_SYNC_FILE ) );
}
// store PATH to this plugin's directory
if ( ! defined( 'BP_XPROFILE_WP_USER_SYNC_PATH' ) ) {
	define( 'BP_XPROFILE_WP_USER_SYNC_PATH', plugin_dir_path( BP_XPROFILE_WP_USER_SYNC_FILE ) );
}



/*
--------------------------------------------------------------------------------
BpXProfileWordPressUserSync Class
--------------------------------------------------------------------------------
*/

class BpXProfileWordPressUserSync {

	/**
	 * Properties
	 */

	// plugin options
	public $options = array();



	/**
	 * Initialises this object
	 *
	 * @return object
	 */
	function __construct() {

		// get options array, if it exists
		$this->options = get_option( 'bp_xp_wp_sync_options', array() );

		// add action for plugin init
		add_action( 'bp_init', array( $this, 'register_hooks' ) );

		// use translation
		add_action( 'plugins_loaded', array( $this, 'translation' ) );

		// --<
		return $this;

	}



	/**
	 * Loads translation, if present
	 *
	 * @return void
	 */
	function translation() {

		// only use, if we have it...
		if ( function_exists( 'load_plugin_textdomain' ) ) {

			// not used, as there are no translations as yet
			load_plugin_textdomain(

				// unique name
				'bp-xprofile-wp-user-sync',

				// deprecated argument
				false,

				// relative path to directory containing translation files
				dirname( plugin_basename( BP_XPROFILE_WP_USER_SYNC_FILE ) ) . '/languages/'

			);

		}

	}



	//##########################################################################



	/**
	 * Insert xProfile fields for "First Name" and "Last Name"
	 *
	 * @return void
	 */
	public function activate() {

		// are we re-activating?
		$reactivating = ( get_option( 'bp_xp_wp_sync_installed', 'false' ) === 'true' ) ? true : false;

		// before we create our fields, test if we're reactivating...
		if ( $reactivating ) {

			// yes, get existing field data
			$existing_fields = get_option( 'bp_xp_wp_sync_options_store', array() );

			// if we're reactivating after an upgrade from a version that does not
			// have the code to salvage the connection between fields and data...
			if ( empty( $existing_fields ) ) {

				// hmm...

			} else {

				// first name field
				$existing_first_name_field_id = $existing_fields['first_name_field_id'];
				$existing_first_name_field_name = $existing_fields['first_name_field_name'];
				$existing_first_name_field_desc = $existing_fields['first_name_field_desc'];

				// first name field
				$existing_last_name_field_id = $existing_fields['last_name_field_id'];
				$existing_last_name_field_name = $existing_fields['last_name_field_name'];
				$existing_last_name_field_desc = $existing_fields['last_name_field_desc'];

			}

		}



		// "First Name" field

		// set field name
		$name = __( 'First Name', 'bp-xprofile-wp-user-sync' );
		if ( isset( $existing_first_name_field_name ) ) {
			$name = $existing_first_name_field_name;
		}

		// set field description
		$description = '';
		if ( isset( $existing_first_name_field_desc ) ) {
			$description = $existing_first_name_field_desc;
		}

		// get id of field
		$first_name_field_id = $this->_create_field( $name, $description );



		// "Last Name" field

		// set field name
		$name = __( 'Last Name', 'bp-xprofile-wp-user-sync' );
		if ( isset( $existing_last_name_field_name ) ) {
			$name = $existing_last_name_field_name;
		}

		// set field description
		$description = '';
		if ( isset( $existing_last_name_field_desc ) ) {
			$description = $existing_last_name_field_desc;
		}

		// get id of field
		$last_name_field_id = $this->_create_field( $name, $description );



		// are we re-activating?
		if ( $reactivating ) {

			// reconnect data to fields
			$this->_reconnect_field( $existing_first_name_field_id, $first_name_field_id );
			$this->_reconnect_field( $existing_last_name_field_id, $last_name_field_id );

			// delete storage array
			delete_option( 'bp_xp_wp_sync_options_store' );

			// add to options
			$this->options['first_name_field_id'] = $existing_first_name_field_id;
			$this->options['last_name_field_id'] = $existing_last_name_field_id;

			// update options array
			update_option( 'bp_xp_wp_sync_options', $this->options );

		} else {

			// add to options
			$this->options['first_name_field_id'] = $first_name_field_id;
			$this->options['last_name_field_id'] = $last_name_field_id;

			// save options array
			add_option( 'bp_xp_wp_sync_options', $this->options );

			/**
			 * Set installed flag
			 *
			 * We can't retain fields when the plugin is deactivated, but the field
			 * data does survive and we'll try and reconnect it when the plugin is
			 * reactivated.
			 */
			add_option( 'bp_xp_wp_sync_installed', 'true' );

		}

	}



	/**
	 * Actions to perform on plugin deactivation (NOT deletion)
	 *
	 * @return void
	 */
	public function deactivate() {

		/**
		 * There seems to be no way to hide the xProfile fields once they have
		 * been created, so we're left with no option but to lose the data when
		 * we deactivate the plugin :-(
		 *
		 * The 'bp_xp_wp_sync_options_store' option is an attempt at a workaround
		 * but will not work if an older version of the plugin is deactivated and
		 * a newer one is then activated, since no bridging data will have been
		 * saved. Have updated the readme to flag this.
		 *
		 * Also, we can't use BP's API because we can't set 'can_delete' in BP 1.7
		 * so we bypass it and manipulate the field directly
		 */

		// init storage array
		$options = array();

		// get first_name xProfile field
		$field = new BP_XProfile_Field( $this->options['first_name_field_id'] );

		// store data about first name field
		$options['first_name_field_id'] = $field->id;
		$options['first_name_field_name'] = $field->name;
		$options['first_name_field_desc'] = $field->description;

		// delete first_name xProfile field
		$field->can_delete = 1;
		$field->delete();

		// get last_name xProfile field
		$field = new BP_XProfile_Field( $this->options['last_name_field_id'] );

		// store data about first name field
		$options['last_name_field_id'] = $field->id;
		$options['last_name_field_name'] = $field->name;
		$options['last_name_field_desc'] = $field->description;

		// delete last_name xProfile field
		$field->can_delete = 1;
		$field->delete();

		// save our storage array
		add_option( 'bp_xp_wp_sync_options_store', $options );

		// delete our options array
		delete_option( 'bp_xp_wp_sync_options' );

	}



	/**
	 * Actions to perform on plugin init
	 *
	 * @return void
	 */
	public function register_hooks() {

		// do we have a version of BuddyPress capable of pre-filtering?
		if ( function_exists( 'bp_parse_args' ) ) {

			// use bp_parse_args post-parse filter (available since BP 2.0)
			add_filter( 'bp_after_has_profile_parse_args', array( $this, 'intercept_profile_query_args' ), 30, 1 );

		} else {

			// exclude the default name field type on profile edit and registration
			// screens and exclude our fields on profile view screen
			add_filter( 'bp_has_profile', array( $this, 'intercept_profile_query' ), 30, 2 );

		}

		// exclude the default name field type on profile fields admin screen (available since BP 2.1)
		add_filter( 'bp_xprofile_get_groups', array( $this, 'intercept_profile_fields_query' ), 30, 2 );

		// populate our fields on user registration and update by admins
		add_action( 'user_register', array( $this, 'intercept_wp_user_update' ), 30, 1 );
		add_action( 'profile_update', array( $this, 'intercept_wp_user_update' ), 30, 1 );

		// update the default name field before xprofile_sync_wp_profile is called
		add_action( 'xprofile_updated_profile', array( $this, 'intercept_wp_profile_sync' ), 9, 3 );
		add_action( 'bp_core_signup_user', array( $this, 'intercept_wp_profile_sync' ), 9, 3 );
		add_action( 'bp_core_activated_user', array( $this, 'intercept_wp_profile_sync' ), 9, 3 );

		// compatibility with "WP FB AutoConnect Premium"
		add_filter( 'wpfb_xprofile_fields_received', array( $this, 'intercept_wp_fb_profile_sync' ), 10, 2 );

	}



	/**
	 * Intercept xProfile query process and manage display of fields
	 *
	 * @param array $args The existing arguments used to query for fields
	 * @return array $args The modified arguments used to query for fields
	 */
	public function intercept_profile_query_args( $args ) {

		// if on profile view screen
		if ( bp_is_user_profile() AND ! bp_is_user_profile_edit() ) {

			// get fields to exclude on profile view screen
			$exclude_fields = $this->_get_excluded_fields();

			// merge with existing if populated
			$args['exclude_fields'] = $this->_merge_excluded_fields( $args['exclude_fields'], $exclude_fields );

		}

		// if on profile edit screen
		if ( bp_is_user_profile_edit() ) {

			// exclude name field (bp_xprofile_fullname_field_id is available since BP 2.0)
			$exclude_fields = bp_xprofile_fullname_field_id();

			// merge with existing if populated
			$args['exclude_fields'] = $this->_merge_excluded_fields( $args['exclude_fields'], $exclude_fields );

		}

		/**
		 * Apply to registration form whichever page it is displayed on, whilst avoiding
		 * splitting the Name field into First Name and Last Name fields in the profile
		 * display loop of the user. Note that we cannot determine if we are in the loop
		 * prior to the query, so we test for an empty user ID instead.
		 */
		if (
			! is_user_logged_in() // user must be logged out
			AND
			( ! bp_is_user_profile() OR ( bp_is_user_profile() AND empty( $args['user_id'] ) ) )
		) {

			// query only group 1
			$args['profile_group_id'] = 1;

			// exclude name field (bp_xprofile_fullname_field_id is available since BP 2.0)
			$exclude_fields = bp_xprofile_fullname_field_id();

			// merge with existing if populated
			$args['exclude_fields'] = $this->_merge_excluded_fields( $args['exclude_fields'], $exclude_fields );

		}

		// --<
		return $args;

	}



	/**
	 * Intercept xProfile query process and manage display of fields
	 *
	 * @param boolean $has_groups
	 * @param object $profile_template
	 * @return boolean $has_groups
	 */
	public function intercept_profile_query( $has_groups, $profile_template ) {

		// init args
		$args = array();

		// if on profile view screen
		if ( bp_is_user_profile() AND ! bp_is_user_profile_edit() ) {

			// get fields to exclude on profile view screen
			$args['exclude_fields'] = $this->_get_excluded_fields();

		}

		// if on profile edit screen
		if ( bp_is_user_profile_edit() ) {

			// check which profile group is being queried
			if (

				isset( $profile_template->groups ) AND
				is_array( $profile_template->groups ) AND
				count( $profile_template->groups ) > 0

			) {

				// don't want to pop, so loop through them
				foreach( $profile_template->groups AS $group ) {

					// is this the base group?
					if ( $group->id == 1 ) {

						// query only group 1
						$args['profile_group_id'] = 1;

						// get field id from name
						$fullname_field_id = xprofile_get_field_id_from_name( bp_xprofile_fullname_field_name() );

						// exclude name field
						$args['exclude_fields'] = $fullname_field_id;

					}

					// only the first
					break;

				}

			}

		}

		// determine if we are currently in the profile display loop
		$in_loop = false;
		if ( isset( $profile_template->in_the_loop ) AND $profile_template->in_the_loop === true ) {
			$in_loop = true;
		}

		/**
		 * Apply to registration form whichever page it is displayed on, whilst avoiding
		 * splitting the Name field into First Name and Last Name fields in the profile
		 * display loop of the user. Props https://github.com/sbrajesh
		 */
		if ( ! is_user_logged_in() AND ( ! bp_is_user_profile() OR bp_is_user_profile() AND ! $in_loop ) ) {

			// query only group 1
			$args['profile_group_id'] = 1;

			// get field id from name
			$fullname_field_id = xprofile_get_field_id_from_name( bp_xprofile_fullname_field_name() );

			// exclude name field
			$args['exclude_fields'] = $fullname_field_id;

		}

		// test for new BP xProfile admin screen

		// get BuddyPress instance
		$bp = buddypress();

		// test for new BP_Members_Admin object
		if( isset( $bp->profile->admin ) ) {

			// check which profile group is being queried
			if (

				isset( $profile_template->groups ) AND
				is_array( $profile_template->groups ) AND
				count( $profile_template->groups ) > 0

			) {

				// don't want to pop, so loop through them
				foreach( $profile_template->groups AS $group ) {

					// is this the base group?
					if ( $group->id == 1 ) {

						/**
						 * BP_XProfile_User_Admin queries prior to the loop, so
						 * do we have the fields populated?
						 * see BP_XProfile_User_Admin->register_metaboxes()
						 */
						if ( isset( $group->fields ) AND is_array( $group->fields ) ) {

							// get user ID
							$user_id = isset( $_GET['user_id'] ) ? intval( $_GET['user_id'] ) : 0 ;

							// only edit other users profiles
							if ( $user_id AND get_current_user_id() != $user_id ) {

								// query only group 1
								$args['profile_group_id'] = 1;

								// query only for this user
								$args['user_id'] = $user_id;

								// get field id from name
								$fullname_field_id = xprofile_get_field_id_from_name( bp_xprofile_fullname_field_name() );

								// exclude name field
								$args['exclude_fields'] = $fullname_field_id;

							}

						}

					}

					// only the first
					break;

				}

			}

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
	 * Intercept xprofile query process and manage display of fields
	 *
	 * @param array $groups The xProfile groups
	 * @param array $args The arguments
	 * @return void
	 */
	public function intercept_profile_fields_query( $groups, $args ) {

		// bail if not in admin
		if ( ! is_admin() ) return $groups;

		// exclude name field
		$args['exclude_fields'] = bp_xprofile_fullname_field_id();

		// re-query the groups
		$groups = BP_XProfile_Group::get( $args );

		// --<
		return $groups;

	}



	/**
	 * Intercept WP user registration and update process and populate our fields.
	 *
	 * However, BuddyPress updates the "Name" field before wp_insert_user or wp_update_user get
	 * called - it hooks into 'user_profile_update_errors' instead. So, there are two options:
	 * either hook into the same action or call the same function below. Until I raise this as
	 * an issue (ie, why do database operations via an action designed to collate errors) I'll
	 * temporarily call the same function.
	 *
	 * @param integer $user_id
	 * @return void
	 */
	public function intercept_wp_user_update( $user_id ) {

		// only map data when the site admin is adding users, not on registration.
		if ( ! is_admin() ) { return false; }

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

			/**
			 * In multisite when not on the main blog, our options are not loaded
			 * because I mistakenly failed to use a site_option instead of a blog
			 * option. At the moment, there's little I can do except to switch to
			 * the BP root blog and grab the values from there. I assume this
			 * won't be a common occurrence and therefore that this won't cause
			 * too much of an overhead.
			 */

			// test for site other than main site
			if ( is_multisite() AND ! is_main_site() ) {

				// switch to main blog
				switch_to_blog( bp_get_root_blog_id() );

				// get options array, if it exists
				$this->options = get_option( 'bp_xp_wp_sync_options', array() );

				// switch back
				restore_current_blog();

			}

			// test for one of our options
			if ( isset( $this->options['first_name_field_id'] ) ) {

				// update first_name field
				xprofile_set_field_data(
					$this->options['first_name_field_id'],
					$user_id,
					$first_name
				);

				// update last_name field
				xprofile_set_field_data(
					$this->options['last_name_field_id'],
					$user_id,
					$last_name
				);

			}

			/**
			 * When xProfiles are updated, BuddyPress sets user nickname and display name
			 * so we should too...
			 */

			// construct full name
			$full_name = $first_name . ' ' . $last_name;

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
	 * Intercept BP core's attempt to sync to WP user profile
	 *
	 * @param integer $user_id
	 * @param array $posted_field_ids
	 * @param boolean $errors
	 * @return void
	 */
	public function intercept_wp_profile_sync( $user_id = 0, $posted_field_ids, $errors ) {

		// we're hooked in before BP core
		$bp = buddypress();

		if ( ! empty( $bp->site_options['bp-disable-profile-sync'] ) && (int) $bp->site_options['bp-disable-profile-sync'] )
			return true;

		if ( empty( $user_id ) )
			$user_id = bp_loggedin_user_id();

		if ( empty( $user_id ) )
			return false;

		// get our user's first name
		$first_name = xprofile_get_field_data(
			$this->options['first_name_field_id'],
			$user_id
		);

		// get our user's last name
		$last_name = xprofile_get_field_data(
			$this->options['last_name_field_id'],
			$user_id
		);

		// concatenate as per BP core
		$name = $first_name . ' ' . $last_name;
		//print_r( array( 'name' => $name ) ); die();

		/**
		 * Set default name field for this user - setting it now ensures that
		 * when xprofile_sync_wp_profile() is called, BuddyPress has the correct
		 * data to perform its updates with
		 */
		xprofile_set_field_data( bp_xprofile_fullname_field_name(), $user_id, $name );

	}



	/**
	 * Compatibility with "WP FB AutoConnect Premium"
	 *
	 * @param array $facebook_user
	 * @return array $facebook_user
	 */
	public function intercept_wp_fb_profile_sync( $facebook_user, $wp_user_id ) {

		/**
		 * When xProfiles are updated, BuddyPress sets user nickname and display name
		 * so WP FB AutoConnect Premium should do too. To do so, alter line 1315 or so:
		 *
		 * //A filter so 3rd party plugins can process any extra fields they might need
		 * $fbuser = apply_filters('wpfb_xprofile_fields_received', $fbuser, $args['WP_ID']);
		 */

		// set user nickname
		bp_update_user_meta( $wp_user_id, 'nickname', $facebook_user['name'] );

		// access db
		global $wpdb;

		// set user display name - see xprofile_sync_wp_profile()
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->users} SET display_name = %s WHERE ID = %d",
				$facebook_user['name'],
				$wp_user_id
			)
		);

		// pass it on
		return $facebook_user;

	}



	//##########################################################################



	/**
	 * Create a field with a given name
	 *
	 * @param str $field_name The name of the field
	 * @param str $field_description The field description
	 * @return int $field_id True on success, false on failure
	 */
	private function _create_field( $field_name, $field_description = '' ) {

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
		$description = $field_description;

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
		if ( ! is_numeric( $field_id ) ) {

			// construct message
			$msg = __(
				'BP XProfile WordPress User Sync plugin: Could not create xProfile field',
				'bp-xprofile-wp-user-sync'
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
		// Fixed in BuddyPress 1.9, but leave the check below for older versions
		$field = new BP_XProfile_Field( $field_id );

		// let's see if our new field is correctly set
		if ( $field->can_delete != 0 ) {

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
					'BP XProfile WordPress User Sync plugin: Could not set "can_delete" for xProfile field',
					'bp-xprofile-wp-user-sync'
				);

				// use var_dump as this seems to display in the iframe
				var_dump( $msg ); die();

			}

		}

		// --<
		return $field_id;

	}



	/**
	 * Update a field with a given ID
	 *
	 * The idea here is to try and reconnect the "orphaned" field data with the
	 * field definition by changing the auto-incremented ID of the field back to
	 * its original value. This should work because the original value should not
	 * have been reused unless the table has been truncated and the auto-increment
	 * value reset.
	 *
	 * @param int $old_field_id The previous ID of the field
	 * @param int $new_field_id The new ID of the field
	 * @return bool True if update successful
	 */
	private function _reconnect_field( $old_field_id, $new_field_id ) {

		// we'll need these to manually update
		global $wpdb, $bp;

		// check if old field exists
		$field = new BP_XProfile_Field( $old_field_id );

		// if it does, we've got a bigger problem...
		if ( isset( $field->id ) AND $field->id == $old_field_id ) {

			// construct message
			$msg = __(
				'BP XProfile WordPress User Sync plugin: An xProfile field with that ID already exists. Cannot reconnect data.',
				'bp-xprofile-wp-user-sync'
			);

			// use var_dump as this seems to display in the iframe
			var_dump( $msg ); die();

		}

		// construct query
		$sql = $wpdb->prepare(
			"UPDATE {$bp->profile->table_name_fields} SET id = %d WHERE id = %d",
			$old_field_id,
			$new_field_id
		);

		// we must have one row affected
		if ( $wpdb->query( $sql ) !== 1 ) {

			// construct message
			$msg = __(
				'BP XProfile WordPress User Sync plugin: Could not update "ID" for xProfile field. SQL = ' . $sql,
				'bp-xprofile-wp-user-sync'
			);

			// use var_dump as this seems to display in the iframe
			var_dump( $msg ); die();

		}

		// --<
		return true;

	}



	/**
	 * Get excluded fields on Profile View
	 *
	 * @return string $exclude_fields Comma-separated list of field IDs
	 */
	private function _get_excluded_fields() {

		// comma-delimit our fields
		$exclude_fields = implode( ',', $this->options );

		/**
		 * Exclude our xprofile fields, but allow filtering. The relevant params
		 * are passed to the filter so that other plugins can make an informed
		 * choice of what to return.
		 *
		 * To retain the first name and last name fields, an appropriate way to
		 * do this would look something like:
		 *
		 * add_filter( 'bp_xprofile_wp_user_sync_exclude_fields', 'my_function' );
		 * function my_function( $exclude_fields ) {
		 *     return bp_xprofile_fullname_field_id();
		 * }
		 *
		 * @param string $exclude_fields Comma-delimited pseudo-array of custom fields
		 * @param array $options Array of custom field IDs
		 */
		return apply_filters(
			'bp_xprofile_wp_user_sync_exclude_fields',
			$exclude_fields,
			$this->options
		);

	}



	/**
	 * Merge excluded fields on Profile View
	 *
	 * @param string $excluded_fields Comma-delimited list of fields already excluded
	 * @param string $exclude_fields Comma-delimited list of fields requiring exclusion
	 * @return string $excluded_fields Comma-delimited list of all fields to be excluded
	 */
	private function _merge_excluded_fields( $excluded_fields, $exclude_fields ) {

		// if params are not arrays already, convert them
		if ( ! is_array( $excluded_fields ) ) $excluded_fields = explode( ',', $excluded_fields );
		if ( ! is_array( $exclude_fields ) ) $exclude_fields = explode( ',', $exclude_fields );

		// merge with existing if populated
		if ( ! empty( $excluded_fields ) ) {
			$excluded_fields = array_unique( array_merge( $excluded_fields, $exclude_fields ) );
		} else {
			$excluded_fields = $exclude_fields;
		}

		// --<
		return implode( ',', $excluded_fields );

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

// uninstall will use the 'uninstall.php' method when xProfile fields can be "deactivated"
// see: http://codex.wordpress.org/Function_Reference/register_uninstall_hook





