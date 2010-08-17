<?php

class BP_Console_Sections_Template {
	var $current_section = -1;
	var $section_count;
	var $total_section_count;
	var $sections;
	var $section;

	var $in_the_loop = false;

	function bp_console_sections_template( $max, $start ) {
		global $bp;

		// First try to get the user's settings
		$cells = get_user_meta( $bp->loggedin_user->id, 'bp_console_cells' );

		// If the user hasn't set anything, look for the admin-set defaults
		if ( empty( $cells ) ) {
			// If the admin hasn't set defaults, use the plugin defaults. Todo: move this to an activation hook
			if ( !$cells = get_option( 'bp_console_defaults' ) )
				$cells = array(
					1 => array(
						'type' => 'activity',
						'max_items' => 10
					),
					2 => array(
						'type' => 'activity',
						'max_items' => 5
					),
					3 => array(
						'type' => 'activity',
						'max_items' => 2
					),
					4 => array(
						'type' => 'activity',
						'max_items' => 8
					),
				);
		}

		$this->total_section_count = count( $cells );

		if ( !isset( $max ) )
			$max = $this->total_section_count;

		if ( !isset( $start ) )
			$start = 1;

		$sections = array();
		for ( $i = $start; $i <= $max; $i++ ) {
			$sections[] = $cells[$i];
		}

		$this->section_count = count( $sections );

		$this->sections = $sections;
	}

	function has_sections() {
		if ( $this->section_count )
			return true;

		return false;
	}

	function next_section() {
		$this->current_section++;
		$this->section = $this->sections[$this->current_section];

		return $this->section;
	}

	function rewind_sections() {
		$this->current_section = -1;
		if ( $this->section_count > 0 ) {
			$this->section = $this->sections[0];
		}
	}

	function user_sections() {
		if ( $this->current_section + 1 < $this->section_count ) {
			return true;
		} elseif ( $this->current_section + 1 == $this->section_count ) {
			do_action('section_loop_end');
			// Do some cleaning up after the loop
			$this->rewind_sections();
		}

		$this->in_the_loop = false;
		return false;
	}

	function the_section() {
		global $section;

		$this->in_the_loop = true;
		$this->section = $this->next_section();

		if ( is_array( $this->section ) )
			$this->section = (object) $this->section;

		if ( $this->current_section == 0 ) // loop has just started
			do_action('section_loop_start');
	}
}


function bp_console_sections() {
	global $sections_template;
	return $sections_template->user_sections();
}

function bp_console_the_section() {
	global $sections_template;
	return $sections_template->the_section();
}

function bp_console_has_sections( $max, $start ) {
	global $sections_template;

	$sections_template = new BP_Console_Sections_Template( $max, $start );
print_r($sections_template);

	return apply_filters( 'bp_console_has_sections', $sections_template->has_sections(), &$sections_template );
}

function bp_console_section( $section_id = 1 ) {

	// First try to get the user's settings
	$cells = get_user_meta( $bp->loggedin_user->id, 'bp_console_cells' );

	// If the user hasn't set anything, look for the admin-set defaults
	if ( empty( $cells ) ) {
		// If the admin hasn't set defaults, use the plugin defaults. Todo: move this to an activation hook
		if ( !$cells = get_option( 'bp_console_defaults' ) )
			$cells = array(
				1 => array(
					'type' => 'activity',
					'max_items' => 10
				),
				2 => array(
					'type' => 'activity',
					'max_items' => 5
				),
				3 => array(
					'type' => 'activity',
					'max_items' => 2
				),
				4 => array(
					'type' => 'activity',
					'max_items' => 8
				),
			);
	}

	$section_type = $cells[$section_id]['type'];

	$section_template = locate_template( array( 'console/' . $section_type . '-section.php' ), false );

	if ( $section_template )
		include( locate_template( array( 'console/' . $section_type . '-section.php' ), false ) );
	else
		include( WP_PLUGIN_DIR . '/bp-console/templates/' . $section_type . '-section.php' );

}

function bp_console_col_one_content() {

}


function bp_console_col_two_content() {
	echo "col two";
}


function bp_console_activity_args( $cell = 1 ) {
	global $bp;

	$cells = get_user_meta( $bp->loggedin_user->id, 'bp_console_cells' );

	if ( !isset( $cells[$cell] ) )
		$args = '';
	else
		$args = $cells[$cell];

	$object = false;
	$action = false;
	$primary_id = false;
	$secondary_id = false;

	$defaults = array(
		'per_page' => 6, // number of items per page
		'max' => false, // max number to return

		/* Scope - pre-built activity filters for a user (friends/groups/favorites/mentions) */
		'scope' => '',

		/* Filtering */
		'user_id' => $user_id, // user_id to filter on
		'object' => $object, // object to filter on e.g. groups, profile, status, friends
		'action' => false, // action to filter on e.g. activity_update, new_forum_post, profile_updated
		'primary_id' => $primary_id, // object ID to filter on e.g. a group_id or forum_id or blog_id etc.
		'secondary_id' => false, // secondary object ID to filter on e.g. a post_id

		/* Searching */
		'search_terms' => false // specify terms to search on
	);

	$r = wp_parse_args( $args, $defaults );

	foreach ( $r as $key => $v ) {
		if ( $v == '' )
			unset( $r[$key] );
	}

	return $r;
}



?>