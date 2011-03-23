<?php
 /**
  * Some WP filters you may want to use:
  *  - wp_filter_kses() VERY IMPORTANT see below.
  *  - wptexturize()
  *  - convert_smilies()
  *  - convert_chars()
  *  - wpautop()
  *  - stripslashes_deep()
  *  - make_clickable()
  */
add_filter( 'bpt_registration_redemption_code', 'wp_filter_kses', 1 );
add_filter( 'bpt_registration_redemption_code', 'stripslashes_deep' );

add_filter( 'bpt_members_ticketing_history_name', 'wp_filter_kses', 1 );
add_filter( 'bpt_members_ticketing_history_name', 'wptexturize' );
add_filter( 'bpt_members_ticketing_history_name', 'convert_chars' );
add_filter( 'bpt_members_ticketing_history_name', 'stripslashes_deep' );

add_filter( 'bpt_checkout_ticketing_email', 'wp_filter_kses', 1 );
add_filter( 'bpt_checkout_ticketing_email', 'wptexturize' );
add_filter( 'bpt_checkout_ticketing_email', 'convert_chars' );
add_filter( 'bpt_checkout_ticketing_email', 'stripslashes_deep' );

add_filter( 'bpt_get_product_name', 'wp_filter_kses', 1 );
add_filter( 'bpt_get_product_name', 'wptexturize' );
add_filter( 'bpt_get_product_name', 'convert_chars' );
add_filter( 'bpt_get_product_name', 'stripslashes_deep' );

//add_filter('wpsc_products_page_forms', 'bpt_add_events_wpec_products_pages');


?>