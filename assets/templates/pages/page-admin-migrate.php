<?php
/**
 * Migration Page template.
 *
 * Handles markup for the Migration Page.
 *
 * @package BpXProfileWordPressUserSync
 * @since 0.6.7
 */

?><!-- assets/templates/pages/page-admin-migrate.php -->
<div class="wrap">

	<h1><?php _e( 'BP xProfile WordPress User Sync', 'bp-xprofile-wp-user-sync' ); ?></h1>

	<form method="post" id="<?php echo $this->migrate_page_slug; ?>_form" action="<?php echo $this->page_submit_url_get(); ?>">

		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( $this->migrate_page_slug . '_action', $this->migrate_page_slug . '_nonce' ); ?>

		<div id="poststuff">

			<div id="post-body" class="metabox-holder columns-<?php echo $columns;?>">

				<!--<div id="post-body-content">
				</div>--><!-- #post-body-content -->

				<div id="postbox-container-1" class="postbox-container">
					<?php do_meta_boxes( $screen->id, 'side', null ); ?>
				</div>

				<div id="postbox-container-2" class="postbox-container">
					<?php do_meta_boxes( $screen->id, 'normal', null );  ?>
					<?php do_meta_boxes( $screen->id, 'advanced', null ); ?>
				</div>

			</div><!-- #post-body -->
			<br class="clear">

		</div><!-- #poststuff -->

	</form>

</div><!-- /.wrap -->
