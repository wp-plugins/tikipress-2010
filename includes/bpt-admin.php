<?php
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
*
Remove the menu for event press registrations, we want to use our registration page!
*
*/
function remove_menu() {
	global $menu;

		if (class_exists('ep_admin_menus')) {
		unset($menu[31]);
		}
	}
add_action('admin_head', 'remove_menu');

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
//exit (print_r($_POST,1));
	//if this is export to PDF page or badges pdf, then just output PDF, no need to return anything.
	if ( $_POST['bpt_pdf'] )
		bpt_pdf($new_settings);
		
	if($_POST['bpt_badges_pdf'])
		bpt_badges_pdf($new_settings);
		
	if($_POST['submit_email'])
		bpt_send_email($_POST);

	if ( is_string( $new_settings ) )
		return get_blog_option( BP_ROOT_BLOG, 'bpt' );

	if ( isset( $new_settings['ticket_category'] ) )
			$new_settings['ticket_category'] = (int) $new_settings['ticket_category'];
					
	return wp_parse_args( $new_settings, get_blog_option( BP_ROOT_BLOG, 'bpt' ) );
}


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
 * Admin page for BuddyPress ticketing.
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
				<p><?php _e( 'Your WP e-Commerce Ticketing settings have been saved.', 'bpt' ) ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php" id="wpsc-buddypressticketing">
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
					</div>

					<?php if ( $is_settings_tab ) : ?>
						<p><input type="submit" class="button-primary" value="<?php _e( 'Save WP e-Commerce Ticketing settings', 'bpt' ) ?>" /></p>
					<?php endif ?>
				</div>
			</div>
		</form>

	</div><!-- #bpt-admin-metaboxes-general -->
	</div><!-- #bp-admin -->
<?php
}


/**
 * Metabox for category settings
 * @param array $settings Array containing bpt settings
 */
function bpt_admin_screen_ticketcategory( $settings ) {
	$categories = bpt_wpsc_get_categories();
	$settings = maybe_unserialize($settings);
?>	<h2>Ticket Category</h2>
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
	
	<h2>Tiki Profile Feilds</h2>
	<p>The Tiki profile fields enable you to gather statistics and attendee data for your event. W have created some default fields for you but feel free to create your own.
	<a href= "<?php echo get_admin_url();?>admin.php?page=bp-profile-setup">Set up your TikiPress feilds</a><br />
	Values of Radio buttons and Drop-down lists will be shown in statistics, other information will be show per user.</p>
<?php

}

/**
 * Metabox for statistics
 * @param array $settings Array containing bpt settings
 */	
function bpt_admin_screen_statistics( $settings ) {
	global $wpdb;
	
	echo'<div class="float_left">';
	$url ='/wp-admin/admin.php?page=wpsc-buddypressticketing&event=';
	$product_id = bpt_select_event_dropdown($url);
	$users = bpt_get_registered_users( $product_id, true );

	if((float)WPSC_VERSION >= 3.8 )
		$ticket_total = get_post_meta($product_id, '_wpsc_stock', true); 
	else
	{
		$sql=  'SELECT `quantity` FROM `'.$wpdb->prefix . 'wpsc_product_list` WHERE `id` = '.$product_id[0];
		$ticket_total = $wpdb->get_results( $sql ) ;
		$ticket_total = $ticket_total[0]->quantity;
	}
	
	$tickets_sold = bpt_get_quantities_sold($product_id);
	$remaining_tickets = ($ticket_total - $tickets_sold);

	//echo $product_id;
	echo '<h2>Ticket stock control</h2>';
	
	if ($ticket_total == 0)
		echo '<p> You have not entered a ticket limit for this event, to do so please edit your ticket product. </p>';
	
	echo '<p> Sold: (' .$tickets_sold .'/' . $ticket_total . ')</p>';
	echo '<p> Remaining Tickets: ' . $remaining_tickets . '</p>';
	

	?> 	<h2>Tiki profile feilds </h2>
	<a href= "<?php echo get_admin_url();?>admin.php?page=bp-profile-setup">Set up your TikiPress feilds</a><br />
	<p><small>Values of Radio buttons and Drop-down lists will be shown <br />
	 in statistics, other information will be show per user.</small></p>
	<?php
	$sql = 'SELECT  `data`.`value` ,  `fields`.`name` , COUNT( * ) as count 
			FROM  `' . $wpdb->prefix . 'bp_xprofile_data` `data` 
			JOIN  `' . $wpdb->prefix . 'bp_xprofile_fields` `fields` ON  `data`.`field_id` =  `fields`.`id` 
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
			echo '<h4>' . key( $statistic ) . '</h4>';
			foreach( (array) $statistic[key($statistic)] as $stat ){
				$stat['value'] = maybe_unserialize( $stat['value'] );
				if ( is_array( $stat['value'] ) ) {
					echo implode( ', ', $stat['value'] );
				} else {
					echo $stat['value'];
				}
				echo ' <strong>(' . $stat['count'] . ')</strong><br />';
			}
			next( $statistic );
		}	
	} else {
		echo '<h2>There are no statistics for this event.</h2>';
	}	
	echo '</div>';
	
/* 	This graph came from wp event ticketing */

		echo '<div class="float_left" id="attendeeGraph">';
		echo '<img src="http://chart.apis.google.com/chart?chs=300x150&cht=p3&chd=t:'.number_format($tickets_sold/1000,3).','.number_format(($ticket_total-$tickets_sold)/1000,3).'&chdl=Sold|Left&chp=0.628&chl=' . $tickets_sold . '|' . ($ticket_total - $tickets_sold) . '&chtt=Attendance">';
		echo '</div>';
		echo '<div class="clear"> </div>';
}


/**
 * Metabox for attendees list
 * @param array $settings Array containing bpt settings
 */
function bpt_admin_screen_attendees( $settings ) {
		global $wpdb;
		
		//this url is used in the function select drop down list
		$url ='/wp-admin/admin.php?page=wpsc-buddypressticketing&tab=attendees&event=';
	
		if ( isset( $_GET['user'] ) ) {
			$user = get_userdata( $_GET['user'] );
			$sql = 'SELECT `fields`.`name`, `data`.`value` FROM `' . $wpdb->prefix . 'bp_xprofile_data` `data` INNER JOIN `' . $wpdb->prefix . 'bp_xprofile_fields` `fields` ON `data`.`field_id` = `fields`.`id` WHERE `data`.`user_id`=' . absint( $_GET['user'] ) . ' AND `fields`.`name` != "Name"';
			$profile_fields = $wpdb->get_results( $sql ); ?>
			
			<h2><?php echo get_avatar( $user->user_email , '50' );?> <?php echo $user->display_name; ?></h2><?php
			foreach ( (array) $profile_fields as $field ) {
				$field->value = maybe_unserialize($field->value);
				if(is_array($field->value)){
					$field->value = implode(',', $field->value);
				}
				echo '<strong>' . $field->name . '</strong>: ' . $field->value . '<br /><br />';
			}
		} else {
			$columns = array( 
			'author' => __( 'Registered User' )
		 );
		 
	echo '<div class="left">';
	
		$product_id = bpt_select_event_dropdown($url);
		$users=bpt_get_registered_users( $product_id );
		

		if(count($users) > 0){
			register_column_headers( 'attendees' ,$columns ); ?>
		<table class="widefat fixed" cellspacing="0">
			<thead>
				<tr class="thead">
					<?php print_column_headers( 'attendees' ); ?>
				</tr>
			</thead>		
			<tbody id="users" class="list:user user-list">
			<?php
				$style = '';
					foreach ( (array) $users as $u ) {
						if ( $style ) {
							$style = ' class="alternate"';
						} else {
							$style = '';
						}
						?>
						<tr <?php echo $style; ?>>
							<td class="username column-username">
								<?php echo get_avatar( $u->user_email , '32' ); ?> <a href="<?php echo get_admin_url( null, 'admin.php?page=wpsc-buddypressticketing&tab=attendees&user='.$u->ID, 'admin' ); ?>"><?php echo $u->display_name; ?></a>
							</td>
						</tr>
						<?php
					} 
					?>
	
			</tbody>
			<tfoot>
				<tr class="thead">
					<?php print_column_headers( 'attendees', false ); ?>
				</tr>
			</tfoot>
		</table>
		</div><?php
		}else{
			echo '<h2>There are no registered attendees for this event yet.</h2>';
		}?>
		<div class="left">
	<h4>Mailing List</h4> 
	<p>These are the registered attendees who will receive the email.</p>
	<?php
	foreach ($users as $user)
		echo $user->user_email .', ';
		
		echo '<h4> Send a group email to all registered attendees </h4>';	
		echo '<table>';
		echo '<form action="" method="post">';
		echo '<input type="hidden" name="attendeeNotificationNonce" id="attendeeNotificationNonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';

		echo '<tr valign="top">';
		echo '<th scope="row"><label for="email_attendees_subject">Subject:</label></th>';
		echo '<td><input name="email_attendees_subject" size="80" type="text" value=""></td></tr>';
		echo '<tr valign="top">';
		echo '<th scope="row"><label for="email_attendees_body">Message:</th>';
		echo '<td><textarea rows="10" cols="80" name="email_attendees_body"></textarea></td></tr>';
		echo '<tr><td></td><td><input class="button-primary" type="submit" name="submit_email" value="Send Notification"></td></tr>';
		echo '</form>';
		echo '</table>';
		
echo '</div>';
//echo '<div class="clear_float"></div>';

	
	}
	
	echo '<div class="clear_float"></div>';

}
/* Send a group email from the attendees page */
function bpt_send_email($_POST){

	if (wp_verify_nonce($_POST['attendeeNotificationNonce'], plugin_basename(__FILE__)))
		{
		$event_id = $_POST['events_dropdown'];
		$product_id = bpt_wpsc_get_product_id_from_event( $event_id);
		$users = bpt_get_registered_users( $product_id );

			$i=0;
			foreach ($users as $user){
				$bccEmail[$i] = $user->user_email;
			$i++; }
				
				for($i=0; $i<=count($users); $i++)
					$headers = 'Bcc: ' . $bccEmail[$i]."\r\n";
					$headers .= 'To: '. $_POST['sender'] . "\r\n";

			//WP -always- quotes so we have to -always- stripslashes
			$body = stripslashes($_POST["email_attendees_body"]);
			$subject = stripslashes($_POST["email_attendees_subject"]);

			wp_mail($bccEmail, $subject , $body , $headers);

		}
}


/* TikiPress Help menu - pehapes add some images */
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


/**
 * Adds metabox to wpsc product editing page
 *
 */
function bpt_add_events_wpec_products_page() {
	add_meta_box( "bpt_wpsc_product_event", __( 'BuddyPress Tickets', 'wpsc' ), "bpt_add_events_wpec_products_pages", "wpsc" );
	do_meta_boxes( 'wpsc', 'advanced', null ); 
}
		

/**
 * Metabox function for wpsc product editing page
 * @param int $product_id Product id
 */
function bpt_add_events_wpec_products_pages( $product_id ) { 
	global $wpdb;

	if ( absint( $product_id ) ) {
		update_post_meta( $product_id, '_bpt_event_prod_id', $_POST['event_ticket'] );
		return true;
	}
	
		$sql = "SELECT `posts`.`ID`, `posts`.`post_title`, `posts`.`post_status` FROM `" . $wpdb->prefix . "posts` `posts` WHERE `posts`.`post_type` = 'ep_event'";
		$events = $wpdb->get_results( $sql ) ;
		$event_id = get_post_meta( absint( $_REQUEST['product_id'] ), '_bpt_event_prod_id', true );
		
		echo '<P>Select the Event that this ticket product is for </p>';
		
			foreach ( (array) $events as $event ){
				if ( $event->post_status == 'publish' ){
				?> <input type='radio' <?php if ( $event_id === $event->ID ) { echo 'checked="checked"'; } ?> value= "<?php echo $event->ID ?>" name="event_ticket" /> &nbsp; <?php echo $event->post_title . "<br />";
			
				}		
			}
		echo '<p><small>NOTE: You must create the event before you create the ticket product</small></p>';		
}

add_action( 'wpsc_edit_product', 'bpt_add_events_wpec_products_pages', 10, 1 );			

/**
 * Get ticket fields
 * Selects all the buddy press profile fields that are used for tickets
 */
function bpt_get_ticket_fields() {
	global $wpdb;
	$fields=$wpdb->get_results( 'SELECT `fields`.`name`, `fields`.`id` FROM `' . $wpdb->prefix . 'bp_xprofile_fields` `fields` WHERE `fields`.`parent_id`=0' );

	return $fields;
}

/**
 * Export PDF metabox
 * @param array $new_settings An array with changed settings
 */
function bpt_admin_screen_pdf( $settings, $product_id) {
	global $wpdb;
	$url='/wp-admin/admin.php?page=wpsc-buddypressticketing&tab=pdf&event=';
	$product_id = bpt_select_event_dropdown($url); ?>
	<form name="bpt_pdf" method="post" enctype="multipart/form-data"  action="">
	
		<?php $fields = bpt_get_ticket_fields();  
		
		foreach ( (array) $fields as $field ) {
			$checked='';
			if ( @in_array( $field->id, $settings['fields'] ) )
				$checked='checked="checked"';
		?>
		<br /><input type="checkbox" name="bpt[fields][]" value="<?php echo $field->id; ?>" <?php echo $checked; ?>/> <?php echo $field->name;
		
		} ?>
	
		<input type="hidden" name="bpt_pdf" value="true" />
		<p><input type="submit" class="button-primary" value="<?php _e( 'Export as PDF', 'bpt' ); ?>" /></p>
	 </form>
<?php
}

/**
 * Badges PDF metabox
 * @param array $new_settings An array with changed settings
 */
function bpt_admin_screen_badges( $settings, $product_id ) {
	global $wpdb;
	$url='/wp-admin/admin.php?page=wpsc-buddypressticketing&tab=badges&event='; ?>
	
	<div class="float_left" id="badges_options">
	<?php $product_id = bpt_select_event_dropdown($url); ?>
		<br /><p><?php _e('Select the attendee data you would like to display on your ticket / badge, the template position number relates to the template. If you don\'t want to use all the template areas then leave them blank' , 'bpt');?></p>

<table>
	<form name="bpt_badges_pdf" method="post" enctype="multipart/form-data"  action="">
	
	<tr>
		<td><label for 'bpt_badges_pdf'>Upload your logo:</label></td>
		<td> <input type="file" id="fUpload" name="fUpload" />
	</tr>
	
	<tr>
		<td>Show Gravitar</td>
		<td class='badges_pdf'>Yes<input type='radio' checked = 'checked' name='badges_gravatar' value='1' >No<input type='radio' name='badges_gravatar' value='0' ></td>
	</tr>
	
	<?php
/* 	these fields are collected from gravatar or the user account and not the buddypress profile feilds like the event data */
	$extra_fields = array(
	   array(name => "Twitter Id", id => 'badges_twitter',),
	   array(name => "Site Link", id => 'badges_site',),
	   array(name => "Email Address", id => 'badges_email',)
	);
	
	foreach ((array)$extra_fields as $field ) {
	?>	
		<tr>
			<?php echo '<td>'.$field[name] . '</td>';
			$value= $field[id];
			
			for($i=1; $i < 8; $i++){
				if($i == 1)
					echo'<td class="badges_pdf">';
				/* 	set the default settings as per the eg template */
				if ($field['id'] == 'badges_twitter' && $i == 3)
					echo "$i<input checked = 'checked' type='radio' name='bpt[fields][".$i."]' value='".$value."'> ";
					
				elseif ($field['id'] == 'badges_email' && $i == 4)
					echo "$i<input checked = 'checked' type='radio' name='bpt[fields][".$i."]' value='".$value."'> ";
					
				elseif ($field['id'] == 'badges_site' && $i == 5)
					echo "$i<input checked = 'checked' type='radio' name='bpt[fields][".$i."]' value='".$value."'> ";
				else
					echo "$i<input type='radio' name='bpt[fields][".$i."]' value=' ".$value."' > ";
				if($i == 7)
					echo'</td></div>';
			}
		echo '</tr>';
	 } 
	// get the extra ticket feilds from the bp profile data
	$fields= bpt_get_ticket_fields();
	 
	foreach ((array) $fields as $field ) {
		$selected='';?>
			<tr>
				<?php echo '<td>'.$field->name . '</td>';
				$value= $field->id;
				create_badges_radio_button($value, $settings, $field);
			echo'</tr>';
	} ?>
		
		<input type="hidden" name="bpt_badges_pdf" value="true" />
		<p><input type="submit" class="button-primary" value="<?php _e( 'Export badges as PDF', 'bpt' ); ?>" /></p>
		</form>
	</table>
</div>

<div class="float_left_img">
	<p><strong>Badges Template</strong></p>
	<?php $template_url = plugins_url('templates/bpt/badges/badges_template.jpg', __FILE__);
	echo "<img src='" . $template_url."' alt='badges_template'/>" ; ?>
</div>

<div class="float_left_img">
	<p><strong>Example using the default settings</strong></p> 
	
	<?php $example_url = plugins_url('templates/bpt/badges/wordcamp-ticket-badge.jpg', __FILE__);
	echo "<img src='" . $example_url."' alt='badges_template'/>" ; ?>
</div>

<div class='clear'></div>

<?php }

/* Generates Radio buttons based on field id's */
function create_badges_radio_button($value, $settings,$field) {

	for($i=1; $i < 8; $i++){
		if($i == 1)
			echo'<td class="badges_pdf">';
		
//create a default layout for pdf badges and generate radio buttons unselected for the rest
		
		if ($field->name == 'First name' && $i == 1)
			echo "$i<input checked = 'checked' type='radio' name='bpt[fields][".$i."]' value='".$value."'> ";
			
		elseif ($field->name == 'Last name' && $i == 2)
			echo "$i<input checked = 'checked' type='radio' name='bpt[fields][".$i."]' value='".$value."'> ";
			
		elseif ($field->name == 'How would you describe yourself' && $i == 7)
			echo "$i<input checked = 'checked' type='radio' name='bpt[fields][".$i."]' value='".$value."'> ";
		
		elseif ($field->name == 'How long have you been using WordPress' && $i == 6)
			echo "$i<input checked = 'checked' type='radio' name='bpt[fields][".$i."]' value='".$value."'> ";
		else
			echo "$i<input type='radio' name='bpt[fields][".$i."]' value=' ".$value."' > ";

		if($i == 7)
			echo'</td></div>';
		}
}

/**
 * Event selection dropdown
 * @return array Array of product IDs associated with event 
 * $url is the location this is used because atendees and stats page have different urls but use same function. More tabs etc can now be added and used
 */
 
function bpt_select_event_dropdown($url) {
	global $wpdb;
	
	$sql = "SELECT `id`, `post_title` FROM " . $wpdb->posts . " WHERE `post_type` = 'ep_event' AND post_status != 'auto-draft'";
	$events = $wpdb->get_results( $sql ) ; ?>

	<label for="events">Select an Event:</label>
	<select name="events_dropdown" style="width:200px" onchange="location.href='<?php bloginfo( 'url' );?><?php echo $url;?>'+document.getElementById('events_dropdown').value;" id="events_dropdown"><?php
	foreach( (array) $events as $event ) {
		$selected='';
		if( $event->id == $_GET['event'] )
			$selected="selected='selected'";
		echo '<option ' . $selected . ' value="' . $event->id . '">' . $event->post_title . '</option>';
	}
	?>
	</select>
	<?php

	if ( isset ( $_GET['event'] ) ) {
	 	$event = absint( $_GET['event'] );
	} else {
		$event = $wpdb->get_var( "SELECT `id` FROM " . $wpdb->prefix . "posts WHERE `post_type` = 'ep_event' AND `post_status` != 'draft' LIMIT 1" );
	}	 
	
	$product_id = $wpdb->get_col( 'SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE meta_key = "_bpt_event_prod_id" AND meta_value = ' . $event );
	
	return $product_id[0];
}


/**
 * Output PDF
 *
 */
function bpt_pdf($settings) {
//exit('<pre>'.print_r($settings,true).'</pre>');

	$pdf=new PDF();
	$pdf->AliasNbPages();
	
	//load data
	$pdf->LoadData( $settings);
	
	$pdf->SetFont( 'Arial', '', 8);
	$pdf->AddPage(L);
	$pdf->PrintTable();

	$pdf->Output();
	exit();
}

/**
 * Output Badges PDF Needs finishing
 *
 */

function bpt_badges_pdf($settings){
	global $wpdb;

@ini_set('log_errors','on');
@ini_set('display_errors','on');

//require_once(WPSC_FILE_PATH."/wpsc-includes/fpdf/mc_table.php");
require_once('bpt-functions.php');

@ini_set( 'memory_limit', '128M' );
@ini_set( 'max_input_time', '240' );
// Set up the new PDF object
$pdf = new PDF( 'L', 'in', 'Legal' );

$attendees = $pdf->LoadBadgesData($settings);
// Remove page margins.
$pdf->SetMargins(0, 0);
$pdf->SetFont('helvetica','',10);
// Disable auto page breaks.
$pdf->SetAutoPageBreak(0);

// Set up badge counter
$counter = 1;

for ( $i = 0; $i < count($attendees); $i++ ) {

		// Set the text color to black.
		$pdf->SetTextColor(223,125,80);

		// Grab the template file that will be used for the badge page layout
		//for here and now if you wanted to use your own template put it in the same directory and replace the file name
		require('templates/sf2010.php');

		// Download and store the gravatar for use, FPDF does not support gravatar formatted image links the user email has been saved into array[7] ready to go just for this reason!
		$grav_file_raw = WP_CONTENT_DIR.'/plugins/'.WPSC_TICKETS_FOLDER.'/images/temp/' . $attendees[$i][0] . '-' . rand();
		$grav_url = 'http://www.gravatar.com/avatar/' . md5($attendees[$i][7]) . '?s=512&default=http%3A%2F%2F2010.sf.wordcamp.org%2Fblank.jpg';
	//	exit('Data from Gravatar '.$grav_url);
		$grav_data = get_file_by_curl( $grav_url, $grav_file_raw );

		// Check if the image is a png, if it is, convert it, otherwise add a JPG extension to the raw filename
		if ( !$grav_file = pngtojpg($grav_file_raw) ) {
			$grav_file_extension = get_image_extension($grav_file_raw);
			$grav_file = $grav_file_raw . $grav_file_extension;
			rename( $grav_file_raw, $grav_file );
		}
		// Add the background image for the badge to the page
		$back_path = WP_CONTENT_DIR.'/plugins/'.WPSC_TICKETS_FOLDER.'/images/badgelogo2.jpg';
		$pdf->image($back_path, $background_x, $background_y, 2.8, 1.23);

		//set all images to the man.jpg for testing

		$pdf->image($grav_file, $avatar_x, $avatar_y, 0.6, 0.6);
		$pdf->SetDrawColor(187,187,187);
		$pdf->Rect($avatar_x - 0.02, $avatar_y - 0.02, 0.64, 0.64);

		// Set the co-ordinates, font $attendees[$i][0] [0] relates to template area 1 and so on.
		$pdf->SetXY($text_x, $text_y);
		$pdf->SetFont('helvetica','b',28);
		$pdf->MultiCell(0, 0,ucwords(stripslashes($attendees[$i][0])),0,'L');


		$pdf->SetXY($text_x, $text_y + 0.35);
		$pdf->SetFont('helvetica','',18);
		$pdf->SetTextColor(126,193,246);
		$pdf->MultiCell(0, 0,stripslashes(ucwords($attendees[$i][1])),0,'L');

		$pdf->SetXY($infotext_x, $infotext_y);
		$pdf->SetFont('helvetica','',10);
		$pdf->SetTextColor(99,100,102);

	
		$pdf->SetFont('helvetica','b',11);
		$pdf->MultiCell( 2.4, 0.21, stripslashes($attendees[$i][2]), 0, 'L' );
		$infotext_y += 0.23;
		
		$pdf->SetXY($infotext_x, $infotext_y);
		$pdf->SetFont('helvetica','',10);
		$pdf->MultiCell( 2.4, 0.21, stripslashes($attendees[$i][3]), 0, 'L' );
		$infotext_y += 0.23;
			
		$pdf->SetXY($infotext_x, $infotext_y);
		$pdf->SetFont('helvetica','b',11);
		$pdf->MultiCell( 2.4, 0.21, stripslashes($attendees[$i][4]), 0, 'L' );
				
		$pdf->SetXY($years_x + 0.21, $years_y);
		$pdf->SetFont('helvetica','',8);
		$pdf->MultiCell( 2.4, 0.21, stripslashes($attendees[$i][5]), 0, 'R' );

		$pdf->SetFillColor( 126, 193, 246 );
		$pdf->Rect( $typebox_x, $typebox_y, 3, 0.5, 'F' );

		$pdf->SetTextColor(255, 255, 255);
		$pdf->SetXY($typebox_x, $typebox_y);
		$pdf->SetFont('helvetica','b', 12);
		$pdf->MultiCell( 3, 0.5, strtoupper($attendees[$i][6]), 0, 'C' );

		$pdf->SetDrawColor(187,187,187);
		$counter++;
}
$pdf->Output();
exit();
}


/**
 * Create counter for tickets sold..
 *
 */
function bpt_get_total_quanity($product_id){
	global $wpdb;
	$sql = 'SELECT SUM(`'.WPSC_TABLE_CART_CONTENTS.'`.`quantity`) FROM `'.WPSC_TABLE_CART_CONTENTS.'` LEFT JOIN `'.WPSC_TABLE_PURCHASE_LOGS.'` ON `'.WPSC_TABLE_CART_CONTENTS.'`.`purchaseid` = `'.WPSC_TABLE_PURCHASE_LOGS.'`.`id` WHERE `'.WPSC_TABLE_PURCHASE_LOGS.'`.`processed` >1 AND `'.WPSC_TABLE_PURCHASE_LOGS.'`.`processed` < 5  AND `'.WPSC_TABLE_CART_CONTENTS.'`.`prodid`='.$id;
	$num = $wpdb->get_var($sql);
	if($num != null){
		return $num;
	}else{
		return 0;
	}
}

/* used for the counter on the stats page */
function bpt_get_quantities_sold($product_id){
	
	$users = bpt_get_registered_users( $product_id, true );
	return count($users);
	}
	
?>