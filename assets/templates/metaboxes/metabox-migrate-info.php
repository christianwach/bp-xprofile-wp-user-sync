<!-- assets/templates/metaboxes/metabox-migrate-info.php -->
<?php if ( $metabox['args']['migrated'] === false ) : ?>

	<h3><?php _e( 'Why am I seeing this?', 'bp-xprofile-wp-user-sync' ) ?></h3>

	<p><?php echo sprintf( __( 'The BP xProfile WordPress User Sync plugin is no longer needed because the "First Name" and "Last Name" xProfile Fields that it provides have been added to BuddyPress 8.0.0. In addition to this, BuddyPress itself now handles syncing the data with the built-in WordPress User fields. For more information about this transition, please visit the %1$sBuddyPress Dev Update page%2$s.', 'bp-xprofile-wp-user-sync' ), '<a href="https://bpdevel.wordpress.com/2021/03/24/wordpress-xprofile-field-types/">', '</a>' ); ?></p>

	<h3><?php _e( 'What needs to be done?', 'bp-xprofile-wp-user-sync' ) ?></h3>

	<p><?php _e( 'Before you go ahead and deactivate and delete the BP xProfile WordPress User Sync plugin, there are few things that need to be checked to make sure your site continues to work the way it did before.', 'bp-xprofile-wp-user-sync' ); ?></p>

	<?php if ( $metabox['args']['filters-metadata'] === false ) : ?>

		<div id="bpxpwp_filters">

			<h3><?php _e( 'Filters and Actions', 'bp-xprofile-wp-user-sync' ) ?></h3>

			<p><em><?php _e( 'If you have not implemented any of the Filters or Actions provided by this plugin, then you can click the "Confirm done" button now.', 'bp-xprofile-wp-user-sync' ); ?></em></p>

			<p><?php _e( 'When you deactivate this plugin, the Filters and Actions that it provides will no longer be available. If you have used them to modify or extend the behaviour of BP xProfile WordPress User Sync, then you will have to figure out the Filters and Actions in BuddyPress that can be used instead.', 'bp-xprofile-wp-user-sync' ); ?> <em><?php _e( 'You need to do this before taking any further action.', 'bp-xprofile-wp-user-sync' ); ?></em></p>

			<div class="bpxpwp_filters_notice notice notice-error inline" style="background-color: #f7f7f7;<?php echo $metabox['args']['filters-notice-hidden']; ?>">
				<p><?php echo $metabox['args']['filters-notice-message']; ?></p>
			</div>

			<?php submit_button( $metabox['args']['filters-button_title'], 'primary', 'bpxpwp_filters_process', false, [
				'data-security' => esc_attr( wp_create_nonce( 'bpxpwp_migrate_filters' ) ),
			] ); ?> <span class="spinner bpxpwp_filters"></span>

		</div>

	<?php endif; ?>

	<?php if ( $metabox['args']['settings-metadata'] === false ) : ?>

		<div id="bpxpwp_settings">

			<h3><?php _e( 'xProfile Field Settings', 'bp-xprofile-wp-user-sync' ) ?></h3>

			<p><?php _e( 'In order to switch to using the core BuddyPress functionality, you will have to make some changes to the "First Name" and "Last Name" xProfile Fields that were supplied by this plugin.', 'bp-xprofile-wp-user-sync' ); ?></p>

			<h4><?php _e( 'First Name Field', 'bp-xprofile-wp-user-sync' ) ?></h4>

			<ol>
				<li><?php _e( 'Visit the BuddyPress "Profile Fields" page and edit the "First Name" Field.', 'bp-xprofile-wp-user-sync' ); ?></li>
				<li><?php _e( 'Use the dropdown to switch the Field Type to "Text field" in the "WordPress Fields" group.', 'bp-xprofile-wp-user-sync' ); ?></li>
				<li><?php _e( 'A section called "Select the information you want to use for your WordPress field" will appear.', 'bp-xprofile-wp-user-sync' ); ?></li>
				<li><?php _e( 'Select the "First Name" radio button.', 'bp-xprofile-wp-user-sync' ); ?></li>
				<li><?php _e( 'Click "Update" to save the Field.', 'bp-xprofile-wp-user-sync' ); ?></li>
			</ol>

			<h4><?php _e( 'Last Name Field', 'bp-xprofile-wp-user-sync' ) ?></h4>

			<ol>
				<li><?php _e( 'Visit the BuddyPress "Profile Fields" page and edit the "Last Name" Field.', 'bp-xprofile-wp-user-sync' ); ?></li>
				<li><?php _e( 'Use the dropdown to switch the Field Type to "Text field" in the "WordPress Fields" group.', 'bp-xprofile-wp-user-sync' ); ?></li>
				<li><?php _e( 'A section called "Select the information you want to use for your WordPress field" will appear.', 'bp-xprofile-wp-user-sync' ); ?></li>
				<li><?php _e( 'Select the "Last Name" radio button.', 'bp-xprofile-wp-user-sync' ); ?></li>
				<li><?php _e( 'Click "Update" to save the Field.', 'bp-xprofile-wp-user-sync' ); ?></li>
			</ol>

			<div class="bpxpwp_settings_notice notice notice-error inline" style="background-color: #f7f7f7;<?php echo $metabox['args']['settings-notice-hidden']; ?>">
				<p><?php echo $metabox['args']['settings-notice-message']; ?></p>
			</div>

			<?php submit_button( $metabox['args']['settings-button_title'], 'primary', 'bpxpwp_settings_process', false, [
				'data-security' => esc_attr( wp_create_nonce( 'bpxpwp_migrate_settings' ) ),
			] ); ?> <span class="spinner bpxpwp_settings"></span>

		</div>

	<?php endif; ?>

	<p><?php _e( 'You should only deactivate and delete BP xProfile WordPress User Sync when you are sure everything mentioned here has been attended to and you have clicked "Submit".', 'bp-xprofile-wp-user-sync' ); ?></p>

<?php else : ?>

	<h3><?php _e( 'Congratulations!', 'bp-xprofile-wp-user-sync' ) ?></h3>

	<p><em><?php _e( 'You have successfully migrated from BP xProfile WordPress User Sync to BuddyPress core functionality.', 'bp-xprofile-wp-user-sync' ); ?></em></p>

	<p><?php echo sprintf(
		__( 'You can now go to your %1$sPlugins page%2$s and deactivate the BP xProfile WordPress User Sync plugin.', 'bp-xprofile-wp-user-sync' ),
		'<a href="' . admin_url( 'plugins.php' ) . '">',
		'</a>'
	); ?></p>

<?php endif; ?>
