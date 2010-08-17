<?php

function bp_console_action_filter( $action, $raw ) {
	$return = $raw->action;

	$return .= ' ';

	$return .= '<span class="time-since">' . sprintf( __( '%s ago', 'buddypress' ), bp_core_time_since( $raw->date_recorded ) ) . '</span>';

	$return .= ' &middot; ';

	$return .= '<a href="' . bp_activity_get_permalink( $raw->id ) . '">View</a>';

	$return .= ' &middot; ';

	return $return;
}
add_filter( 'bp_get_activity_action', 'bp_console_action_filter', 10, 2 );
?>