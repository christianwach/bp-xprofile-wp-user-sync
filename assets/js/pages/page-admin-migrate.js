/**
 * "Migrate Page" Javascript.
 *
 * Implements progress bar functionality on the plugin's "Migration Page".
 *
 * @package BpXProfileWordPressUserSync
 */

/**
 * Create Migrate object.
 *
 * This works as a "namespace" of sorts, allowing us to hang properties, methods
 * and "sub-namespaces" from it.
 *
 * @since 0.6.7
 */
var BPXPWP_Migrate = BPXPWP_Migrate || {};

/**
 * Pass the jQuery shortcut in.
 *
 * @since 0.6.7
 *
 * @param {Object} $ The jQuery object.
 */
( function( $ ) {

	/**
	 * Create Settings Object.
	 *
	 * @since 0.6.7
	 */
	BPXPWP_Migrate.settings = new function() {

		// Prevent reference collisions.
		var me = this;

		/**
		 * Initialise Settings.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.6.7
		 */
		this.init = function() {
			me.init_localisation();
			me.init_settings();
		};

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.6.7
		 */
		this.dom_ready = function() {

		};

		// Init localisation array
		me.localisation = [];

		/**
		 * Init localisation from settings object.
		 *
		 * @since 0.6.7
		 */
		this.init_localisation = function() {
			if ( 'undefined' !== typeof BPXPWP_Migrate_Settings ) {
				me.localisation = BPXPWP_Migrate_Settings.localisation;
			}
		};

		/**
		 * Getter for localisation.
		 *
		 * @since 0.6.7
		 *
		 * @param {String} The identifier for the desired localisation string
		 * @return {String} The localised string
		 */
		this.get_localisation = function( identifier ) {
			return me.localisation[identifier];
		};

		// Init settings array.
		me.settings = [];

		/**
		 * Init settings from settings object.
		 *
		 * @since 0.6.7
		 */
		this.init_settings = function() {
			if ( 'undefined' !== typeof BPXPWP_Migrate_Settings ) {
				me.settings = BPXPWP_Migrate_Settings.settings;
			}
		};

		/**
		 * Getter for retrieving a setting.
		 *
		 * @since 0.6.7
		 *
		 * @param {String} The identifier for the desired setting
		 * @return The value of the setting
		 */
		this.get_setting = function( identifier ) {
			return me.settings[identifier];
		};

	};

	/**
	 * Create Filters & Actions Confirmation Object.
	 *
	 * @since 0.6.7
	 */
	BPXPWP_Migrate.filters_confirm = new function() {

		// Prevent reference collisions.
		var me = this;

		// Finished flag.
		me.finished = false;

		/**
		 * Initialise Progress Bar.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.6.7
		 */
		this.init = function() {

		};

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.6.7
		 */
		this.dom_ready = function() {

			// Bail if already migrated.
			var migrated = BPXPWP_Migrate.settings.get_setting( 'filters_migrated' );
			if ( migrated === 'y' ) {
				me.finished = true;
			}

			me.listeners();

		};

		/**
		 * Initialise listeners.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.6.7
		 */
		this.listeners = function() {

			// Declare vars.
			me.button_filters = $('#bpxpwp_filters_process');

			// The AJAX nonce.
			me.ajax_nonce = me.button_filters.data( 'security' );

			/**
			 * Add a click event listener to start process.
			 *
			 * @param {Object} event The event object.
			 */
			me.button_filters.on( 'click', function( event ) {

				// Prevent form submission.
				if ( event.preventDefault ) {
					event.preventDefault();
				}

				me.button_filters.prop( 'disabled', true );
				me.button_filters.val( BPXPWP_Migrate.settings.get_localisation( 'done' ) );
		        $(this).next('.spinner.bpxpwp_filters').css('visibility', 'visible');

				// Send.
				me.send();

			});

		};

		/**
		 * Send AJAX request.
		 *
		 * @since 0.6.7
		 *
		 * @param {Array} data The data received from the server
		 */
		this.update = function( data ) {

			// Declare vars.
			var val, batch_count;

			// Are we still in progress?
			if ( data.finished == 'false' ) {

				me.button_filters.prop( 'disabled', false );
				me.button_filters.val( BPXPWP_Migrate.settings.get_localisation( 'todo' ) );
				me.button_filters.next( '.spinner.bpxpwp_filters' ).css( 'visibility', 'hidden' );

				$('.bpxpwp_filters_notice').show();
				$('.bpxpwp_filters_notice p').html( data.message );

			} else {

				// Set finished flag.
				me.finished = true;

				// Hide the Filters & Actions section.
				setTimeout(function () {
					$('#bpxpwp_filters').hide();
					// Maybe enabled Submit.
					if ( BPXPWP_Migrate.settings_confirm.finished === true ) {
						$('#bpxpwp_save').prop('disabled', false);
					}
				}, 2000 );

			}

		};

		/**
		 * Send AJAX request.
		 *
		 * @since 0.6.7
		 */
		this.send = function() {

			// Define vars.
			var url, data;

			// URL to post to.
			url = BPXPWP_Migrate.settings.get_setting( 'ajax_url' );

			// Data received by WordPress.
			data = {
				action: 'bpxpwp_process_filters',
				_ajax_nonce: me.ajax_nonce
			};

			// Use jQuery post.
			$.post( url, data,

				// Callback.
				function( data, textStatus ) {

					// If success.
					if ( textStatus == 'success' ) {

						// Update progress bar.
						me.update( data );

					} else {

						// Show error.
						if ( console.log ) {
							console.log( textStatus );
						}

					}

				},

				// Expected format.
				'json'

			);

		};

	};

	/**
	 * Create xProfile Field Settings Confirmation Object.
	 *
	 * @since 0.6.7
	 */
	BPXPWP_Migrate.settings_confirm = new function() {

		// Prevent reference collisions.
		var me = this;

		// Finished flag.
		me.finished = false;

		/**
		 * Initialise Progress Bar.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.6.7
		 */
		this.init = function() {

		};

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.6.7
		 */
		this.dom_ready = function() {

			// Bail if already migrated.
			var migrated = BPXPWP_Migrate.settings.get_setting( 'settings_migrated' );
			if ( migrated === 'y' ) {
				me.finished = true;
			}

			me.listeners();

		};

		/**
		 * Initialise listeners.
		 *
		 * This method should only be called once.
		 *
		 * @since 0.6.7
		 */
		this.listeners = function() {

			// Declare vars.
			me.button_settings = $('#bpxpwp_settings_process');

			// The AJAX nonce.
			me.ajax_nonce = me.button_settings.data( 'security' );

			/**
			 * Add a click event listener to start process.
			 *
			 * @param {Object} event The event object.
			 */
			me.button_settings.on( 'click', function( event ) {

				// Prevent form submission.
				if ( event.preventDefault ) {
					event.preventDefault();
				}

				me.button_settings.prop( 'disabled', true );
				me.button_settings.val( BPXPWP_Migrate.settings.get_localisation( 'done' ) );
		        $(this).next('.spinner.bpxpwp_settings').css('visibility', 'visible');

				// Send.
				me.send();

			});

		};

		/**
		 * Send AJAX request.
		 *
		 * @since 0.6.7
		 *
		 * @param {Array} data The data received from the server
		 */
		this.update = function( data ) {

			// Declare vars.
			var val, batch_count;

			// Are we still in progress?
			if ( data.finished == 'false' ) {

				me.button_settings.prop( 'disabled', false );
				me.button_settings.val( BPXPWP_Migrate.settings.get_localisation( 'todo' ) );
				me.button_settings.next( '.spinner.bpxpwp_settings' ).css( 'visibility', 'hidden' );

				$('.bpxpwp_settings_notice').show();
				$('.bpxpwp_settings_notice p').html( data.message );

			} else {

				// Set finished flag.
				me.finished = true;

				// Hide the xProfile Field Settings section.
				setTimeout(function () {
					$('#bpxpwp_settings').hide();
					// Maybe enabled Submit.
					if ( BPXPWP_Migrate.filters_confirm.finished === true ) {
						$('#bpxpwp_save').prop( 'disabled', false );
					}
				}, 2000 );

			}

		};

		/**
		 * Send AJAX request.
		 *
		 * @since 0.6.7
		 */
		this.send = function() {

			// Define vars.
			var url, data;

			// URL to post to.
			url = BPXPWP_Migrate.settings.get_setting( 'ajax_url' );

			// Data received by WordPress.
			data = {
				action: 'bpxpwp_process_settings',
				_ajax_nonce: me.ajax_nonce
			};

			// Use jQuery post.
			$.post( url, data,

				// Callback.
				function( data, textStatus ) {

					// If success.
					if ( textStatus == 'success' ) {

						// Update progress bar.
						me.update( data );

					} else {

						// Show error.
						if ( console.log ) {
							console.log( textStatus );
						}

					}

				},

				// Expected format.
				'json'

			);

		};

	};

	// Init settings.
	BPXPWP_Migrate.settings.init();

	// Init Progress Bars.
	BPXPWP_Migrate.filters_confirm.init();
	BPXPWP_Migrate.settings_confirm.init();

} )( jQuery );

/**
 * Trigger dom_ready methods where necessary.
 *
 * @since 0.6.7
 */
jQuery(document).ready(function($) {

	// The DOM is loaded now.
	BPXPWP_Migrate.settings.dom_ready();

	// The DOM is loaded now.
	BPXPWP_Migrate.filters_confirm.dom_ready();
	BPXPWP_Migrate.settings_confirm.dom_ready();

}); // end document.ready()
