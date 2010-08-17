<?php
/*
Plugin Name: BP Console
Plugin URI: http://teleogistic.net/code/buddypress/bp-console
Description: A personalizable console for BuddyPress.
Author: boonebgorges
Version: 0.1
Network: true
Author URI: http://teleogistic.net
*/

function bp_console_init() {
	require( dirname( __FILE__ ) . '/bp-console.php' );
}
add_action( 'bp_include', 'bp_console_init' );
?>