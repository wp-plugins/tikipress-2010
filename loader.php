<?php
/*
Plugin Name: TikiPress
Version: 1.1
Requires at least: WP 3.0, BuddyPress 1.2.5
Tested up to: WP 3.03, BuddyPress 1.2.6
Author: Getshopped.org, Paul Gibbs
Author URI:http://getshopped.org http://byotos.com
Description: A plugin that combines wp-e-Commerce and BuddyPress allowing people to create events, gather statisics and sell tickets as products.
Site Wide Only: true
Network: true
Domain Path: /includes/languages/
Text Domain: bpt
*/

/* Only load the component if BuddyPress is loaded and initialized. */
function bpt_init() {
	global $wpdb, $bp;

	require( dirname( __FILE__ ) . '/includes/bpt-core.php' );
	// TODO: do install/upgrade properly.
	if ( !get_blog_option( BP_ROOT_BLOG, 'bpt' ) ){
		if ( !$field_group_id = xprofile_insert_field_group( array( 'name' => __( 'Ticketing Fields', 'bpsc' ) ) ) )
			return;
		
		$ticketing_fields = array(
			array( 
				'type'			=> 'radio',
				'name'			=> __( 'Gender', 'bpt' ),
				'description'	=> false ,
				'values'	 	=> array( __( 'M', 'bpt' ), __( 'F', 'bpt' ) ),
				'can_delete'	=> 1,
				'is_required'	=> 1
			),
			array( 
				'type'			=> 'selectbox',
				'name'			=> __( 'T-Shirt Size', 'bpt' ),
				'description'	=> false ,
				'values'	 	=> array( __( 'Mens Small', 'bpt' ), __( 'Mens Medium', 'bpt' ), __( 'Mens Large', 'bpt' ), __( 'Mens X Large', 'bpt' ), __( 'Womens Small', 'bpt' ), __( 'Womens Medium', 'bpt' ), __( 'Womens Large', 'bpt' ), __( 'Womens X Large', 'bpt' ) ),
				'can_delete'	=> 1,
				'is_required'	=> 1
			),
			array(
				'type'			=> 'selectbox',
				'name'			=> __( 'Meal Restrictions', 'bpt' ),
				'description'	=> false,
				'values'		=> array( __( 'none', 'bpt' ), __( 'Vegan', 'bpt' ), __( 'Vegetarian', 'bpt' ), __( 'Gluten Free', 'bpt' ) ),
				'can_delete'	=> 1,
				'is_required'	=> 1
			),
			array(
				'type'			=> 'selectbox',
				'name'			=> __( 'How long have you been using WordPress', 'bpt' ),
				'description'	=> false,
				'values'		=> array( __( 'never', 'bpt' ), __( 'less than 6 mnths', 'bpt' ), __( '6-12mnths', 'bpt' ), __( '1yr', 'bpt' ), __( '2yrs', 'bpt' ), __( '3yrs', 'bpt' ), __( '4yrs', 'bpt' ), __( '5yrs', 'bpt' ), __( '6yrs', 'bpt' ) ),
				'can_delete'	=> 1,
				'is_required'	=> 1
			),
			array(
				'type'			=> 'selectbox',
				'name'			=> __( 'How many WordPress blogs do you manage', 'bpt' ),
				'description' 	=> false,
				'values'		=> array( __( 'none', 'bpt' ), __( '1 - 2', 'bpt' ), __( '3 - 5', 'bpt' ), __( '6 - 10', 'bpt' ), __( '11 - 20', 'bpt' ), __( '20 +', 'bpt' ) ),
				'can_delete'	=> 1,
				'is_required'	=> 1
			),
			array(
				'type'			=> 'selectbox',
				'name'			=> __( 'Which sessions are you most likely to attend', 'bpt' ),
				'description' 	=> false,
				'values'		=> array( __( 'Blogger', 'bpt' ), __( 'BeginnerDev', 'bpt' ), __( 'AdvancedDev', 'bpt' ), __( 'CMS', 'bpt' ), __( 'Academic', 'bpt' ), __( 'BuddyPress', 'bpt' ), __( 'opensource', 'bpt' ) ),
				'can_delete'	=> 1,
				'is_required'	=> 1
			),
			array(
				'type'			=> 'selectbox',
				'name'			=> __( 'How would you describe yourself', 'bpt' ),
				'description' 	=> __( 'if more than one, choose the one that will influence your choice of sessions at this WordCamp', 'bpt' ),
				'values'		=> array( __( 'Personal Blogger', 'bpt' ), __( 'Corporate Blogger', 'bpt' ), __( 'Plugin Developer', 'bpt' ), __( 'Theme Developer', 'bpt' ), __( 'Theme Designer (no coding)', 'bpt' ), __( 'System Admin', 'bpt' ), __( 'Core Contributor', 'bpt' ), __( 'Forum Moderator', 'bpt' ), __( 'BuddyPress/MU', 'bpt' ), __( 'Open Source Community', 'bpt' ) ) ,
				'can_delete'	=> 1,
				'is_required'	=> 1
			),
			array(
				'type'			=> 'selectbox',
				'name'			=> __( 'Do you make money from your WordPress Website', 'bpt' ),
				'description' 	=> false,
				'values'		=> array( __( 'None', 'bpt' ), __( 'Day Job', 'bpt' ), __( 'Support', 'bpt' ), __( 'Custom Development', 'bpt' ), __( 'Hosting', 'bpt' ), __( 'Ads', 'bpt' ), __( 'E-Commerce', 'bpt' ), __( 'Themes', 'bpt' ), __( 'Plugins', 'bpt' ) ),
				'can_delete'	=> 1,
				'is_required'	=> 1
			)
		);
		
		$counter = 3;
		
		foreach ($ticketing_fields as $field ) {
			$_POST[$field['type'].'_option'] = $field['values'];

			$field_id = xprofile_insert_field( array( 'field_group_id' => $field_group_id, 'type' => $field['type'], 'name' => $field['name'], 'description' => $field['description'], 'is_required' => 0, 'can_delete' =>  $field['can_delete'], 'field_order' => $counter, 'is_required'	=> $field['is_required'] ) );
			$counter++;
		}
		
		$wpdb->query('INSERT INTO '.$wpdb->prefix.'bp_xprofile_fields VALUES ("", "'.$field_group_id.'", 0, "textbox", "'.__( 'Last name', 'bpt' ).'", "", 1, 0, 0, 0, "", 0 ), ("", "'.$field_group_id.'", 0, "textbox", "'.__( 'First name', 'bpt' ).'", "", 1, 0, 0, 0, "", 0 )');
		
		update_blog_option( BP_ROOT_BLOG, 'bpt', array( 'is_installed' => true, 'fields_group_id' => $field_group_id ) );
	}
}
add_action( 'bp_init', 'bpt_init' );
?>