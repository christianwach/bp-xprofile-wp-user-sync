<?php
/**
 * BuddyPress compatibility Class.
 *
 * Handles migration of data to BuddyPress 8.0+.
 *
 * @package BpXProfileWordPressUserSync
 * @since 0.6.7
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;



/**
 * BP xProfile WP User Sync Migrate Class.
 *
 * This class Handles migration of data to BuddyPress 8.0+.
 *
 * @since 0.6.7
 */
class BP_xProfile_WP_User_Sync_Migrate {

	/**
	 * Plugin (calling) object.
	 *
	 * @since 0.6.7
	 * @access public
	 * @var object $plugin The plugin object.
	 */
	public $plugin;

	/**
	 * BuddyPress reference.
	 *
	 * @since 0.6.7
	 * @access public
	 * @var object $acf The BuddyPress plugin reference.
	 */
	public $bp = false;

	/**
	 * Migration Page reference.
	 *
	 * @since 0.6.7
	 * @access public
	 * @var str $migrate_page The Migration Page reference.
	 */
	public $migrate_page;

	/**
	 * Migration Page slug.
	 *
	 * @since 0.6.7
	 * @access public
	 * @var str $migrate_page_slug The slug of the Migration Page.
	 */
	public $migrate_page_slug = 'bpxpwp_migrate';

	/**
	 * The number of Items to process per AJAX request.
	 *
	 * @since 0.6.7
	 * @access public
	 * @var int $step_count The number of Items to process per AJAX request.
	 */
	public $step_count = 10;



	/**
	 * Initialises this object.
	 *
	 * @since 0.6.7
	 *
	 * @param object $parent The parent object.
	 */
	public function __construct( $parent ) {

		// Store reference.
		$this->plugin = $parent;

		// Store reference to BuddyPress.
		$this->bp = buddypress();

		// Register hooks.
		$this->register_hooks();

		/**
		 * Broadcast that this class is now loaded.
		 *
		 * @since 0.6.7
		 */
		do_action( 'bpxpwp/migrate/loaded' );

	}



	/**
	 * Register hooks.
	 *
	 * @since 0.6.7
	 */
	public function register_hooks() {

		// Bail if not WordPress Admin.
		if ( ! is_admin() ) {
			return;
		}

		// Show a notice.
		add_action( 'admin_notices', [ $this, 'admin_notice' ] );

		// Add menu item(s) to WordPress admin menu.
		add_action( 'admin_menu', [ $this, 'admin_menu' ], 30 );

		// Add our meta boxes.
		add_action( 'add_meta_boxes', [ $this, 'meta_boxes_add' ], 11, 1 );

		// Add AJAX handlers.
		add_action( 'wp_ajax_bpxpwp_process_filters', [ $this, 'filters_process' ] );
		add_action( 'wp_ajax_bpxpwp_process_settings', [ $this, 'settings_process' ] );

	}



	// -------------------------------------------------------------------------



	/**
	 * Show a notice when migration is required.
	 *
	 * @since 0.6.7
	 */
	public function admin_notice() {

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get current screen.
		$screen = get_current_screen();

		// Bail if it's not what we expect.
		if ( ! ( $screen instanceof WP_Screen ) ) {
			return;
		}

		// Bail if we are on our "Migration" page.
		if ( $screen->id == 'settings_page_' . $this->migrate_page_slug ) {
			return;
		}

		// Have we already migrated?
		if ( in_array( 'accepted', get_site_option( 'bp_xp_wp_sync_migration', [] ) ) ) {

			// Show general "Call to Action".
			$message = sprintf(
				__( 'You can now deactivate %1$sBP xProfile WordPress User Sync%2$s.', 'bp-xprofile-wp-user-sync' ),
				'<strong>', '</strong>'
			);

			// Add a link if we are not on the "Plugins" page.
			if ( $screen->id !== 'plugins' ) {

				//Add link.
				$link = sprintf(
					__( 'Please visit the %1$sPlugins Page%2$s to deactivate it.', 'bp-xprofile-wp-user-sync' ),
					'<a href="' . admin_url( 'plugins.php' ) . '">', '</a>'
				);

				// Merge.
				$message = sprintf( __( '%1$s %2$s', 'bp-xprofile-wp-user-sync' ), $message, $link );

			}

		} else {

			// Show general "Call to Action".
			$message = sprintf(
				__( '%1$sBP xProfile WordPress User Sync%2$s is no longer required. Please visit the %3$sMigration Page%4$s before disabling it.', 'bp-xprofile-wp-user-sync' ),
				'<strong>', '</strong>',
				'<a href="' . menu_page_url( $this->migrate_page_slug, false ) . '">', '</a>'
			);

		}

		// Show it.
		echo '<div id="message" class="notice notice-warning">';
		echo '<p>' . $message . '</p>';
		echo '</div>';

	}



	/**
	 * Add our admin page to the WordPress admin menu.
	 *
	 * @since 0.6.7
	 */
	public function admin_menu() {

		// We must be network admin in Multisite.
		if ( is_multisite() AND ! is_super_admin() ) {
			return;
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Add our "Migration Page" to the Settings menu.
		$this->migrate_page = add_options_page(
			__( 'BP xProfile WordPress User Sync', 'bp-xprofile-wp-user-sync' ), // Page title.
			__( 'BP xProfile Sync', 'bp-xprofile-wp-user-sync' ), // Menu title.
			'manage_options', // Required caps.
			$this->migrate_page_slug, // Slug name.
			[ $this, 'page_migrate' ] // Callback.
		);

		// Register our form submit hander.
		add_action( 'load-' . $this->migrate_page, [ $this, 'form_submitted' ] );

		// Add styles and scripts only on our "Migration Page".
		// @see wp-admin/admin-header.php
		add_action( 'admin_head-' . $this->migrate_page, [ $this, 'admin_head' ] );
		add_action( 'admin_print_styles-' . $this->migrate_page, [ $this, 'admin_styles' ] );
		add_action( 'admin_print_scripts-' . $this->migrate_page, [ $this, 'admin_scripts' ] );

	}



	/**
	 * Add metabox scripts and initialise plugin help.
	 *
	 * @since 0.6.7
	 */
	public function admin_head() {

		// Enqueue WordPress scripts.
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'dashboard' );

	}



	/**
	 * Enqueue required scripts.
	 *
	 * @since 0.6.7
	 */
	public function admin_scripts() {

		$handle = 'bpxpwp_js';

		// Enqueue Javascript.
		wp_enqueue_script(
			$handle,
			plugins_url( 'assets/js/pages/page-admin-migrate.js', BP_XPROFILE_WP_USER_SYNC_FILE ),
			[ 'jquery' ],
			BP_XPROFILE_WP_USER_SYNC_VERSION // Version.
		);

		$localisation = [
			'todo' => esc_html__( 'Confirm done', 'bp-xprofile-wp-user-sync' ),
			'done' => esc_html__( 'Confirming...', 'bp-xprofile-wp-user-sync' ),
		];

		$settings = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		];

		$vars = [
			'localisation' => $localisation,
			'settings' => $settings,
		];

		// Localise the WordPress way.
		wp_localize_script( $handle, 'BPXPWP_Migrate_Settings', $vars );

	}



	/**
	 * Enqueue any styles needed by our Migrate Page.
	 *
	 * @since 0.6.7
	 */
	public function admin_styles() {

		// Enqueue CSS.
		wp_enqueue_style(
			'bpxpwp-admin-migrate',
			BP_XPROFILE_WP_USER_SYNC_URL . 'assets/css/pages/page-admin-migrate.css',
			null,
			BP_XPROFILE_WP_USER_SYNC_VERSION,
			'all' // Media.
		);

	}



	/**
	 * Show our "Migration Page".
	 *
	 * @since 0.6.7
	 */
	public function page_migrate() {

		// We must be network admin in Multisite.
		if ( is_multisite() AND ! is_super_admin() ) {
			wp_die( __( 'You do not have permission to access this page.', 'bp-xprofile-wp-user-sync' ) );
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have permission to access this page.', 'bp-xprofile-wp-user-sync' ) );
		}

		// Get current screen.
		$screen = get_current_screen();

		/**
		 * Allow meta boxes to be added to this screen.
		 *
		 * The Screen ID to use is: "settings_page_bpxpwp_migrate".
		 *
		 * @since 0.6.7
		 *
		 * @param string $screen_id The ID of the current screen.
		 */
		do_action( 'add_meta_boxes', $screen->id, null );

		// Grab columns.
		$columns = ( 1 == $screen->get_columns() ? '1' : '2' );

		// Include template file.
		include BP_XPROFILE_WP_USER_SYNC_PATH . 'assets/templates/pages/page-admin-migrate.php';

	}



	/**
	 * Get the URL for the form action.
	 *
	 * @since 0.6.7
	 *
	 * @return string $target_url The URL for the admin form action.
	 */
	public function page_submit_url_get() {

		// Sanitise admin page url.
		$target_url = $_SERVER['REQUEST_URI'];
		$url_array = explode( '&', $target_url );

		// Strip flag, if present, and rebuild.
		if ( ! empty( $url_array ) ) {
			$url_raw = str_replace( '&amp;updated=true', '', $url_array[0] );
			$target_url = htmlentities( $url_raw . '&updated=true' );
		}

		return $target_url;

	}



	/**
	 * Register meta boxes.
	 *
	 * @since 0.6.7
	 *
	 * @param string $screen_id The Admin Page Screen ID.
	 */
	public function meta_boxes_add( $screen_id ) {

		// Define valid Screen IDs.
		$screen_ids = [
			'settings_page_' . $this->migrate_page_slug,
		];

		// Bail if not the Screen ID we want.
		if ( ! in_array( $screen_id, $screen_ids ) ) {
			return;
		}

		// Bail if user cannot access CiviCRM.
		if ( ! current_user_can( 'access_civicrm' ) ) {
			return;
		}

		// Init data.
		$data = [];

		// Have we already migrated?
		$data['migrated'] = false;
		if ( in_array( 'accepted', get_site_option( 'bp_xp_wp_sync_migration', [] ) ) ) {
			$data['migrated'] = true;
		}

		// Init meta box title.
		$title = __( 'Migration Tasks', 'bp-xprofile-wp-user-sync' );
		if ( $data['migrated'] === true ) {
			$title = __( 'Migration Complete', 'bp-xprofile-wp-user-sync' );
		}

		// Have we already resolved Filters?
		$data['filters-metadata'] = false;
		if ( in_array( 'filters-migrated', get_site_option( 'bp_xp_wp_sync_migration', [] ) ) ) {
			$data['filters-metadata'] = true;
		}

		// Set the Filters button title.
		$data['filters-button_title'] = esc_html__( 'Confirm done', 'bp-xprofile-wp-user-sync' );

		// Set the Filters message.
		$data['filters-notice-message'] = '';
		$data['filters-notice-hidden'] = ' display: none;';

		// Have we already resolved Settings metadata?
		$data['settings-metadata'] = false;
		if ( in_array( 'settings-migrated', get_site_option( 'bp_xp_wp_sync_migration', [] ) ) ) {
			$data['settings-metadata'] = true;
		}

		// Set the Settings button title.
		$data['settings-button_title'] = esc_html__( 'Confirm done', 'bp-xprofile-wp-user-sync' );

		// Set the Filters message.
		$data['settings-notice-message'] = '';
		$data['settings-notice-hidden'] = ' display: none;';

		// Only show Submit if not migrated.
		if ( $data['migrated'] === false ) {

			// Disable Submit if not fully migrated.
			$data['submit-atts'] = [];
			if ( $data['filters-metadata'] === false || $data['settings-metadata'] === false ) {
				$data['submit-atts'] = [
					'disabled' => 'disabled',
				];
			}

			// Create Submit metabox.
			add_meta_box(
				'submitdiv',
				__( 'Confirm Migration', 'bp-xprofile-wp-user-sync' ),
				[ $this, 'meta_box_submit_render' ], // Callback.
				$screen_id, // Screen ID.
				'side', // Column: options are 'normal' and 'side'.
				'core', // Vertical placement: options are 'core', 'high', 'low'.
				$data
			);

		}

		// Create "Migrate Info" metabox.
		add_meta_box(
			'bpxpwp_info',
			$title,
			[ $this, 'meta_box_migrate_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core', // Vertical placement: options are 'core', 'high', 'low'.
			$data
		);

	}



	/**
	 * Render Submit meta box on Admin screen.
	 *
	 * @since 0.6.7
	 *
	 * @param mixed $unused Unused param.
	 * @param array $metabox Array containing id, title, callback, and args elements.
	 */
	public function meta_box_submit_render( $unused, $metabox ) {

		// Include template file.
		include BP_XPROFILE_WP_USER_SYNC_PATH . 'assets/templates/metaboxes/metabox-migrate-submit.php';

	}



	/**
	 * Render "Migrate Settings" meta box on Admin screen.
	 *
	 * @since 0.6.7
	 *
	 * @param mixed $unused Unused param.
	 * @param array $metabox Array containing id, title, callback, and args elements.
	 */
	public function meta_box_migrate_render( $unused, $metabox ) {

		// Include template file.
		include BP_XPROFILE_WP_USER_SYNC_PATH . 'assets/templates/metaboxes/metabox-migrate-info.php';

	}



	/**
	 * Perform actions when the form has been submitted.
	 *
	 * @since 0.6.7
	 */
	public function form_submitted() {

		// If our "Submit" button was clicked.
		if ( ! empty( $_POST['bpxpwp_save'] ) ) {
			$this->form_nonce_check();
			$this->form_migration_accept();
			$this->form_redirect( 'updated' );
		}

		// If our Filters & Actions "Confirm done" button was clicked.
		if ( ! empty( $_POST['bpxpwp_filters_process'] ) ) {
			$this->form_nonce_check();
			$this->filters_process();
			$this->form_redirect();
		}

		// If our xProfile Field "Confirm done" button was clicked.
		if ( ! empty( $_POST['bpxpwp_settings_process'] ) ) {
			$this->form_nonce_check();
			$this->settings_process();
			$this->form_redirect();
		}

	}



	/**
	 * Accept the migration tasks.
	 *
	 * @since 0.6.7
	 */
	private function form_migration_accept() {

		// Do this by adding an element to the migration settings array.
		$settings = get_site_option( 'bp_xp_wp_sync_migration', [] );
		$settings[] = 'accepted';
		update_site_option( 'bp_xp_wp_sync_migration', $settings );

	}



	/**
	 * Check the nonce.
	 *
	 * @since 0.6.7
	 */
	private function form_nonce_check() {

		// Do we trust the source of the data?
		check_admin_referer( $this->migrate_page_slug . '_action', $this->migrate_page_slug . '_nonce' );

	}



	/**
	 * Redirect to the Settings page with an optional extra param.
	 *
	 * @since 0.6.7
	 *
	 * @param str $mode Pass 'updated' to append the extra param.
	 */
	private function form_redirect( $mode = '' ) {

		// Our default array of arguments.
		$args = [
			'page' => $this->migrate_page_slug,
		];

		// Maybe append param.
		if ( $mode === 'updated' ) {
			$args['settings-updated'] = 'true';
		}

		// Redirect to our admin page.
		wp_safe_redirect( add_query_arg( $args, menu_page_url( $this->migrate_page_slug, false ) ) );
		exit;

	}



	/**
	 * The Filters & Actions "Confirm done" AJAX callback.
	 *
	 * @since 0.6.7
	 */
	public function filters_process() {

		// Init return.
		$data = [ 'finished' => 'false' ];

		// If this is an AJAX request, check security.
		$result = true;
		if ( wp_doing_ajax() ) {
			$result = check_ajax_referer( 'bpxpwp_migrate_filters', false, false );
		}

		// Bail if we get an error.
		if ( $result === false ) {
			$data['message'] = __( 'Authentication failed.', 'bp-xprofile-wp-user-sync' );
			wp_send_json( $data );
			return;
		}

		// Set finished flag.
		$data['finished'] = 'true';

		// Add a unique string to the migration option.
		$settings = get_site_option( 'bp_xp_wp_sync_migration', [] );
		$settings[] = 'filters-migrated';
		update_site_option( 'bp_xp_wp_sync_migration', $settings );

		// Send data to browser.
		if ( wp_doing_ajax() ) {
			wp_send_json( $data );
		}

	}



	/**
	 * The xProfile Field Settings "Confirm done" AJAX callback.
	 *
	 * @since 0.6.7
	 */
	public function settings_process() {

		// Init return.
		$data = [ 'finished' => 'false' ];

		// If this is an AJAX request, check security.
		$result = true;
		if ( wp_doing_ajax() ) {
			$result = check_ajax_referer( 'bpxpwp_migrate_settings', false, false );
		}

		// Bail if we get an error.
		if ( $result === false ) {
			$data['message'] = __( 'Authentication failed.', 'bp-xprofile-wp-user-sync' );
			wp_send_json( $data );
			return;
		}

		// Maybe switch to BP root site.
		if ( is_multisite() ) {
			switch_to_blog( bp_get_root_blog_id() );
		}

		// Get options array, if it exists.
		$options = get_option( 'bp_xp_wp_sync_options', [] );

		// Maybe switch back.
		if ( is_multisite() ) {
			restore_current_blog();
		}

		// Let's look at the stored fields.
		if ( ! empty( $options['first_name_field_id'] ) ) {

			$field = new BP_XProfile_Field( $options['first_name_field_id'] );

			if ( empty( $field->type ) OR $field->type !== 'wp-textbox' ) {
				$data['message'] = __( 'Please change the Type of the "First Name" xProfile Field', 'bp-xprofile-wp-user-sync' );
				wp_send_json( $data );
				return;
			}

			$wp_user_key = bp_xprofile_get_meta( $field->id, 'field', 'wp_user_key', true );
			if ( empty( $wp_user_key ) OR $wp_user_key !== 'first_name' ) {
				$data['message'] = __( 'Please select the WordPress User field for the "First Name" xProfile Field', 'bp-xprofile-wp-user-sync' );
				wp_send_json( $data );
				return;
			}

		}
		if ( ! empty( $options['last_name_field_id'] ) ) {

			$field = new BP_XProfile_Field( $options['last_name_field_id'] );

			if ( empty( $field->type ) OR $field->type !== 'wp-textbox' ) {
				$data['message'] = __( 'Please change the Type of the "Last Name" xProfile Field', 'bp-xprofile-wp-user-sync' );
				wp_send_json( $data );
				return;
			}

			$wp_user_key = bp_xprofile_get_meta( $field->id, 'field', 'wp_user_key', true );
			if ( empty( $wp_user_key ) OR $wp_user_key !== 'last_name' ) {
				$data['message'] = __( 'Please select the WordPress User field for the "Last Name" xProfile Field', 'bp-xprofile-wp-user-sync' );
				wp_send_json( $data );
				return;
			}

		}

		// Set finished flag because there is not much else we can do.
		$data['finished'] = 'true';

		// Add a unique string to the migration option.
		$settings = get_site_option( 'bp_xp_wp_sync_migration', [] );
		$settings[] = 'settings-migrated';
		update_site_option( 'bp_xp_wp_sync_migration', $settings );

		// Send data to browser.
		if ( wp_doing_ajax() ) {
			wp_send_json( $data );
		}

	}



} // Class ends.



