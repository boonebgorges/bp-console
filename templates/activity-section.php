
<h3><?php bp_console_section_title() ?></h3>

<?php if ( bp_has_activities( bp_console_activity_args( 1 ) ) ) : ?>

	<?php /* Code borrowed liberally from bp-default :) */ ?>

	<?php /* Show pagination if JS is not enabled, since the "Load More" link will do nothing */ ?>
		<noscript>
			<div class="pagination">
				<div class="pag-count"><?php bp_activity_pagination_count() ?></div>
				<div class="pagination-links"><?php bp_activity_pagination_links() ?></div>
			</div>
		</noscript>

		<?php if ( empty( $_POST['page'] ) ) : ?>
			<ul id="activity-stream" class="activity-list item-list">
		<?php endif; ?>

		<?php while ( bp_activities() ) : bp_the_activity(); ?>
			<?php $temp = locate_template( array( 'console/activity-entry.php' ), false ) ?>
			<?php if ( $temp ) : ?>
				<?php include( locate_template( array( 'console/activity-entry.php' ), false ) ) ?>
			<?php else : ?>
				<?php include( WP_PLUGIN_DIR . '/bp-console/templates/activity-entry.php' ) ?>
			<?php endif; ?>

		<?php endwhile; ?>

		<?php if ( bp_get_activity_count() == bp_get_activity_per_page() ) : ?>
			<li class="load-more">
				<a href="#more"><?php _e( 'Load More', 'buddypress' ) ?></a> &nbsp; <span class="ajax-loader"></span>
			</li>
		<?php endif; ?>

		<?php if ( empty( $_POST['page'] ) ) : ?>
			</ul>
		<?php endif; ?>
<?php else : ?>
	<div id="message" class="info">
		<?php _e( 'Sorry, there was no activity found', 'bp-console' ) ?>
	</div>
<?php endif; ?>