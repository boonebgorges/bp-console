<?php /* This template is used by activity-loop.php and AJAX functions to show each activity */ ?>

<?php do_action( 'bp_before_activity_entry' ) ?>

<li class="<?php bp_activity_css_class() ?> console-activity-entry" id="activity-<?php bp_activity_id() ?>">
	<div class="activity-avatar">
		<a href="<?php bp_activity_user_link() ?>">
			<?php bp_activity_avatar() ?>
		</a>
	</div>

	<div class="activity-content">

		<div class="activity-header">
			<?php bp_activity_action() ?>

			<?php if ( is_user_logged_in() ) : ?>
				<?php if ( !bp_get_activity_is_favorite() ) : ?>
					<a href="<?php bp_activity_favorite_link() ?>" title="<?php _e( 'Mark as Favorite', 'buddypress' ) ?>"><?php _e( 'Favorite', 'buddypress' ) ?></a>
				<?php else : ?>
					<a href="<?php bp_activity_unfavorite_link() ?>" title="<?php _e( 'Remove Favorite', 'buddypress' ) ?>"><?php _e( 'Remove Favorite', 'buddypress' ) ?></a>
				<?php endif; ?>
			<?php endif;?>

		</div>

		<?php if ( bp_activity_has_content() ) : ?>
			<div class="activity-inner">
				<?php bp_activity_content_body() ?>
			</div>
		<?php endif; ?>

		<?php do_action( 'bp_activity_entry_content' ) ?>

	</div>

	<?php if ( 'activity_comment' == bp_get_activity_type() ) : ?>
		<div class="activity-inreplyto">
			<strong><?php _e( 'In reply to', 'buddypress' ) ?></strong> - <?php bp_activity_parent_content() ?> &middot;
			<a href="<?php bp_activity_thread_permalink() ?>" class="view" title="<?php _e( 'View Thread / Permalink', 'buddypress' ) ?>"><?php _e( 'View', 'buddypress' ) ?></a>
		</div>
	<?php endif; ?>

</li>


