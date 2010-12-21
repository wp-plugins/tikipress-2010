<?php
/**
 * bpt_add_admin_css()
 *
 * Adds CSS to admin pages.
 */
function bpt_add_admin_css() {
	wp_enqueue_script( 'bpt-js', plugins_url( '/js/admin.js', __FILE__ ), array(), BPT_VERSION );
	wp_enqueue_style( 'bpt-admin-css', plugins_url( '/css/admin.css', __FILE__ ), array(), BPT_VERSION );
	wp_enqueue_style( 'bpt-bpstyles', plugins_url( '/css/bpstyles.css', __FILE__ ), array(), BPT_VERSION );
}
add_action( 'admin_init', 'bpt_add_admin_css' );

function bpt_add_css() {
	global $bp;

	if ( BPT_TICKETS_SLUG != $bp->current_component )
		return;
	wp_enqueue_style( 'bpt-ticketing', plugins_url( '/css/ticketing.css', __FILE__ ), array(), BPT_VERSION );
	wp_print_styles();
}
add_action( 'wp_head', 'bpt_add_css' );

/* attendee css has its own page as its a shortcode and could need to be loaded from anywhere */
function bpt_add_attendee_css() {
	global $bp;
	wp_enqueue_style( 'bpt-attendee', plugins_url( '/css/attendee.css', __FILE__ ), array(), BPT_VERSION );
	wp_print_styles();
}
add_action( 'wp_head', 'bpt_add_attendee_css' );
?>