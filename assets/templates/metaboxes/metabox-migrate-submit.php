<!-- assets/templates/metaboxes/metabox-migrate-submit.php -->
<div class="submitbox">
	<div id="minor-publishing">
		<div id="misc-publishing-actions">
			<div class="misc-pub-section">
				<span><?php _e( 'I have checked everything mentioned here and I am ready to migrate.', 'bp-xprofile-wp-user-sync' ); ?></span>
			</div>
		</div>
		<div class="clear"></div>
	</div>

	<div id="major-publishing-actions">
		<div id="publishing-action">
			<?php submit_button( esc_html__( 'Submit', 'bp-xprofile-wp-user-sync' ), 'primary', 'bpxpwp_save', false, $metabox['args']['submit-atts'] ); ?>
			<input type="hidden" name="action" value="update" />
		</div>
		<div class="clear"></div>
	</div>
</div>
