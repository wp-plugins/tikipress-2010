<?php
//include all the little admin functions that have nothing to do with setting up meta box / menus etc
include_once( dirname( __FILE__ ) . '/bpt-functions.php' );

/**
 * Register settings group with WordPress settings API.
 *
 */
function bpt_admin_register_settings() {
	register_setting( 'bpt-settings-group', 'bpt', 'bpt_admin_validate' );
}

/**
 * Validation function for register_setting.
 *
 * @param array $new_settings An array with changed settings
 */
function bpt_admin_validate( $new_settings ) {

	//if this is export to PDF page or badges pdf, then just output PDF, no need to return anything.
	if ( $_POST['bpt_pdf'] )
		bpt_pdf($new_settings);
		
	if($_POST['bpt_badges_pdf'])
		bpt_badges_pdf($new_settings,$_FILES);
		
	if($_POST['submit_email'])
		bpt_send_email($_POST);

	if ( is_string( $new_settings ) )
		return get_blog_option( BP_ROOT_BLOG, 'bpt' );

	if ( isset( $new_settings['ticket_category'] ) )
		$new_settings['ticket_category'] = (int) $new_settings['ticket_category'];
		$settings = wp_parse_args( $new_settings, get_blog_option( BP_ROOT_BLOG, 'bpt' ) );	
	if( WP_MULTISITE )
		update_option('ticket_category', $new_settings['ticket_category']);
	return $settings;
}

/**
 * Setup meta boxes, used in backend
 *
 */
function bpt_admin_pages_on_load() {
	// Configure tab
	add_meta_box( 'bpt-admin-metaboxes-config', __( 'Ticketing Category', 'bpt' ), 'bpt_admin_screen_ticketcategory', 'store_page_wpsc-buddypressticketing-settings');
	// Stats tab
	add_meta_box( 'bpt-admin-metaboxes-stats', __( 'Statistics', 'bpt' ), 'bpt_admin_screen_statistics', 'store_page_wpsc-buddypressticketing');
	//Attendees tab
	add_meta_box( 'bpt-admin-metaboxes-attendees', __( 'Attendees', 'bpt' ), 'bpt_admin_screen_attendees', 'store_page_wpsc-buddypressticketing-attendess');
	//Export to pdf tab
	add_meta_box( 'bpt-admin-metaboxes-pdf', __( 'Export to PDF', 'bpt' ), 'bpt_admin_screen_pdf', 'store_page_wpsc-buddypressticketing-pdf');
	///badges
	add_meta_box( 'bpt-admin-metaboxes-pdf', __( 'Create PDF Badges', 'bpt' ), 'bpt_admin_screen_badges', 'store_page_wpsc-buddypressticketing-badges');
	///help page
	add_meta_box( 'bpt-admin-metaboxes-help', __( 'Getting Started', 'bpt' ), 'bpt_admin_screen_help_page', 'store_page_wpsc-buddypressticketing-help');

}


/**
 * bpt_add_dashboard_widgets
 *
 * function for the wp_dashboard_setup widget hook
 * 
 */

function bpt_add_dashboard_widgets() {
	wp_add_dashboard_widget('ticket_sales', 'Ticket Sales', 'bpt_ticket_sales_dashboard_widget');	
} 
add_action('wp_dashboard_setup', 'bpt_add_dashboard_widgets' );

// Dashboard Widget for ticket sales
function bpt_ticket_sales_dashboard_widget() {

	$url ='/wp-admin/';
	$admin_url= get_admin_url();
	
	$product_id = bpt_select_event_dropdown($url);
	$users = bpt_get_registered_users( $product_id, true );
	
	$tickets_sold = bpt_get_quantities_sold($product_id);
	$ticket_total = bpt_get_ticket_total($product_id);
	$remaining_tickets = ($ticket_total - $tickets_sold);
	$class = "";
	
	$html = '<div id="bpt_dashboard_info">';
	$html.= '<br /><br /><span class="btp_dashboard_stat"><strong> Total Tickets: </strong>' . $ticket_total . '</span>';
	$html.= '<span class="btp_dashboard_stat"><strong>Attendees so far: </strong>' . $tickets_sold . '</span>';
	$html.= '<span class="btp_dashboard_stat"><strong>Tickets Remaining: </strong>' . $remaining_tickets . '</span><br /><br />';
	$html.= '<span class="btp_dashboard_stat"><a href="'.$admin_url.'/admin.php?page=wpsc-buddypressticketing&tab=attendees">See a List of Event Attendee\'s</a></span>';
	$html.= '<span class="btp_dashboard_stat"><a href="'.$admin_url.'/admin.php?page=wpsc-buddypressticketing">See All Event Statistics</a></span>';
	$html.= '</div>';
	
	$html.= bpt_display_graph($tickets_sold,$ticket_total,$class);
	
	echo $html;	
} 



/**
*
Remove the menu for event press registrations, we want to use our registration page!
*
*/
function bpt_remove_menu() {
global $menu;

	foreach($menu as $menu_item){
		if($menu_item[2] == "edit.php?post_type=ep_reg"){
			$registration_menu_arr_key = array_keys($menu,$menu_item);
			unset($menu[$registration_menu_arr_key[0]]);
			break;
		}
	}
}
add_action('admin_head', 'bpt_remove_menu');


/**
 * Add "Settings" link on plugins menu 
 * 
 */
function bpt_admin_add_action_link( $actions, $plugin_file, $plugin_data ) {
	if ( 'BuddyPress Ticketing' != $plugin_data['Name'] )
		return $actions;

	$settings_link = '<a href="' . admin_url( 'admin.php?page=wpsc-buddypressticketing' ) . '">' . __( 'Settings', 'bpt' ) . '</a>';
	array_unshift( $actions, $settings_link );

	return $actions;
}
add_filter( 'plugin_action_links', 'bpt_admin_add_action_link', 10, 3 );


/**
 * Admin page for BuddyPress ticketing and all sub menus.
 *
 */
function bpt_admin() {
	$settings = get_blog_option( BP_ROOT_BLOG, 'bpt' );
	
	switch ( $_GET['tab'] ) {
	    case BPT_ADMIN_SETTINGS_SLUG:
	        $is_settings_tab = true;
	        break;
	    case BPT_ADMIN_ATTENDEES_SLUG:
	        $is_attendee_tab = true;
	        break;
	    case BPT_ADMIN_PDF_SLUG:
	        $is_pdf_tab = true;
	        break;
	    case BPT_ADMIN_PDF_BADGES_SLUG:
	        $is_pdf_badges_tab = true;
	        break;
	    case BPT_ADMIN_HELP_SLUG:
	        $is_help_tab = true;
	        break;
	   	default:
	   		$is_statistics_tab = true;
	}	

?>
	<div id="bp-admin">
		<div id="bpt-admin-metaboxes-general" class="wrap">
		
			<div id="bp-admin-header">
				<h3><?php _e( 'BuddyPress', 'bpt' ) ?></h3>
				<h4><?php _e( 'TikiPress', 'bpt' ) ?></h4> &nbsp;
				<?php $TikiLogo_url = plugins_url('images/tikipress-logo.png', __FILE__);
				echo "<img src='" . $TikiLogo_url."' width= '100px' height='50px' alt='TikiPress'/>" ; ?>
			</div>
		
			<div id="bp-admin-nav">
				<ol>
					<li <?php if ( $is_statistics_tab ) echo 'class="current"' ?>>
						<a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing', 'admin' ) ?>"><?php _e( 'Statistics', 'bpt' ) ?></a>
					</li>
					<li <?php if ( $is_settings_tab ) echo 'class="current"' ?>>
						<a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing&amp;tab=' . BPT_ADMIN_SETTINGS_SLUG, 'admin' )  ?>"><?php _e( 'Configure', 'bpt' ) ?></a>
					</li>
					<li <?php if ( $is_attendee_tab ) echo 'class="current"' ?>>
						<a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing&amp;tab=' . BPT_ADMIN_ATTENDEES_SLUG, 'admin' )  ?>"><?php _e( 'Attendees', 'bpt' ) ?></a>
					</li>
					<li <?php if ( $is_pdf_tab ) echo 'class="current"' ?>>
						<a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing&amp;tab=' . BPT_ADMIN_PDF_SLUG, 'admin' )  ?>"><?php _e( 'Create Attendee PDF', 'bpt' ) ?></a>
					</li>
					<li <?php if ( $is_pdf_badges_tab ) echo 'class="current"' ?>>
						<a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing&amp;tab=' . BPT_ADMIN_PDF_BADGES_SLUG, 'admin' )  ?>"><?php _e( 'Create PDF Badges', 'bpt' ) ?></a>
					</li>
					<li <?php if ( $is_help_tab ) echo 'class="current"' ?>>
						<a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing&amp;tab=' . BPT_ADMIN_HELP_SLUG, 'admin' )  ?>"><?php _e( 'Getting started with TikiPress', 'bpt' ) ?></a>
					</li>
		
				</ol>
			</div>
		
			<?php if ( isset( $_GET['updated'] ) ) : ?>
				<div id="message" class="updated">
					<p><?php _e( 'Your TikiPress settings have been updated.', 'bpt' ) ?></p>
				</div>
			<?php endif; ?>
		
			<form enctype="multipart/form-data" method="post" action="options.php" id="wpsc-buddypressticketing">
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ) ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ) ?>
				<?php settings_fields( 'bpt-settings-group' ) ?>
		
				<div id="poststuff" class="metabox-holder">
					<div id="post-body" class="has-sidebar">
						<div id="post-body-content" class="has-sidebar-content">
							<?php
							if ( $is_settings_tab )
								do_meta_boxes( 'store_page_wpsc-buddypressticketing-settings', 'advanced', $settings );
							if ($is_attendee_tab)
								do_meta_boxes( 'store_page_wpsc-buddypressticketing-attendess', 'advanced', $settings );
							if ($is_pdf_tab)
								do_meta_boxes( 'store_page_wpsc-buddypressticketing-pdf', 'advanced', $settings );
							if ( $is_statistics_tab )
								do_meta_boxes( 'store_page_wpsc-buddypressticketing', 'advanced', $settings );
							if ( $is_pdf_badges_tab )
								do_meta_boxes( 'store_page_wpsc-buddypressticketing-badges', 'advanced', $settings );
							if ( $is_help_tab )
								do_meta_boxes( 'store_page_wpsc-buddypressticketing-help', 'advanced', $settings );
							?>
						</div><!-- #bpt-settings-group -->
					</div><!-- #post-body -->
				</div><!-- #poststuff -->
			</form>
		
		</div><!-- #bpt-admin-metaboxes-general -->
	</div><!-- #bp-admin -->
<?php
}


/**
 * Metabox function for wpsc product editing page
 * @param int $product_id Product id
 */
function bpt_add_events_wpec_products_pages( $product  ) { 

	global $wpdb;
	$product_id = $product->id;
	
	if ( absint( $product_id ) ) {
		echo "You have already selected an event for this product changing it is not reccommended if people have already bought tickets!";
	}else{
	echo '<P>Select the Event that this ticket product is for </p>';
	}
	
	$events = bpt_get_all_eventpress_event_details();
	$event_id = get_post_meta( $product_id, '_bpt_event_prod_id', true );

	foreach ( (array) $events as $event ){
		if ( $event->post_status == 'publish' ){ ?> 
			<input type='radio' <?php if ( $event_id === $event->ID ) { echo 'checked="checked"'; } ?> value= "<?php echo $event->ID ?>" name="event_ticket" /> &nbsp; <?php echo $event->post_title . "<br />";
		}		
	}
	echo '<p><small>NOTE: You must create the event before you create the ticket product</small></p>';		
}

/**
 * bpt_wpsc_add_event_wpsc_product_form
 * @param $order - the order the meta box appears in the product add / edit form
 * @return $order - the new order
 * 
 */
function bpt_wpsc_add_event_wpsc_product_form($order) {
	if(array_search('bpt_add_events_wpec_products_pages', $order) === false) {
		$order[] = 'bpt_add_events_wpec_products_pages';
	}	
return $order;
}

/**
 * bpt_wpsc_new_meta_boxes
 * @param $order - the order the meta box appears in the product add / edit form
 * 
 * This is the new way wpec-3.8 adds its meta boxes
 */
function bpt_wpsc_new_meta_boxes() {
	add_meta_box( 'bpt_add_events_wpec_products_pages', 'Buddy Press Tickets', 'bpt_add_events_wpec_products_pages', 'wpsc-product', 'normal', 'high' , 1);
	}
	
	if( (float)WPSC_VERSION < 3.8 ){
		add_filter('wpsc_products_page_forms', 'bpt_wpsc_add_event_wpsc_product_form');
		add_action( 'wpsc_edit_product', 'bpt_admin_submit_product_37', 10, 1 );	
	}else{
		add_action( 'add_meta_boxes', 'bpt_wpsc_new_meta_boxes');
		add_action( 'save_post', 'bpt_admin_submit_product_38', 10, 2 );
	}

/**
 * bpt_save_product_deets
 * wpec3.7 will use this function to save the event / product relationship
 * this has been hacked together for backwards compatiblity
 */
function bpt_admin_submit_product_37(){
	update_post_meta( $_POST['product_id'], '_bpt_event_prod_id', $_POST['event_ticket'] );
}


/**
 * bpt_admin_submit_product
 * wpec 3.8 saves the product details for 3.8
 * 
 */
function bpt_admin_submit_product_38( $post_ID, $post ) {
	global $current_screen, $wpdb;

	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || $current_screen->id != 'wpsc-product' )
		return $post_ID;
		
	update_post_meta( $post_ID, '_bpt_event_prod_id', $_POST['event_ticket'] );
}


/**
 * Metabox for statistics
 * @param array $settings Array containing bpt settings
 */	
function bpt_admin_screen_statistics( $settings ) {
	global $wpdb, $bp;
	?> <div class="float_left"> <?php
	
	$url ='/wp-admin/admin.php?page=wpsc-buddypressticketing&event=';
	$product_id = bpt_select_event_dropdown($url);
	$users = bpt_get_registered_users( $product_id, true );
	
	$tickets_sold = bpt_get_quantities_sold($product_id);
	$ticket_total = bpt_get_ticket_total($product_id);
	$remaining_tickets = ($ticket_total - $tickets_sold);

	$html= '<h2>Ticket stock control</h2>';
	
	if ($ticket_total == 0)
		$html.= '<p> You have not entered a ticket limit for this event, to do so please edit your ticket product. </p>';
	
	$html.= '<p> Sold: (' .$tickets_sold .'/' . $ticket_total . ')</p>';
	$html.= '<p> Remaining Tickets: ' . $remaining_tickets . '</p>';
	$html.= '<p> Total Revenue gathered for this event: $' . $total_revenue . '</p>';
	

	$html.= '<h2>Tiki profile feilds </h2>';
	$html.=  '<a href= "<?php echo get_admin_url();?>admin.php?page=bp-profile-setup">Set up your TikiPress feilds</a><br />
	<p><small>Values of Radio buttons and Drop-down lists will be shown <br />
	 in statistics, other information will be show per user.</small></p>';
	
	$sql = 'SELECT  `data`.`value` ,  `fields`.`name` , COUNT( * ) as count 
			FROM  `' . $bp->table_prefix . 'bp_xprofile_data` `data` 
			JOIN  `' . $bp->table_prefix . 'bp_xprofile_fields` `fields` ON  `data`.`field_id` =  `fields`.`id` 
			WHERE user_id
			IN (' . implode ( ',', $users ) . ') 
			AND  `fields`.`type` 
			IN (
			"radio",  "selectbox",  "checkbox",  "multiselectbox"
			)
			GROUP BY  `data`.`value`';
	$stats = $wpdb->get_results( $sql ) ;
	
	if( $stats ) {
		$statistic = array();
		foreach ( (array) $stats as $stat ) {
			$statistic[$stat->name][] = array( 
				'value' => $stat->value,
				'count' => $stat->count
			);
		}
		while( current( $statistic ) ) {
			$html.=  '<h4>' . key( $statistic ) . '</h4>';
			foreach( (array) $statistic[key($statistic)] as $stat ){
				$stat['value'] = maybe_unserialize( $stat['value'] );
				if ( is_array( $stat['value'] ) ) {
					echo implode( ', ', $stat['value'] );
				} else {
					$html.=  $stat['value'];
				}
				$html.=  ' <strong>(' . $stat['count'] . ')</strong><br />';
			}
			next( $statistic );
		}	
	}else{
		$html.= '<h2>There are no statistics for this event.</h2>';
	}	
	$html.= '</div>';
	
$class="float_left";
$html.= bpt_display_graph($tickets_sold,$ticket_total,$class);
echo $html;

}

/**
 * Metabox for category settings
 * @param array $settings Array containing bpt settings
 */
function bpt_admin_screen_ticketcategory( $settings ) {
	if( WP_MULTISITE )
		$settings = get_option('ticket_category');
	else
		$settings = maybe_unserialize($settings);
		
	$categories = bpt_wpsc_get_categories(); ?>	

	<h2>Ticket Category</h2>
	<p><label for="ticket_category"><?php _e( "Select the category to use for ticketing:", 'bpt' ) ?></label></p>
	
	<select name="bpt[ticket_category]">
		<?php
		foreach ( (array) $categories as $cat ) {
	
			$selected = '';
			if ( $settings['ticket_category'] == $cat['id'] || $settings['ticket_category'] == $cat['term_id'] )
				$selected = "selected='selected'";
			if((float)WPSC_VERSION >= 3.8 )
				echo "<option value='" . $cat['term_id'] . "'" . $selected . ' >' . $cat['name'] . "</option>";
			else
				echo "<option value='" . $cat['id'] . "'" . $selected . ' >' . $cat['name'] . "</option>";
		}
		?>
	</select>
	
	<h2>Tiki Profile Fields</h2>
	<p>The Tiki profile fields enable you to gather statistics and attendee data for your event. We have created some default fields for you but feel free to create your own.
	<a href= "<?php echo get_admin_url();?>admin.php?page=bp-profile-setup">Set up your TikiPress fields</a><br />
	Values of Radio buttons and Drop-down lists will be shown in statistics, other information will be show per user.</p>

	<p><input type="submit" class="button-primary" value="<?php _e( 'Save WP e-Commerce Ticketing settings', 'bpt' ) ?>" /></p>
<?php
}


/**
 * Metabox for attendees list
 * @param array $settings Array containing bpt settings
 */
function bpt_admin_screen_attendees( $settings ) {
	global $wpdb, $bp;
		
		echo '<div class="left">';
		$url ='/wp-admin/admin.php?page=wpsc-buddypressticketing&tab=attendees&event=';
		$product_id = bpt_select_event_dropdown( $url );
		$users = bpt_get_registered_users( $product_id );
		$user_id = $_GET['user'];
		//viewing just one attendee
		if ( isset( $user_id ) ) {
			$user = get_userdata( $user_id );
			$profile_fields=bpt_get_user_profile_data($user_id);
			
			?><h2><?php echo get_avatar( $user->user_email , '50' );?> <?php echo $user->display_name; ?></h2><?php
			
			foreach ( (array) $profile_fields as $field ) {
				$field->value = maybe_unserialize($field->value);
				
				if(is_array($field->value))
					$field->value = implode(',', $field->value);
				
				echo '<strong>' . $field->name . '</strong>: ' . $field->value . '<br /><br />';
			}
		//viewing all attendees for the event
		}else{
			$columns = array( 
			'author' => __( 'Registered User' )
		 );

		if(count($users) > 0)
			bpt_display_attenddee_table($users,$columns);
		else
			echo '<h2>There are no registered attendees for this event yet.</h2> </div>';

        bpt_display_group_email($users);
	}
}


/**
 * Export PDF metabox
 * @param array $new_settings An array with changed settings
 */
function bpt_admin_screen_pdf( $settings, $product_id) {
	global $wpdb;
	$url='/wp-admin/admin.php?page=wpsc-buddypressticketing&tab=pdf&event=';
	$product_id = bpt_select_event_dropdown($url);
	$fields = bpt_get_ticket_fields();  

	foreach ( (array) $fields as $field ) {
		$checked='';
		if ( @in_array( $field->id, $settings['fields'] ) )
			$checked='checked="checked"';
		?>
		<br /><input type="checkbox" name="bpt[fields][]" value="<?php echo $field->id; ?>" <?php echo $checked; ?>/> <?php echo $field->name;
	
	} ?>
	
	<input type="hidden" name="bpt_pdf" value="true" />
	<p><input type="submit" class="button-primary" value="<?php _e( 'Export as PDF', 'bpt' ); ?>" /></p>
<?php
}


/**
 * Badges PDF metabox
 * @param array $new_settings An array with changed settings
 */
function bpt_admin_screen_badges( $settings, $product_id ) {
	global $wpdb, $bp;
	
	$url='/wp-admin/admin.php?page=wpsc-buddypressticketing&tab=badges&event='; 
	$user_details = wp_get_current_user();
	$user_email=$user_details->data->user_email;
	
	/* 	these fields are collected from gravatar or the user account and not the buddypress profile feilds like the event data */
	$extra_fields = array(
	array(name => "Twitter Id", id => 'badges_twitter',),
	array(name => "Site Link", id => 'badges_site',),
	array(name => "Email Address", id => 'badges_email',)
	);		
	$fields= bpt_get_ticket_fields();
	$all_fields = array_merge($extra_fields, $fields);?>
	
	<div class="left" id="badges_options">
		<p><?php _e('To use the badge generator first select the event you would like to create the ticket for, then select which information you would like to use on your badge. You should see a live preview - once your happy with it export it to a pdf', 'bpt');?></p>
		<p><?php $product_id = bpt_select_event_dropdown($url); ?></p><br />
		
		<label for='bpt_badges_pdf'>Upload your logo:</label>
		<input type="file" name="logo_upload" /> 	
		
		<table>
			<tr id="grav">
				<td>Show Gravitar</td>
				<td class="grav_radio"> Yes <input type='radio' name='badges_gravatar' value='1' >  No <input type='radio' checked = 'checked' name='badges_gravatar' value='0' ></td>
			</tr>
			
		<?php
		for($j=0; $j < count($all_fields); $j++){
			
			$select_id = 'Select'.($j+1) ;
			$select_name = "bpt[fields][".$value."]"; ?>
			<tr class="bpt_template_select_row"> <?php
				//if we are accessing grav fields - these three $extrafields always go first in 
				//the array - need to convert them all to objects or standard array options
				if($j < 3){
					echo '<td class="bpt_template_select">'.$all_fields[$j]['name'] . '</td>';
					$value = $all_fields[$j]['id'];
				}else{
					echo '<td class="bpt_template_select">'.$all_fields[$j]->name . '</td>';
					$value = $all_fields[$j]->id;
				}
				$select_name = "bpt[fields][".$value."]"; 
				
				//generate the select boxes and their options
				//for each template posistion ($i) create that option will need to 
				//be not hard coded when more templates are added ?>
				<td class="bpt_template_select"> 
					<select id="<?php echo $select_id ?>" name="<?php echo $select_name ?>" onchange="javascript:SelectBoxes(this);">
						<option value='exclude' selected='selected'>Exclude</option> <?php
						for($i=1; $i < 8; $i++) 
							echo '<option value="' . $i . '"> Template Area ' . $i . '</option>'; ?>
					</select> 
				</td>
			</tr> <?php 
		} 	
			?>		
		</table>
		<input type="hidden" name="bpt_badges_pdf" value="true" />
		<input type="submit" class="button-primary" value="<?php _e( 'Export badges as PDF', 'bpt' ); ?>" />
	</div>
<?php $user_data = generate_user_preview_data(); ?>

	<div id="float_left_img2" class="float_left_img2">
		<p><strong>Your Super Cool Badge Builder</strong></p>
		<p>Below is a preview of your badge and current settings - change the template options to change the look of your badge! Don't forget to export when your happy with it!</p>
		<?php $template_url = plugins_url('templates/bpt/badges/wordcamp-ticket-badge.jpg', __FILE__); ?>
		<div id="template_area" class="template_area">
			<?php
			echo "<img id='background_badge' src='" . $template_url."' alt='badges_template' />" ;
			echo $preview_divs = bpt_preview_divs(); 
			?>
		</div> <!-- template_area -->
	</div> <!-- float_left_img2 -->

	<?php $template_url = plugins_url('templates/bpt/badges/badges_template.jpg', __FILE__); ?>
	<div class="float_left_img">
		<p><strong>Area Guide</strong></p>
		<p>Use the area guide to see the location of the different template positions</p>
		<?php
		echo "<img width='200px' src='" . $template_url."' alt='badges_template' />" ; 
		?>
	</div>
	<div class='clear'></div>
	<?php
 }



/**
 * bpt_admin_screen_help_page
 * Meta Box for bpt help section
 * @todo add some screen shots, update the badges generation
 */
function bpt_admin_screen_help_page(){	
?>
<div id="help">
	<p>
		<h2>Additional plugins needed for TikiPress</h2>
		<ul>
			<li>Please ensure you have all these plugins activated and installed!</li>
			<li><a href="http://wordpress.org/extend/plugins/wp-e-commerce/" target="_blank">WP-e-Commerce </a> at least version 3.7.7</li>
			<li><a href="http://wordpress.org/extend/plugins/eventpress/" target="_blank">EventPress </a> </li>
			<li><a href="http://wordpress.org/extend/plugins/buddypress/" target="_blank">BuddyPress </a> </li>
			<li><a href="http://wordpress.org/extend/plugins/buddypress-custom-posts/" target="_blank">BuddyPress - custom post types </a> </li>
		</ul>
		<br />
	</p>
	

	<h2>Required Settings</h2>
	<p>Please change the following settings to allow for a seamless integration of these plugins.</p>
		<ol>
			<li>Set your  Permalink structure to <strong>/%category%/%postname%/</strong></li>
			<li>Users must be registered to your site to redeems ticket codes so it would be advised to change your wordpress settings to "anyone can register" (you do this under the main wordpress users menu)</li>
			<li>Create a WordPress page with the following shortcode: <strong>[bpt_redeem_code_page]</strong> This is the page that the user will go to when redeeming ticket codes</li>
			<li>Create a Product category in WP-e-Commerce called Tickets</li>
			<li>Go to Store-> TikiPress -> Configure and select the newly created product category for your tickets - All tickets created for sale in WP-e-Commerce must have this category</li>
		</ol>
	
	
	<h2>Optional Settings / Things to note</h2>
		<ol>
			<li> The payment must be marked as accepted in the WP-e-Commerce sales log in order for any redemption codes to be sent.
			</li>
			<li>Some buddy press themes will not support Parent / child menu structure. If this is the case and you only see a products page (and not a checkout page) in your nav then please change the checkout page to have no parent.</li>
			<li>If you would like to display a list of attendees on your site (so other users can see who has registered for the event) then create a page with this shortcode: [bpt_attendees id='productid']
			where product id is the product id for the ticket thats the event relates to. (an easy way to find this is to go to store > products and hover over the ticket in the bar at the bottom you will see the product id)</li>
			<li>If you don't want to use BuddyPress for the front end of your site then you can deactivate the BuddyPress menus. BuddyPress is only required in the backend to assist TikiPress with the management of attendees</li>
			<li>The Profile feilds are used to collect atendee data, you can add extra ones if you like.<br /> Go to BuddyPress -> Profile Field Setup and add any fields you want. Values of Radio buttons and Drop-down lists will be shown in statistics, other information will be show per user.</li>
		</ol>
	
	<h2>Creating an Event - EventPress</h2>
		<ol>
			<li> Go to Events and add a new event, this is the event that you are going to be selling a ticket for.
			</li>
			<li>Fill in all event information, you can include goggle map details and a feature image if you wish</li>
			<li>TikiPress will handle all attendee and registration data so filling out the registration section in EventPress is not required</li>
		</ol>
		
	<h2>Creating a Ticket - WP-e-Commerce</h2>
		<ol>
			<li> Go to Store -> Products -> Add product Fill out all the description details about your ticket and event.			</li>
			<li>Apply your Ticket category</li>
			<li>In the Event Metabox select the event that this ticket relates to</li>
			<li>Set a stock limit for you ticket - This is required in order for the statistics to work</li>
			<li>you can apply any other WP-e-Commerce product settings to your ticket</li>
		</ol>
	
	<h2>Getting to know TikiPress</h2>
	<p>If you followed the above steps then your should have tickets and events created in no time. Below is a quick run down of the TikiPress plugin's menus. </p><br />
	
	<p><strong><a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing', 'admin' ) ?>"><?php _e( 'Statistics', 'bpt' ) ?></a></strong><br />
	The Statistics tab contains all the statistics, grouped by event. Only statistics of registered attendees will be recorded. Use the mailing list to email all the attendees for that event. <br /></p>
	
	<p><strong><a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing&amp;tab=' . BPT_ADMIN_SETTINGS_SLUG, 'admin' )  ?>">Configure</a></strong><br />
	This is where you link your ticket category (created in wp-e-commerce) to your ticket sales - This category must be used for all ticket products. If you want to sell things like merchandise for your event create a different WP-e-Commerce category for these products.<br /></p>
	
	<p><strong><a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing&amp;tab=' . BPT_ADMIN_ATTENDEES_SLUG, 'admin' )  ?>">Attendees</a></strong><br />
	This page is also ordered by event. To view all the details about a user just click on that users name. Like the statistics page only registered users will show.<br /></p>
	
	<p><strong><a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing&amp;tab=' . BPT_ADMIN_PDF_SLUG, 'admin' )  ?>">Create Attendee PDF</a></strong><br />
	This page is used to generate PDF lists of all attendees for any selected event. To create a PDF simply select the event and the fields you require to be generated for the PDF.<br /></p>
	
	<p><strong><a href="<?php echo site_url( 'wp-admin/admin.php?page=wpsc-buddypressticketing&amp;tab=' . BPT_ADMIN_PDF_BADGES_SLUG, 'admin' )  ?>"><?php _e( 'Create PDF Badges', 'bpt' ) ?></a></strong><br />
	This page is used to generate PDF Badges or Tickets for your Attendees. The Areas on the template relate to the position and font size for the badge / ticket. The default settings relate to the colored example on the page. The radio buttons relate to the template areas and the list of information relates to the users. <br />
	Eg: First Name position 7 - this will put the first name of every user in template position 7<br />
	Show Gravatar - YES will show each users avatar in the default position.<br />
	You can also upload your own event logo - this to has a default posistion. <br />
	<br /></p>
	
	<h2>Testing your ticket settings</h2>
		<ol>
			<li> Go to front-end Products page</li>
			<li>Add some tickets to your cart, to test the ticket code redemption you will need to purchase more than one ticket.</li>
			<li>Enter billing/contact details and fill out the ticket information fields</li>
			<li>Enter email addresses for the recipients of the other tickets (for testing enter in an address you have access to), those need to be emails of registered users, if they are not registered, then they must register with the same email before Redeeming the voucher.</li>
			<li>If using manual gateway go to the sales log (store > sales from the admin dashboard) and change the payment status to accepted.</li>
			<li>Check the email for a redemption code, for the other tickets purchased.</li>
			<li>You should now be able to go to the redeem page and redeem your code, you should be asked to fill out the same fields again to register your statistics and options</li>
			<li>Once you have some registered attendees you can use the statistics and attendees features under store >buddyPress ticketing </li>
		</ol>
	
	<p><h2>Support</h2>
		<ul>
			<li>If you have any problems with TikiPress or require more information please check out these links <br /></li>
			<li><a href="http://getshopped.org/resources/docs/">Documentation</a></li>
			<li><a href="Support Forum: http://www.getshopped.org/forums/">Support Forum</a></li>
			<li><a href="http://getshopped.org/resources/premium-support/">Premium Support Forum</a></li>
			
			<li>Please Note: that we will do our best to assist you with any problems relating to TikiPress, however general questions relating to the other plugins eg EventPress / BuddyPress We will not be able to help you with, you would need to talk to the core developers for that plugin </li>
		</ul>
	</p>
</div>
	<?php
}	
?>