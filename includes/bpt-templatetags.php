<?php
function bpt_is_member_redeem_page() {
	global $bp;

	return apply_filters( 'bpt_is_member_redeem_page', ( $bp->current_component == BPT_TICKETS_SLUG && $bp->current_action == BPT_REDEEM_SLUG ) );
}

function bpt_is_member_tickets_page() {
	global $bp;

	return apply_filters( 'bpt_is_member_tickets_page', ( $bp->current_component == BPT_TICKETS_SLUG && $bp->current_action == BPT_TICKETS_HISTORY_SLUG ) );
}


// Convenience

function bpt_get_ticketing_category() {

	if( WP_MULTISITE ){
		$settings = get_option('ticket_category');
		return apply_filters( 'bpt_get_ticketing_category', $settings);
	}else{
		$settings = maybe_unserialize( get_blog_option( BP_ROOT_BLOG, 'bpt' ) );
		return apply_filters( 'bpt_get_ticketing_category', $settings['ticket_category'] );
		}
}
?>