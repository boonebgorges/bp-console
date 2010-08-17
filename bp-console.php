<?php
/*
- Setting to make it the initial login page
*/

require_once( dirname( __FILE__ ) . '/bp-console-templatetags.php' );
require_once( dirname( __FILE__ ) . '/bp-console-filters.php' );

if ( !defined( 'BP_CONSOLE_SLUG' ) )
	define ( 'BP_CONSOLE_SLUG', 'console' );


function bp_console_add_css() {
	global $bp;

	if ( $bp->current_component == BP_CONSOLE_SLUG ) {
   		$style_url = WP_PLUGIN_URL . '/bp-console/_inc/css/main.css';
        $style_file = WP_PLUGIN_DIR . '/bp-console/_inc/css/main.css';

        if (file_exists($style_file)) {
            wp_register_style('bp-console-css', $style_url);
            wp_enqueue_style('bp-console-css');
        }
    }
}
add_action( 'wp_print_styles', 'bp_console_add_css' );



function bp_console_setup_globals() {
	global $bp, $wpdb;

	$bp->console->id = 'console';

	$bp->console->table_name = $wpdb->base_prefix . 'bp_console';
	$bp->console->slug = 'console';

	/* Register this in the active components array */
	$bp->active_components[$bp->console->slug] = $bp->console->id;
}
add_action( 'bp_setup_globals', 'bp_console_setup_globals', 2 );
//add_action( 'admin_menu', 'bp_console_setup_globals', 2 );


function bp_console_setup_root_component() {
	/* Register 'groups' as a root component */
	bp_core_add_root_component( BP_CONSOLE_SLUG );
}
add_action( 'bp_setup_root_components', 'bp_console_setup_root_component' );

function bp_console_setup_nav() {
	global $bp;

	/* Add 'Console' to the main user profile navigation */
	bp_core_new_nav_item( array(
		'name' => __( 'Console', 'bp-console' ),
		'slug' => $bp->console->slug,
		'position' => 01,
		'screen_function' => 'bp_console_main_display',
		'default_subnav_slug' => 'console',
		'show_for_displayed_user' => bp_is_my_profile()
	) );

	$console_link = $bp->loggedin_user->domain . $bp->console->slug . '/';

	/* Create two sub nav items for this component */
	bp_core_new_subnav_item( array(
		'name' => __( 'My Console', 'bp-console' ),
		'slug' => 'my-console',
		'parent_slug' => $bp->console->slug,
		'parent_url' => $console_link,
		'screen_function' => 'bp_console_main_display',
		'position' => 10,
		'user_has_access' => bp_is_my_profile()
	) );

	bp_core_new_subnav_item( array(
		'name' => __( 'Console Setup', 'bp-console' ),
		'slug' => 'setup',
		'parent_slug' => $bp->console->slug,
		'parent_url' => $console_link,
		'screen_function' => 'bp_console_setup_display',
		'position' => 20,
		'user_has_access' => bp_is_my_profile()
	) );
}
add_action( 'bp_setup_nav', 'bp_console_setup_nav', 1 );

function bp_console_directory_groups_setup() {
	global $bp;

	if ( $bp->current_component == $bp->console->slug && empty( $bp->current_action ) && empty( $bp->current_item ) ) {
		$bp->is_directory = true;

		do_action( 'groups_directory_groups_setup' );
		bp_core_load_template( apply_filters( 'groups_template_directory_groups', 'groups/index' ) );
	}
}
// Only need to add if you want Console as a tab. Still must add nav for it
//add_action( 'wp', 'bp_console_directory_groups_setup', 3 );



function bp_console_main_display() {
	global $bp;

	do_action( 'bp_console_main' );

	if ( file_exists( locate_template( array( 'console/main.php' ) ) ) ) {
		bp_core_load_template( apply_filters( 'groupblog_screen_blog', 'console/main' ) );
		add_action( 'wp', 'groupblog_screen_blog', 4 );
	}
	else {
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	  	add_action( 'bp_template_content', 'bp_console_main_content' );
	}
}

function bp_console_main_content() {

	load_template( WP_PLUGIN_DIR . '/bp-console/templates/main.php' );
}


function invite_anyone_screen_one_content() {
	global $bp;



		if ( !$iaoptions = get_option( 'invite_anyone' ) )
			$iaoptions = array();

		if ( !$max_invites = $iaoptions['max_invites'] )
			$max_invites = 5;

		if ( 'group-invites' == $bp->action_variables[0] )
			$from_group = $bp->action_variables[1];

		/* This handles the email addresses sent back when there is an error */
		$returned_emails = array();
		$counter = 0;
		while ( $_GET['email' . $counter] ) {
			$returned_emails[] = trim( urldecode( $_GET['email' . $counter] ) );
			$counter++;
		}

		$returned_groups = array( 0 );

		/* If the user is coming from the widget, $returned_emails is populated with those email addresses */
		if ( $_POST['invite_anyone_widget'] ) {
			check_admin_referer( 'invite-anyone-widget_' . $bp->loggedin_user->id );

			if ( is_array( $_POST['emails'] ) ) {
				foreach( $_POST['emails'] as $email ) {
					if ( trim( $email ) != '' && trim( $email ) != __( 'email address', 'bp-invite-anyone' ) )
						$returned_emails[] = trim( $email );
				}
			}

			/* If the widget appeared on a group page, the group ID should come along with it too */
			if ( isset( $_POST['invite_anyone_widget_group'] ) )
				$returned_groups[] = $_POST['invite_anyone_widget_group'];

		}

		/* $returned_groups is padded so that array_search (below) returns true for first group */
		$counter = 0;
		while ( $_GET['group' . $counter] ) {
			$returned_groups[] = urldecode( $_GET['group' . $counter] );
			$counter++;
		}

		if ( $_GET['subject'] )
			$returned_subject = stripslashes( urldecode( $_GET['subject'] ) );

		if ( $_GET['message'] )
			$returned_message = stripslashes( urldecode( $_GET['message'] ) );

		$blogname = get_bloginfo('name');
		$welcome_message = sprintf( __( 'Invite friends to join %s by following these steps:', 'bp-invite-anyone' ), $blogname );
	?>
	<form action="<?php echo $bp->displayed_user->domain . $bp->invite_anyone->slug . '/sent-invites/send/' ?>" method="post">

	<ol id="invite-anyone-steps">
		<h4><?php _e( 'Invite New Members', 'bp-invite-anyone' ) ?></h4>
		<p><?php echo $welcome_message ?></p>

		<li>
			<p><?php _e( 'Enter email addresses in the fields below.', 'bp-invite-anyone' ) ?> <?php if( invite_anyone_allowed_domains() ) : ?> <?php _e( 'You can only invite people whose email addresses end in one of the following domains:', 'bp-invite-anyone' ) ?> <?php echo invite_anyone_allowed_domains(); ?><?php endif; ?></p>
		</li>

		<?php invite_anyone_email_fields( $returned_emails ) ?>

		<li>
			<?php if ( $iaoptions['subject_is_customizable'] == 'yes' ) : ?>
				<p><?php _e( '(optional) Customize the subject line of the invitation email.', 'bp-invite-anyone' ) ?></p>
					<textarea name="invite_anyone_custom_subject" id="invite-anyone-custom-subject"><?php echo invite_anyone_invitation_subject( $returned_subject ) ?></textarea>
			<?php else : ?>
				<p><?php _e( 'Subject:', 'bp-invite-anyone' ) ?><br />
					<textarea name="invite_anyone_custom_subject" id="invite-anyone-custom-subject" disabled="disabled"><?php echo invite_anyone_invitation_subject( $returned_subject ) ?></textarea>
				</p>
				<input type="hidden" name="invite_anyone_custom_subject" value="<?php echo invite_anyone_invitation_subject() ?>" />
			<?php endif; ?>
		</li>

		<li>
			<?php if ( $iaoptions['message_is_customizable'] == 'yes' ) : ?>
				<p><?php _e( '(optional) Customize the text of the invitation.', 'bp-invite-anyone' ) ?></p>
					<textarea name="invite_anyone_custom_message" id="invite-anyone-custom-message"><?php echo invite_anyone_invitation_message( $returned_message ) ?></textarea>
			<?php else : ?>
				<p><?php _e( 'Message:', 'bp-invite-anyone' ) ?><br />
					<textarea name="invite_anyone_custom_message" id="invite-anyone-custom-message" disabled="disabled"><?php echo invite_anyone_invitation_message( $returned_message ) ?></textarea>
				</p>
				<input type="hidden" name="invite_anyone_custom_message" value="<?php echo invite_anyone_invitation_message() ?>" />
			<?php endif; ?>
				<p><?php _e( 'The message will also contain a custom footer containing links to accept the invitation or opt out of further email invitations from this site.', 'bp-invite-anyone' ) ?></p>

		</li>

		<?php if ( invite_anyone_are_groups_running() ) : ?>
			<?php if ( $iaoptions['can_send_group_invites_email'] == 'yes' && bp_has_groups( "type=alphabetical&user_id=" . bp_loggedin_user_id() ) ) : ?>
			<li>
				<p><?php _e( '(optional) Select some groups. Invitees will receive invitations to these groups when they join the site.', 'bp-invite-anyone' ) ?></p>
				<ul id="invite-anyone-group-list">
					<?php while ( bp_groups() ) : bp_the_group(); ?>
						<li>
						<input type="checkbox" name="invite_anyone_groups[]" id="invite_anyone_groups[]" value="<?php bp_group_id() ?>" <?php if ( $from_group == bp_get_group_id() || array_search( bp_get_group_id(), $returned_groups) ) : ?>checked<?php endif; ?> />
						<?php bp_group_avatar_mini() ?>
						<?php bp_group_name() ?>

						</li>
					<?php endwhile; ?>

				</ul>

			</li>
			<?php endif; ?>

		<?php endif; ?>

		<?php do_action( 'invite_anyone_addl_fields' ) ?>

	</ol>

	<div class="submit">
		<input type="submit" name="invite-anyone-submit" id="invite-anyone-submit" value="<?php _e( 'Send Invites', 'buddypress' ) ?> " />
	</div>


	</form>
	<?php
	}


?>