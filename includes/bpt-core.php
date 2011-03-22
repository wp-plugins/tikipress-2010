<?php
define ( 'BPT_IS_INSTALLED', 1 );
define ( 'BPT_VERSION', '0.1' );
//this is in here because the theme message produced by buddypress is stupid!
define( 'BP_SILENCE_THEME_NOTICE', true );
load_plugin_textdomain( 'bpt', false, '/bp-ticketing/includes/languages/' );


if ( !class_exists( 'WPSC_Query' ) && ((float)WPSC_VERSION < 3.8 ))
	return;


if ( !defined( 'BPT_TICKETS_SLUG' ) )
	define ( 'BPT_TICKETS_SLUG', __( 'tickets', 'bpt' ) );

if ( !defined( 'BPT_TICKETS_HISTORY_SLUG' ) )
	define ( 'BPT_TICKETS_HISTORY_SLUG', __( 'purchases', 'bpt' ) );

if ( !defined( 'BPT_REDEEM_SLUG' ) )
	define ( 'BPT_REDEEM_SLUG', __( 'redeem', 'bpt' ) );

if ( !defined( 'BPT_ADMIN_SETTINGS_SLUG' ) )
	define ( 'BPT_ADMIN_SETTINGS_SLUG', __( 'settings', 'bpt' ) );
	
if ( !defined( 'BPT_ADMIN_ATTENDEES_SLUG' ) )
	define ( 'BPT_ADMIN_ATTENDEES_SLUG', __( 'attendees', 'bpt' ) );
	
if ( !defined( 'BPT_ADMIN_PDF_SLUG' ) )
	define ( 'BPT_ADMIN_PDF_SLUG', __( 'pdf', 'bpt' ) );
	
if ( !defined( 'BPT_ADMIN_PDF_BADGES_SLUG' ) )
	define ( 'BPT_ADMIN_PDF_BADGES_SLUG', __( 'badges', 'bpt' ) );

if ( !defined( 'BPT_ADMIN_HELP_SLUG' ) )
	define ( 'BPT_ADMIN_HELP_SLUG', __( 'help', 'bpt' ) );
	
	

// The classes file holds all database access classes and functions
require ( dirname( __FILE__ ) . '/bpt-classes.php' );

// The cssjs file should set up and enqueue all CSS and JS files used by the component
require ( dirname( __FILE__ ) . '/bpt-cssjs.php' );

// The templatetags file should contain classes and functions designed for use in template files
require ( dirname( __FILE__ ) . '/bpt-templatetags.php' );

// The filters file should create and apply filters to component output functions
require ( dirname( __FILE__ ) . '/bpt-filters.php' );

define('WPSC_TICKETS_FOLDER', dirname(plugin_basename(__FILE__)));
/**
 * bpt_is_wpsc_active() 
 *
 * Convenience function for finding out if WordPress e-Commerce is active
 */
function bpt_is_wpsc_active() {
	return defined( 'WPSC_VERSION' );
}

/**
 * bpt_add_admin_menu()
 *
 * This function will add a WordPress wp-admin admin menu for your component under the
 * "BuddyPress" menu.
 */
 
/*
 	function bpt_add_pages() {
		if ( is_admin() ) {
			add_filter( 'wpsc_additional_pages', 'bpt_add_admin_menu',10,2 );
		}	
	}
	add_action( 'init','bpt_add_pages' ); 
*/
	
function bpt_add_admin_menu($page_hooks, $base_page) {
	global $bp, $menu;

	if ( !$bp->loggedin_user->is_site_admin || !bpt_is_wpsc_active() )
		return $page_hooks;

	require_once( dirname( __FILE__ ) . '/bpt-admin.php' );

	add_action( 'admin_init', 'bpt_admin_pages_on_load' );
	add_action( 'admin_init', 'bpt_admin_register_settings' );
	$page_hooks[] = add_submenu_page( $base_page, __( 'Ticketing Settings', 'wpsc' ), __( '-TikiPress', 'wpsc' ), 'manage_options', 'wpsc-buddypressticketing', 'bpt_admin' );
return $page_hooks;
}
add_filter( 'wpsc_additional_pages', 'bpt_add_admin_menu',10,2 ); /* wpsc_admin_pages = 10 */

/**
 * bpt_load_template_filter()
 *
 * Custom load template filter; allows user to override these templates in
 * their active theme and replace the ones that are stored in the plugin directory.
 */
function bpt_load_template_filter( $found_template, $templates ) {
	global $bp;

	if ( $bp->current_component != BPT_TICKETS_SLUG )
		return $found_template;

	foreach ( (array) $templates as $template ) {
		if ( file_exists( STYLESHEETPATH . '/' . $template ) )
			$filtered_templates[] = STYLESHEETPATH . '/' . $template;
		else
			$filtered_templates[] = dirname( __FILE__ ) . '/templates/' . $template;
	}

	$found_template = $filtered_templates[0];
	return apply_filters( 'bpt_load_template_filter', $found_template );
}
add_filter( 'bp_located_template', 'bpt_load_template_filter', 10, 2 );

/**
 * bpt_load_template()
 *
 * Loads templates within a template.
 */
function bpt_load_template( $template ) {
	if ( $located_template = apply_filters( 'bp_located_template', locate_template( $template, false ), $template ) )
		load_template( apply_filters( 'bp_load_template', $located_template ) );
}


/********************************************************************************
 * Screen Functions
 *
 * Screen functions are the controllers of BuddyPress. They will execute when their
 * specific URL is caught. They will first save or manipulate data using business
 * functions, then pass on the user to a template file.
 */

function bpt_setup_nav() {
	global $bp, $is_member_page;

	if ( !bp_is_my_profile() )
		return;

	$subnav_url = $bp->loggedin_user->domain . BPT_TICKETS_SLUG . '/';
	bp_core_new_nav_item( array( 'name' => __( 'Ticketing', 'bpt' ), 'slug' => BPT_TICKETS_SLUG, 'screen_function' => 'bpt_screen_ticketing', 'default_subnav_slug' => BPT_TICKETS_HISTORY_SLUG, 'item_css_id' => 'ticketing', 'show_for_displayed_user' => bp_is_my_profile() ) );
	bp_core_new_subnav_item( array( 'name' => __( 'Ticket Purchases', 'bpt' ), 'slug' => BPT_TICKETS_HISTORY_SLUG, 'parent_url' => $subnav_url, 'parent_slug' => BPT_TICKETS_SLUG, 'screen_function' => 'bpt_screen_ticketing', 'position' => 10, 'item_css_id' => 'history', 'show_for_displayed_user' => bp_is_my_profile() ) );
	bp_core_new_subnav_item( array( 'name' => __( 'Redeem Voucher', 'bpt' ), 'slug' => BPT_REDEEM_SLUG, 'parent_url' => $subnav_url, 'parent_slug' => BPT_TICKETS_SLUG, 'screen_function' => 'bpt_screen_redeem', 'position' => 20, 'item_css_id' => 'redeem', 'show_for_displayed_user' => bp_is_my_profile() ) );

	bp_core_new_nav_default( array( 'parent_slug' => BPT_TICKETS_SLUG, 'screen_function' => 'bpt_screen_redeem', 'subnav_slug' => BPT_TICKETS_HISTORY_SLUG ) );
}
add_action( 'bp_setup_nav', 'bpt_setup_nav' );

function bpt_screen_ticketing() {
	do_action( 'bpt_screen_ticketing' );
	bp_core_load_template( apply_filters( 'bpt_screen_ticketing', 'bpt/members/single/bp-ticketing' ) );
}

function bpt_screen_redeem() {
	global $bp, $current_user;

	if ( !isset( $_POST['redeem-code-submit'] ) ) {
		do_action( 'bpt_screen_redeem' );
		bp_core_load_template( apply_filters( 'bpt_screen_redeem', 'bpt/members/single/bp-ticketing' ) );
		return;
	}

	if ( !wp_verify_nonce( $_POST['_wpnonce'], 'redemption' ) ) {
		wp_nonce_ays( '' );
		die();
	}

	$is_code_redeemed = true;

	// Get POST form field and validate.
	if ( !$_POST['redeem-code'] )
		$is_code_redeemed = false;

	if ( !$code = apply_filters( 'bpt_registration_redemption_code', $_POST['redeem-code'] ) )
		$is_code_redeemed = false;

	if ( $is_code_redeemed )
		$is_code_redeemed = bpt_is_ticket_code_valid( $code );

	if ( $is_code_redeemed ) {
		// Send email. This is an example, you'd probably want to remove this and hook into the filter.
		get_currentuserinfo();
		update_user_meta( $current_user->ID, 'ticket'.$is_code_redeemed, 'true');
		$product_name = bpt_get_product_name( $is_code_redeemed );
		$message = sprintf( "Hello! You have successfully redeemed your ticket for %s.", $product_name );
		$email = array( 'to' => $current_user->user_email,
										'subject' => sprintf( __( "%s - ticket redeemed", 'bpt' ), $product_name ),
										'message' => $message );
		wp_mail( $email['to'], $email['subject'], $email['message'] );

		do_action( 'bpt_screen_redeem_success', $is_code_redeemed, $code );
		bp_core_add_message( __( "Success! You've succesfully claimed your WordCamp ticket. We've emailed you the details.", 'bpt' ) );
		bp_core_redirect( bp_loggedin_user_domain() . '/' . BPT_TICKETS_SLUG );

	} else {
		do_action( 'bpt_screen_redeem_fail' );
		bp_core_add_message( __( "We found a problem with your code. Please check that you typed it correctly and try again.", 'bpt' ), 'error' );
		bp_core_redirect( bp_loggedin_user_domain() . '/' . BPT_TICKETS_SLUG . '/' . BPT_REDEEM_SLUG );
	}
}


/********************************************************************************
 * Ticket Redemption
 *
 * Hook into registration to capture ticket redemption code and redirect to
 * complete ticket details.
 */

function bpt_extend_checkout_form() {
global $bpt_errors, $wpsc_cart;
	$ticketing_category = bpt_get_ticketing_category();
	$is_a_ticket_sale = false;
	
	while ( wpsc_have_cart_items() ) {
		wpsc_the_cart_item();

		$categories = (array)$wpsc_cart->cart_item->category_id_list;
		foreach ( $categories as $category ) {
			if ( $category == $ticketing_category ) {
				$is_a_ticket_sale = true;
				$quantity = (int)$wpsc_cart->cart_item->quantity;
				break;
			}
		}

		if ( $is_a_ticket_sale )
			break;
	}

	$wpsc_cart->rewind_cart_items();

	if ( !$is_a_ticket_sale)
		return;
	///show the form to enter in email address for multiple ticket holders,	
	if ($quantity >= 2 ){
?>
	<tr>
		<td colspan="2">
			<h4><p><strong><?php _e( "Multiple tickets", 'bpt' ) ?>:</strong></p></h4>
			<p><?php _e( "As you are buying multiple tickets, enter the other recipients' email addresses. We'll email them a redemption code directly.", 'bpt' ) ?></p>
		</td>
	</tr>

	<tr>
		<td><label><?php _e( "Ticket 1: *", 'bpt' ) ?></label></td>
		<td><?php _e( "Details for this ticket will be sent to your account email address but please full out the ticket details below.", 'bpt' ) ?></td>
	</tr>

	<?php for ( $i=0; $i<($quantity-1); $i++ ) : ?>
		<?php $has_error = ( $bpt_errors && isset( $bpt_errors[$i] ) ); ?>

		<tr class="<?php if ( $has_error ) { echo 'validation-error'; } ?>">
			<td><label><?php printf( __( "Ticket %d: *", 'bpt' ), $i+2 ) ?></label></td>
			<td>
				<input type="email" name="bpt[]" class="text" value="<?php if ( $has_error ) { echo esc_attr( $bpt_errors[$i] ); } ?>">
				<?php if ( $has_error ){ ?>
				<p class="validation-error"><?php _e( "Please enter a valid email.", 'bpt' ) ?></p>
				<?php }?>
			</td>
		</tr>	
	

	<?php endfor; 
	}?>
	
	<tr>
		<td colspan="2">
			<h4><p><strong><?php _e( "Your ticket details:", 'bpt' ) ?></strong></p></h4>
			<p><?php _e( "Please check that your ticket information is all up to date, if your information is blank then please fill it out and update it.", 'bpt' ) ?></p>
		
			<?php bpt_displayTikiFields(); ?>
			
		</td>
	</tr>
<?php 
}
add_action( 'wpsc_inside_shopping_cart', 'bpt_extend_checkout_form' );

function bpt_displayTikiFields(){
	global $current_user, $wpdb, $bp;
	get_currentuserinfo();
	$userId= $current_user->ID;
	if(!$userId){
		echo '<h2>'. __('Please login first before purchasing!', 'bpt') . '</h2>';
		return;
	} ?>
	
	<?php
	
				if($_POST['bpt_profile_info']){
				
					$error=false;
					$fields=explode(',', $_POST['field_ids']);
					foreach ( $fields as $field ){
						if ( empty( $_POST['field_' . $field] ) ) {
							if( empty( $_POST['field_' . $field . '_day'] ) ){
								$error=true;
								break;
							}
						}
					}
					if( $error ){?>
						<p class="validation-error"><?php _e( "Please Fill in all the Ticket information fields.", 'bpt' ) ?></p><?php
					} else {

						$fields=explode(',', $_POST['field_ids']);
						foreach ( $fields as $field ){
							$existing = $wpdb->get_var( 'SELECT id FROM `' . $bp->table_prefix . 'bp_xprofile_data` WHERE field_id = ' . $field . ' AND user_id = '.$current_user->ID );
							if ( isset( $_POST['field_'.$field] ) ){
								$value = maybe_serialize($_POST['field_'.$field]);
							}else{
								$monthName = array(
								
									"January" => "01",
								
									"Febuary" => "02",
								
									"March" => "03",
								
									"April" => "04",
								
									"May" => "05",
								
									"June" => "06",
								
									"July" => "07",
								
									"August" => "08",
								
									"September" => "09",
								
									"October" => "10",
								
									"November" => "11",
								
									"December" => "12",
								
								);
								$value = mktime(0, 0, 0, $monthName[$_POST['field_' . $field . '_month']], $_POST['field_' . $field . '_day'], $_POST['field_' . $field . '_year']);
							}
							if ( $existing ) {
								$wpdb->query( 'UPDATE `' . $bp->table_prefix . 'bp_xprofile_data` SET `value` = \'' . $value . '\' WHERE id = ' . $existing . ' LIMIT 1' );
							} else {
								$wpdb->query( 'INSERT INTO `' . $bp->table_prefix . 'bp_xprofile_data` VALUES ("", "' . $field . '", "' . $current_user->ID . '", "' . $value . '", "")' );
							}
						}
					return;
					}
				}

	
	$options = get_blog_option( BP_ROOT_BLOG, 'bpt', true );
				$profile_group_id = $options['fields_group_id'];
				
					?>
					<?php if ( bp_has_profile( 'profile_group_id='.$profile_group_id ) ) : while ( bp_profile_groups() ) : bp_the_profile_group(); 
					$fields = explode(',', bp_get_the_profile_group_field_ids());
				
					foreach($fields as $field)
						$_POST['field_'.$field]=$wpdb->get_var('SELECT `value` FROM `' . $bp->table_prefix . 'bp_xprofile_data` WHERE field_id = ' . $field . ' AND user_id = '.$current_user->ID);
				
					?>
	
									
							<div class="clear"></div>
					
							<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>
					
								<div<?php bp_field_css_class( 'editfield' ) ?>>
					
									<?php if ( 'textbox' == bp_get_the_profile_field_type() ) : ?>
					
										<label for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></label>
										<input type="text" name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>" value="<?php bp_the_profile_field_edit_value() ?>" />
					
									<?php endif; ?>
					
									<?php if ( 'textarea' == bp_get_the_profile_field_type() ) : ?>
					
										<label for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></label>
										<textarea rows="5" cols="40" name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_edit_value() ?></textarea>
					
									<?php endif; ?>
					
									<?php if ( 'selectbox' == bp_get_the_profile_field_type() ) : ?>
																	
										<label for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></label><br />
										<select name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_options() ?></select>
					
									<?php endif; ?>
					
									<?php if ( 'multiselectbox' == bp_get_the_profile_field_type() ) : ?>
					
										<label for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></label>
										<select name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>" multiple="multiple">
											<?php 
												$id = bp_get_the_profile_field_id();
												$values = $wpdb->get_col( 'SELECT `value` FROM `' . $bp->table_prefix . 'bp_xprofile_data` WHERE field_id = ' . $id . ' AND user_id = '.$current_user->ID );
												$values = unserialize($values[0]);
												$options = $wpdb->get_col( 'SELECT `fields`.`name` FROM `' . $bp->table_prefix . 'bp_xprofile_fields` `fields` WHERE `fields`.`parent_id` = ' . $id );
												foreach ( $options as $option ) {
													$selected = '';
													if ( in_array( $option, (array)$values ) )
														$selected = 'selected="selected"';
													echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
												}
											?>
										</select>
					
										<?php if ( !bp_get_the_profile_field_is_required() ) : ?>
											<a class="clear-value" href="javascript:clear( '<?php bp_the_profile_field_input_name() ?>' );"><?php _e( 'Clear', 'buddypress' ) ?></a>
										<?php endif; ?>
					
									<?php endif; ?>
					
									<?php if ( 'radio' == bp_get_the_profile_field_type() ) : ?>
					
										<div class="radio">
											<span class="label"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></span>
					
											<?php bp_the_profile_field_options() ?>
					
											<?php if ( !bp_get_the_profile_field_is_required() ) : ?>
												<a class="clear-value" href="javascript:clear( '<?php bp_the_profile_field_input_name() ?>' );"><?php _e( 'Clear', 'buddypress' ) ?></a>
											<?php endif; ?>
										</div>
					
									<?php endif; ?>
					
									<?php if ( 'checkbox' == bp_get_the_profile_field_type() ) : ?>
					
										<div class="checkbox">
											<span class="label"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></span>
											<?php bp_the_profile_field_options() ?>
										</div>
					
									<?php endif; ?>
					
									<?php if ( 'datebox' == bp_get_the_profile_field_type() ) : ?>
					
										<div class="datebox">
											<label for="<?php bp_the_profile_field_input_name() ?>_day"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></label>
					
											<select name="<?php bp_the_profile_field_input_name() ?>_day" id="<?php bp_the_profile_field_input_name() ?>_day">
												<?php bp_the_profile_field_options( 'type=day' ) ?>
											</select>
					
											<select name="<?php bp_the_profile_field_input_name() ?>_month" id="<?php bp_the_profile_field_input_name() ?>_month">
												<?php bp_the_profile_field_options( 'type=month' ) ?>
											</select>
					
											<select name="<?php bp_the_profile_field_input_name() ?>_year" id="<?php bp_the_profile_field_input_name() ?>_year">
												<?php bp_the_profile_field_options( 'type=year' ) ?>
											</select>
										</div>
					
									<?php endif; ?>
					
									<p class="description"><?php bp_the_profile_field_description() ?></p>
								</div>
					
							<?php endwhile; ?>
					
						<?php do_action( 'bp_after_profile_field_content' ) ?>
						
						<input type="hidden" name="bpt-redeem-code" value="<?php echo $code; ?>" />
						<input type="hidden" name="bpt_profile_info" value="true" />		
						<input type="hidden" name="field_ids" id="field_ids" value="<?php bp_the_profile_group_field_ids() ?>" />
						<?php wp_nonce_field( 'bpt_redemption' ) ?>
					
					
					<?php endwhile; endif;
				return;

	//bpt_edit_profile();

}

function bpt_extend_checkout_validation( $validation_data ) {
	global $bpt_errors;  // I hate using this but can't find a way to pass variables between the two actions/filters here :(

		if($_POST['bpt_profile_info']){
						
			$error=false;
			$fields=explode(',', $_POST['field_ids']);
			foreach ( $fields as $field ){
				if ( empty( $_POST['field_' . $field] ) ) {
					if( empty( $_POST['field_' . $field . '_day'] ) ){
						$error=true;
						break;
					}
				}
			}
			if( $error ){
			$validation_data['is_valid'] = false;
			} else {
			$validation_data['is_valid'] = true;
			}
		}

	if ( !isset( $_POST['bpt'] ) )
		return $validation_data;


	$bpt_errors = array();
	$_POST['bpt'] = (array)$_POST['bpt'];

	for ( $i=0; $i<count( $_POST['bpt'] ); $i++ ) {
		$ticket_email = apply_filters( 'bpt_checkout_ticketing_email', $_POST['bpt'][$i] );

		if ( !$ticket_email || !is_email( $ticket_email ) )
			$bpt_errors[$i] = $ticket_email;
	}

	if ( $bpt_errors )
		$validation_data['is_valid'] = false;

	return $validation_data;
}
add_filter( 'wpsc_checkout_validate_forms', 'bpt_extend_checkout_validation' );

function bpt_update_tiki_fields(){
global $bpt_errors, $current_user, $wpdb, $bp;
//echo'<pre>'.print_r($_POST,1).'</pre>';
$fields=explode(',', $_POST['field_ids']);
//echo'<pre> fff are'.print_r($fields,1).'</pre>';
						foreach ( $fields as $field ){
							$existing = $wpdb->get_var( 'SELECT id FROM `' . $bp->table_prefix . 'bp_xprofile_data` WHERE field_id = ' . $field . ' AND user_id = '.$current_user->ID );
							
							//echo'<pre> current user'.print_r($current_user->ID,1).'</pre>';
							if ( isset( $_POST['field_'.$field] ) ){
							
								$value = maybe_serialize($_POST['field_'.$field]);
								//echo('value'.print_r($value,1).'</pre>');
							}else{
								$monthName = array(
								
									"January" => "01",
								
									"Febuary" => "02",
								
									"March" => "03",
								
									"April" => "04",
								
									"May" => "05",
								
									"June" => "06",
								
									"July" => "07",
								
									"August" => "08",
								
									"September" => "09",
								
									"October" => "10",
								
									"November" => "11",
								
									"December" => "12",
								
								);
								$value = mktime(0, 0, 0, $monthName[$_POST['field_' . $field . '_month']], $_POST['field_' . $field . '_day'], $_POST['field_' . $field . '_year']);
							}
							if ( $existing ) {
								$wpdb->query( 'UPDATE `' . $bp->table_prefix . 'bp_xprofile_data` SET `value` = \'' . $value . '\' WHERE id = ' . $existing . ' LIMIT 1' );
								//echo('INSERT INTO `' . $bp->table_prefix . 'bp_xprofile_data` VALUES ("", "' . $field . '", "' . $current_user->ID . '", "' . $value . '", "")');
							} else {
								$wpdb->query( 'INSERT INTO `' . $bp->table_prefix . 'bp_xprofile_data` VALUES ("", "' . $field . '", "' . $current_user->ID . '", "' . $value . '", "")' );
								
								//exit('<pre>'.print_r($bp,1).'</pre>');
							}
						}
						//exit('hurro');
						return;


}

add_action( 'wpsc_submit_checkout', 'bpt_update_tiki_fields', 10, 1 );


function bpt_create_codes_after_checkout( $sale_data ) {
	if ( !isset( $_POST['bpt'] ) )
		return;
	$codes = array();
	$purchase_id = $sale_data['purchase_log_id'];
	$_POST['bpt'] = (array)$_POST['bpt'];

	for ( $i=0; $i<count( $_POST['bpt'] ); $i++ ) {
		$ticket_email = apply_filters( 'bpt_checkout_ticketing_email', $_POST['bpt'][$i] );

		if ( $ticket_email && is_email( $ticket_email ) )
			$codes[] = array( 'code' => bpt_create_code( $ticket_email, $purchase_id ), 'email' => $ticket_email );
	}

	if ( !$codes )
		return;

	foreach ( $codes as $code )
		$sale_log_codes[] = array( 'email' => $code['email'], 'code' => $code['code'] );
	
	wpsc_update_meta( $sale_data["purchase_log_id"], 'bpt_codes', $sale_log_codes, 'sale_log' );

	do_action( 'bpt_create_codes_after_checkout', $codes, $purchase_id );
}
add_action( 'wpsc_submit_checkout', 'bpt_create_codes_after_checkout', 10, 1 );

function bpt_create_code( $email, $purchase_id ) {
	$hash = md5( time() . $email . time() );
	$code = substr( $hash, 0, 2 ) . 'WC' . date( 'y' ) . substr( $hash, 0, 10 )."EV".$purchase_id;

	return apply_filters( 'bpt_create_code', strtoupper( $code ), $email, $purchase_id );
}

//shortcode function,outputs list of attendees in the same markup as wordcamp sanfran 2010 page
function bpt_attendees($atts) {
	global $wpdb, $bp;
	
	extract(shortcode_atts(array(
		'id' => 0
	), $atts));
	$id=$atts["id"];
	if($id==0)
		return "No Attendees so far. (Event id can not be 0)";
	$users = bpt_get_registered_users($id);?>
	<p>These folks have signed up already. <a href="http://en.gravatar.com/">Get a Gravatar</a> to have your picture show up by your name.</p>
<?php
	if(!$users)
		return "No Attendees so far.";
	$output = "";
	$output = "<p>".count($users)." attendees total.</p> 
<br clear='all' /> ";
foreach ($users as $user){
	$usermd5=md5( strtolower( trim( $user->user_email ) ) );
	$url = $wpdb->get_var('SELECT `'.$bp->table_prefix.'bp_xprofile_data`.`value` FROM `'.$bp->table_prefix.'bp_xprofile_data` WHERE `'.$bp->table_prefix.'bp_xprofile_data`.`field_id`=856 AND  `'.$bp->table_prefix.'bp_xprofile_data`.`user_id`='.$user->ID);
	
	$old_track = ini_set('track_errors', '1');
	if(!$str = @file_get_contents( 'http://www.gravatar.com/'.$usermd5.'.php' )){
		$profile=0;
	}else{
		$profile = unserialize( $str );
	}
	ini_set('track_errors', $old_track);
	
	if(isset($url) && $url != '')
		$url = $url;
	elseif('User not found' != $profile)
		$url = $profile['entry'][0]['urls'][0]['value'];

		
	$output.="<div class='attendee'><h4><a href='".$url."' rel='nofollow'> <img alt='' src='http://www.gravatar.com/avatar/".$usermd5."?s=42' class='avatar avatar-42 photo' height='42' width='42' />".$user->display_name."</a></h4>";
	
	if($url)
		$output.="<a href='".$url."' rel='nofollow' style='text-decoration: none; font-size:12px; color: black;'>".$url."</a>";
	
	elseif('User not found' != $profile){
	foreach((array)$profile['entry'][0]['accounts'] as $account){
		if($account["domain"]=="twitter.com")
		$output .= "<a href='".$account["url"]."' rel='nofollow' style='text-decoration: none; font-size:12px; color: black;'>".$account["display"]."</a>";
	}
	}
	
	$output.="</div>";
}
	return $output;
}
add_shortcode('bpt_attendees', 'bpt_attendees');

function bpt_subnav(){
	global $ep_views, $post;
	$type = get_post_type_object( 'ep_event' );
	
	bp_core_remove_subnav_item ('events', 'registered');
}

add_action( 'ep_subnav', 'bpt_subnav' );


function bpt_buy_now($output, $user_status){
	global $bp, $ep_models, $wpdb, $wpsc_query;
	$post_id=get_the_ID();
	
	
	$product_id = $wpdb->get_var("SELECT post_id FROM ".$wpdb->postmeta." WHERE meta_key='_bpt_event_prod_id' and meta_value = $post_id;");
		
	if((float)WPSC_VERSION >= 3.8 ){
		$product_url = get_permalink($product_id);
	 }else{
		$wpsc_query = new WPSC_query(array('product_id'=>$product_id));
		while (wpsc_have_products()) :  wpsc_the_product();
		$product_url=wpsc_the_product_permalink();
		endwhile;
	}

	if ( ep_registration_open() ) {
		$userid = bp_loggedin_user_id();
		if ( $userid != 0 ) {
			if(!$user_status){
				return "<a href = '" . $product_url . "' class = 'button'>" . __( 'Buy ticket', 'bpt' ) . "</a>";
			}else{
				return "<a href = '" . $product_url . "' class = 'button'>" . __( 'Buy ticket', 'bpt' ) . "</a>";
			}
		}else{
			return "<a href = '" . $product_url . "' class = 'button'>" . __( 'Buy ticket', 'bpt' ) . "</a>";
		}
	}else{
		return "<a href = '" . $product_url . "' class = 'button'>" . __( 'Buy ticket', 'bpt' ) . "</a>";
	}
}


add_filter('ep_register_button', 'bpt_buy_now', 10, 3);



add_action('edit_user_profile', 'bpt_edit_profile');
add_action('edit_user_profile_update', 'bpt_update_profile');

function bpt_edit_profile(){
	global $wpdb, $bp;
		?>
		<a name="xprofile_details"></a><h3>Buddypress ticketing fields:</h3>
		<table class="form-table">
		<?php
	$user_id=$_GET['user_id'];
		
	$profile_data = bpt_get_profile_data($user_id);
	
	$profile_fields=$wpdb->get_results('SELECT `'.$bp->table_prefix.'bp_xprofile_fields`.`name`, `'.$bp->table_prefix.'bp_xprofile_fields`.`id` FROM `'.$bp->table_prefix.'bp_xprofile_fields` WHERE (`'.$bp->table_prefix.'bp_xprofile_fields`.`type` = "textarea" OR `'.$bp->table_prefix.'bp_xprofile_fields`.`type` = "textbox") AND `'.$bp->table_prefix.'bp_xprofile_fields`.`id` != 1 ');
	
	foreach((array)$profile_fields as $field){
		?>
		<tr>
		<th><?php echo $field->name;  ?>:</th> <td><input name="bpt_<?php echo $field->id; ?>" value="<?php echo $profile_data[$field->name] ; ?>" size="50" /></td>
		</tr>
		<?php
	}	
	?>
	</table>
	<?php
}

function bpt_get_profile_data($id){
	global $wpdb, $bp;
	$profile_fields=$wpdb->get_results('SELECT `'.$bp->table_prefix.'bp_xprofile_fields`.`name`, `'.$bp->table_prefix.'bp_xprofile_data`.`value` FROM `'.$bp->table_prefix.'bp_xprofile_data` JOIN `'.$bp->table_prefix.'bp_xprofile_fields` ON `'.$bp->table_prefix.'bp_xprofile_data`.`field_id` = `'.$bp->table_prefix.'bp_xprofile_fields`.`id` WHERE `'.$bp->table_prefix.'bp_xprofile_data`.`user_id`='.absint($id).' AND ( `'.$bp->table_prefix.'bp_xprofile_fields`.`type` = "textarea" OR `'.$bp->table_prefix.'bp_xprofile_fields`.`type` = "textbox" )');
	$profile_data=array();
	
	foreach((array)$profile_fields as $field){
		$profile_data[$field->name] = $field->value;
	}
	return $profile_data;
}

function bpt_update_profile(){
	global $wpdb, $bp;
	$user_id = absint($_POST['user_id']);
	
	$profile_data = bpt_get_profile_data($user_id);
	
	$profile_fields=$wpdb->get_results('SELECT `'.$bp->table_prefix.'bp_xprofile_fields`.`name`, `'.$bp->table_prefix.'bp_xprofile_fields`.`id` FROM `'.$bp->table_prefix.'bp_xprofile_fields` WHERE (`'.$bp->table_prefix.'bp_xprofile_fields`.`type` = "textarea" OR `'.$bp->table_prefix.'bp_xprofile_fields`.`type` = "textbox") AND `'.$bp->table_prefix.'bp_xprofile_fields`.`id` != 1 ');
		
	foreach((array)$profile_fields as $field){
		if(isset($profile_data[$field->name])){
			$wpdb->query($wpdb->prepare('UPDATE `'.$bp->table_prefix.'bp_xprofile_data` SET `value`="%s" WHERE `field_id`=%d AND `user_id`=%d LIMIT 1', $_POST['bpt_'.$field->id], $field->id, $user_id));
		}else{
			$wpdb->query($wpdb->prepare('INSERT INTO `'.$bp->table_prefix.'bp_xprofile_data` VALUES("", %d, %d, "%s", "%s")  LIMIT 1', $field->id, $user_id, $_POST['bpt_'.$field->id], time() ));
		}
	}
}

function bpt_redeem_code_page($ticket){

global $current_user, $wpdb, $bp;
get_currentuserinfo();
if(!$current_user->ID){
?>
	 <p id="login-text">
	<?php
	                        ///not sure if this is the best way to do this..research it perhapes
    		 if(isset($_POST['wpsc_submit_registration'])){
    		 $errors = array();
	    		 if(empty ($_POST['password']) || empty($_POST['password2']))
	    		 	$errors[]= 'You need to enter in a password';
	    		 
	    		 if($_POST['password']!= $_POST['password2'] )
					$errors[]= 'Your passwords need to match';
				
					
				if (empty ($_POST['username']))
					$errors[]= 'You must enter a username';
				else {
					$name_test = validate_username($_POST['username']);
						if($user_test != true) 
								$errors[] = 'Invalid Username';
							
					// check whether username already exists
					$user_id = username_exists( $_POST['username'] );
						if($user_id) 
							$errors[]= 'This username already exists';
				}
				if(empty($_POST['email']))
					$errors[] = "You must enter an email."; 	
				else {
					$email_test = email_exists($_POST['email']);
					
					if($email_test != false) 
							$errors[] = 'An account with this email has already been registered';
				}					
			}


    		
			if (!empty($errors)){
				foreach ($errors as $error)
				echo '<div class="login_error">' . $error . '</div>';
				}
			else {
			 ?>
		<h2><?php _e('Opps, in order to redeem your ticket you need to login!');?></h2>
		<p><?php _e('Fill out the information below to create your WordCamp account, you will then be logged in automatically. If you already have an account then please sign in.');?> </p> <div id="bpt_redeem_join"> <?php			
			}
			
		$form = '';
		$form .=  '<div id="tiki_sign_up">';
		$form .=  '<h2> Sign Up </h2>';
		$form .=  '<form method="post" action="" id="simplr-reg">';
		$form .=  '<label for="username" class="left">Username:</label>';
		$form .=  '<input type="text" name="username" class="right" value="'.$_POST['username'] .'" /><br/>';
		$form .=  '<label for="email" class="left">Email: </label>';
		$form .=  '<input type="text" name="email" class="right" value="'.$_POST['email'] .'" /><br/>';
		$form .=  '<label for="password" class="left">Password:</label>';
		$form .=  '<input type="password" name="password" class="right" value=""/><br/>';
		$form .=  '<label for="password" class="left">Confirm Password:</label>';
		$form .=  '<input type="password" name="password2" class="right" value=""/><br/>';
		$form .=  '<input type="submit" name="wpsc_submit_registration" value="Register" class="submit">';
		$form .=  '</form>';
		$form .=  '</div>';
		echo $form;


					?>
					</div>
<div id="bpt_redeem_login">

		<h2>Sign In</h2>
</p>	
		<?php
		$args = array( 'remember' => false );
		wp_login_form( $args );
		?>
	</div>
<?php
	return;
}
	if(isset($_REQUEST['_wpnonce'])){
		$nonce=$_REQUEST['_wpnonce'];
		if (! wp_verify_nonce($nonce, 'bpt_redemption') ){
			echo __('Something went wrong, please try again.', 'bpt'); 
			return;
		}else{
			$is_code_valid = true;

			// Get POST form field and validate.
			if ( !$_POST['bpt-redeem-code'] )
				$is_code_valid = false;
		
			if ( !$code = apply_filters( 'bpt_registration_redemption_code', $_POST['bpt-redeem-code'] ) )
				$is_code_valid = false;
		
			if ( $is_code_valid )
				$is_code_valid = bpt_is_ticket_code_valid( $code );
				
			if ( $is_code_valid ) {
				if($_POST['bpt_profile_info']){
					$error=false;
					$fields=explode(',', $_POST['field_ids']);
					foreach ( $fields as $field ){
						if ( empty( $_POST['field_' . $field] ) ) {
							if( empty( $_POST['field_' . $field . '_day'] ) ){
								$error=true;
								break;
							}
						}
					}
					if( $error ){
						_e('Please fill in all fields!', 'bpt');
					} else {
						// Send email. This is an example, you'd probably want to remove this and hook into the filter.
						$fields=explode(',', $_POST['field_ids']);
						foreach ( $fields as $field ){
							$existing = $wpdb->get_var( 'SELECT id FROM `' . $bp->table_prefix . 'bp_xprofile_data` WHERE field_id = ' . $field . ' AND user_id = '.$current_user->ID );
							if ( isset( $_POST['field_'.$field] ) ){
								$value = maybe_serialize($_POST['field_'.$field]);
							}else{
								$monthName = array(
								
									"January" => "01",
								
									"Febuary" => "02",
								
									"March" => "03",
								
									"April" => "04",
								
									"May" => "05",
								
									"June" => "06",
								
									"July" => "07",
								
									"August" => "08",
								
									"September" => "09",
								
									"October" => "10",
								
									"November" => "11",
								
									"December" => "12",
								
								);
								$value = mktime(0, 0, 0, $monthName[$_POST['field_' . $field . '_month']], $_POST['field_' . $field . '_day'], $_POST['field_' . $field . '_year']);
							}
							if ( $existing ) {
								$wpdb->query( 'UPDATE `' . $bp->table_prefix . 'bp_xprofile_data` SET `value` = \'' . $value . '\' WHERE id = ' . $existing . ' LIMIT 1' );
							} else {
								$wpdb->query( 'INSERT INTO `' . $bp->table_prefix . 'bp_xprofile_data` VALUES ("", "' . $field . '", "' . $current_user->ID . '", "' . $value . '", "")' );
							}
						}
						////$product_id / product name	is broke :(		
						
						$purchase_id = substr($code,18);
						
						$product_name = bpt_get_product_name( $purchase_id );
						$product_id= bpt_get_product_id ($purchase_id);
						
						update_user_meta( $current_user->ID, 'ticket'.$product_id, 'true');
						$message = sprintf( "Hello! You have successfully redeemed your ticket for %s.", $product_name );
						$email = array( 'to' => $current_user->user_email,
														'subject' => sprintf( __( "%s - ticket redeemed", 'bpt' ), $product_name ),
														'message' => $message );
						wp_mail( $email['to'], $email['subject'], $email['message'] );
						
					/*
	echo "product name is: EMPTY" . $product_name . "<br />";
						echo "is_code_redeemed: EMPTY " . $is_code_redeemed . "<br />";
						echo "is_code_redeemed: CODE" . $code . "<br />";
*/
						
						do_action( 'bpt_screen_redeem_success', $is_code_redeemed, $code );
						echo __( "Success! You've succesfully claimed your WordCamp ticket. We've emailed you the details.", 'bpt' );
						return;
					}
				}
				$options = get_blog_option( BP_ROOT_BLOG, 'bpt', true );
				$profile_group_id = $options['fields_group_id'];
					?>
					<?php if ( bp_has_profile( 'profile_group_id='.$profile_group_id ) ) : while ( bp_profile_groups() ) : bp_the_profile_group(); 
					$fields = explode(',', bp_get_the_profile_group_field_ids());
					foreach($fields as $field)
						$_POST['field_'.$field]=$wpdb->get_var('SELECT `value` FROM `' . $bp->table_prefix . 'bp_xprofile_data` WHERE field_id = ' . $field . ' AND user_id = '.$current_user->ID);
					
					?>
	
					<form action="<?php the_permalink() ?>" method="post" id="profile-edit-form" class="standard-form <?php bp_the_profile_group_slug() ?>">
						<input type="hidden" name="bpt-redeem-code" value="<?php echo $code; ?>" />
						<input type="hidden" name="bpt_profile_info" value="true" />
									
							<div class="clear"></div>
					
							<?php while ( bp_profile_fields() ) : bp_the_profile_field(); ?>
					
								<div<?php bp_field_css_class( 'editfield' ) ?>>
					
									<?php if ( 'textbox' == bp_get_the_profile_field_type() ) : ?>
					
										<label for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></label>
										<input type="text" name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>" value="<?php bp_the_profile_field_edit_value() ?>" />
					
									<?php endif; ?>
					
									<?php if ( 'textarea' == bp_get_the_profile_field_type() ) : ?>
					
										<label for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></label>
										<textarea rows="5" cols="40" name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_edit_value() ?></textarea>
					
									<?php endif; ?>
					
									<?php if ( 'selectbox' == bp_get_the_profile_field_type() ) : ?>
					
										<label for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></label>
										<select name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>">
											<option value="">--------</option>
											<?php 
												$id = bp_get_the_profile_field_id();
												$options = $wpdb->get_col( 'SELECT `fields`.`name` FROM `' . $bp->table_prefix . 'bp_xprofile_fields` `fields` WHERE `fields`.`parent_id` = ' . $id );
												foreach ( $options as $option ) {
													$selected = '';
													if ( $_POST['field_'.$id] == $option )
														$selected = 'selected="selected"';
													echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
												}
											?>
										</select>
					
									<?php endif; ?>
					
									<?php if ( 'multiselectbox' == bp_get_the_profile_field_type() ) : ?>
					
										<label for="<?php bp_the_profile_field_input_name() ?>"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></label>
										<select name="<?php bp_the_profile_field_input_name() ?>" id="<?php bp_the_profile_field_input_name() ?>" multiple="multiple">
											<?php 
												$id = bp_get_the_profile_field_id();
												$values = $wpdb->get_col( 'SELECT `value` FROM `' . $bp->table_prefix . 'bp_xprofile_data` WHERE field_id = ' . $id . ' AND user_id = '.$current_user->ID );
												$values = unserialize($values[0]);
												$options = $wpdb->get_col( 'SELECT `fields`.`name` FROM `' . $bp->table_prefix . 'bp_xprofile_fields` `fields` WHERE `fields`.`parent_id` = ' . $id );
												foreach ( $options as $option ) {
													$selected = '';
													if ( in_array( $option, (array)$values ) )
														$selected = 'selected="selected"';
													echo '<option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
												}
											?>
										</select>
					
										<?php if ( !bp_get_the_profile_field_is_required() ) : ?>
											<a class="clear-value" href="javascript:clear( '<?php bp_the_profile_field_input_name() ?>' );"><?php _e( 'Clear', 'buddypress' ) ?></a>
										<?php endif; ?>
					
									<?php endif; ?>
					
									<?php if ( 'radio' == bp_get_the_profile_field_type() ) : ?>
					
										<div class="radio">
											<span class="label"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></span>
					
											<?php bp_the_profile_field_options() ?>
					
											<?php if ( !bp_get_the_profile_field_is_required() ) : ?>
												<a class="clear-value" href="javascript:clear( '<?php bp_the_profile_field_input_name() ?>' );"><?php _e( 'Clear', 'buddypress' ) ?></a>
											<?php endif; ?>
										</div>
					
									<?php endif; ?>
					
									<?php if ( 'checkbox' == bp_get_the_profile_field_type() ) : ?>
					
										<div class="checkbox">
											<span class="label"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></span>
					
											<?php bp_the_profile_field_options() ?>
										</div>
					
									<?php endif; ?>
					
									<?php if ( 'datebox' == bp_get_the_profile_field_type() ) : ?>
					
										<div class="datebox">
											<label for="<?php bp_the_profile_field_input_name() ?>_day"><?php bp_the_profile_field_name() ?> <?php if ( bp_get_the_profile_field_is_required() ) : ?><?php _e( '(required)', 'buddypress' ) ?><?php endif; ?></label>
					
											<select name="<?php bp_the_profile_field_input_name() ?>_day" id="<?php bp_the_profile_field_input_name() ?>_day">
												<?php bp_the_profile_field_options( 'type=day' ) ?>
											</select>
					
											<select name="<?php bp_the_profile_field_input_name() ?>_month" id="<?php bp_the_profile_field_input_name() ?>_month">
												<?php bp_the_profile_field_options( 'type=month' ) ?>
											</select>
					
											<select name="<?php bp_the_profile_field_input_name() ?>_year" id="<?php bp_the_profile_field_input_name() ?>_year">
												<?php bp_the_profile_field_options( 'type=year' ) ?>
											</select>
										</div>
					
									<?php endif; ?>
					
									<p class="description"><?php bp_the_profile_field_description() ?></p>
								</div>
					
							<?php endwhile; ?>
					
						<?php do_action( 'bp_after_profile_field_content' ) ?>
					
						<div class="submit">
							<input type="submit" name="profile-group-edit-submit" id="profile-group-edit-submit" value="<?php _e( 'Save Changes', 'bpt' ) ?> " />
						</div>
					
						<input type="hidden" name="field_ids" id="field_ids" value="<?php bp_the_profile_group_field_ids() ?>" />
						<?php wp_nonce_field( 'bpt_redemption' ) ?>
					
					</form>
					<?php endwhile; endif; ?> <?php
					return;
				
			} else {
				do_action( 'bpt_screen_redeem_fail' );
				echo __( "We found a problem with your code. Please check that you typed it correctly and try again.", 'bpt' );
			}
		}
	}
	?>
	<form method="post" id="redeem-code-form" class="standard-form" action="<?php the_permalink(); ?>">
		<?php wp_nonce_field( 'bpt_redemption' ) ?>
	
		<p><?php _e( "If you've been sent a WordCamp ticket code, enter the redemption code to claim it:", 'bpt' ) ?></p>
		<p><input type="text" name="bpt-redeem-code" id="bpt-redeem-code" /></p>
	
		<p><input type="submit" name="bpt-redeem-code-submit" id="bpt-redeem-code-submit" value="<?php _e( "Give me the good stuff!", 'bpt' ) ?>"></p>
	</form>
	<?php
}

add_shortcode('bpt_redeem_code_page', 'bpt_redeem_code_page');


function bpt_send_redeem_vouchers( $sale_data ) {
	global $wpdb, $bp;
	$sale_id = absint( $sale_data['purchase_id'] );
	$sale_status = $wpdb->get_var( 'SELECT `logs`.`processed` FROM `' . $wpdb->prefix . 'wpsc_purchase_logs` `logs` WHERE `logs`.`id` = ' . $sale_id );
	$accepted_statuses = array( 2, 3, 4 );
	if( in_array( absint($sale_status), $accepted_statuses ) ) {
		$codes = wpsc_get_meta( $sale_id, 'bpt_codes', 'sale_log' );
		$product_name = bpt_get_product_name( $sale_id );
		foreach( (array)$codes as $code ){
			wpsc_update_meta( bpt_get_product_id($sale_id), $code['email'], $code['code'], 'bp_ticketing' );
			$message = sprintf( 
			"Hello! Someone's bought you a ticket for %s.
			You Will need to register to claim this ticket. If your already a member and this is the registered email address for the account then please enter the following code into the
			Redeem Ticket page at: %s.
			
			Your redeem code is: %s. If you are not a member you will need to sign up using this email address so your ticket can be validated.", $product_name, 'http://wordcamp.org.nz/redeem-your-ticket-code/', $code['code'] );
			$email = array( 'to' => $code['email'],
							'subject' => sprintf( __( "%s - redemption code", 'bpt' ), $product_name ),
							'message' => $message );
			$email = apply_filters( 'bpt_filter_redemption_email', $email, $purchase_id );
			wp_mail( $email['to'], $email['subject'], $email['message'] );
		}
	}
	return;
}

add_action('wpsc_transaction_result_cart_item', 'bpt_send_redeem_vouchers', 10, 1)

?>