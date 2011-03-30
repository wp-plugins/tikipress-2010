<?php

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
 * bpt_display_graph
 *
 * @param int tickets sold, ticket total (for the selected event) $class string class name for graph
 * @return html statisitcs graph
 */
function bpt_display_graph($tickets_sold,$ticket_total,$class){

$html.='<div id="attendeeGraph" class="'.$class.'">';
$html.='<img src="http://chart.apis.google.com/chart?chs=300x150&cht=p3&chd=t:'.number_format($tickets_sold/1000,3).','.number_format(($ticket_total-$tickets_sold)/1000,3).'&chdl=Sold|Left&chp=0.628&chl=' . $tickets_sold . '|' . ($ticket_total - $tickets_sold) . '&chtt=Attendance">';
$html.='</div>';
$html.='<div class="clear"></div>';

return $html;
}

/**
 * bpt_get_quantities_sold
 *
 * @param int $product_id
 * @return int count $users this is the number of users who 
 * have bought tickets to the product/event
 */
function bpt_get_quantities_sold($product_id){
	$users = bpt_get_registered_users( $product_id, true );
	return count($users);
	}
	

/**
 * bpt_display_graph
 *
 * @param array users attending the selected event
 * @return html group email form
 */
function bpt_display_group_email($users){
?>	<div class="left">
		<h4>Mailing List</h4> 
		<p>These are the registered attendees who will receive the email.</p>
		<?php
		foreach ($users as $user)
			echo $user->user_email .', ';
		?>
			<h4> Send a group email to all registered attendees </h4>	
			<table>
				<input type="hidden" name="attendeeNotificationNonce" id="attendeeNotificationNonce" value="<?php echo wp_create_nonce(plugin_basename(__FILE__))?>" />
		
				<tr valign="top">
					<th scope="row"><label for="email_attendees_subject">Subject:</label></th>
					<td><input name="email_attendees_subject" size="80" type="text" value=""></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="email_attendees_body">Message:</th>
					<td><textarea rows="10" cols="80" name="email_attendees_body"></textarea></td>
				</tr>
				<tr>
					<td></td>
					<td><input class="button-primary" type="submit" name="submit_email" value="Send Notification"></td>
				</tr>
			</table>
		</div>
		<div class="clear"></div>
	<?php	
	
//</div>


}


/**
 * bpt_send_email
 *
 * @param mixed email settings
 * 
 */
function bpt_send_email($_POST){

	if (wp_verify_nonce($_POST['attendeeNotificationNonce'], plugin_basename(__FILE__))){
		
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


/**
 * bpt_display_attenddee_table
 *
 * @param array users attending the selected event $collums Table collums
 * @return table with attendees names
 * @todo change the <a> tag to a variable so this can be used elsewhere if a link on the name is desired
 */
function bpt_display_attenddee_table($users,$columns){

//have moved this into a function as people might want to display the table else where however A tag should be removed or URL changed
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
						if ( $style )
							$style = ' class="alternate"';
						else
							$style = '';
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
	</div>
<?php
}
	
		
/**
 * bpt_pdf_badge_dropdown_options
 *
 * @param array $all_fields, all buddypress profile fields and gravatar information collected at checkout
 * @return select boxes relating to the template posistion
 */		
function bpt_pdf_badge_dropdown_options($all_fields){
	
	for($j=0; $j < count($all_fields); $j++){
		
		$select_id = 'Select'.($j+1) ;
		$select_name = "bpt[fields][".$value."]"; ?>
		<tr class="bpt_template_select_row"> <?php
			//if we are accessing grav fields
			if($j < 3){
				echo '<td class="bpt_template_select">'.$all_fields[$j]['name'] . '</td>';
				$value = $all_fields[$j]['id'];
			}else{
				echo '<td class="bpt_template_select">'.$all_fields[$j]->name . '</td>';
				$value = $all_fields[$j]->id;
			}
			$select_name = "bpt[fields][".$value."]"; 
			
			?><td class="bpt_template_select"> 
				<select id="<?php echo $select_id ?>" name="<?php echo $select_name ?>" onchange="javascript:SelectBoxes(this);">
					<option value='exclude' selected='selected'>Exclude</option> <?php
					//for each template posistion ($i) create that option will need to be not hard coded when more templates are added
					for($i=1; $i < 8; $i++) 
						echo '<option value="' . $i . '"> Template Area ' . $i . '</option>'; ?>
				</select> 
			</td>
		</tr> <?php 
	} 
}


/**
 * generate_user_preview_data
 *
 * this function here is generating a js array echoed to the page based
 * on the option values for the logged in user this only relates for the 
 * default install of tikipress feilds the frist name and lastname feilds
 * have been moved as the order is  *different from how they appear - this
 * is why there is the +2 and -2 This is scrappy and will need to be fixed
 * to work with custom feilds etc.
 * 
 * @todo tidy this messey for loop and include functionality for custom feilds
 *
 */	
function generate_user_preview_data(){

global $bp;
//if current user not registered need to use first regerstees deets :)
	$user_id = $bp->loggedin_user->id;
	$current_user_profile = bpt_get_users_profile_data($user_id) ;
	$user_email = $bp->loggedin_user->userdata->user_email;
	$twitter_id = '';
	$grav_data = bpt_conect_to_gravatar($user_email);
	$twitter_id = $grav_data['twitter'];
	$user_url = $grav_data['site url'];
	
	
	if (!$twitter_id)
		$twitter_id = "@Twitter";
	
	if(!$user_url)
		$user_url = "www.example.com";
	
	if(!$user_email)
		$user_email = "example@mail.com";
	
		echo '<script> var arrValues=new Array("'.$twitter_id.'","'.$user_url.'","'.$user_email.'",';
	for($j=0; $j < count($current_user_profile); $j++){
		if($j == count($current_user_profile)-1)
		echo '"'.$current_user_profile[2]['value'].'"';
		elseif($j == count($current_user_profile)-2)
		echo '"'.$current_user_profile[1]['value'].'",';
		elseif($j == 1)
		echo '"'.$current_user_profile[$j+2]['value'].'",';
		elseif($j == 2)
		echo '"'.$current_user_profile[$j+2]['value'].'",';
		elseif($j == count($current_user_profile)-2)
		echo '"'.$current_user_profile[1]['value'].'",';
	else
		echo '"'.$current_user_profile[$j+2]['value'].'",';
	}
	echo '); </script>';
}


/**
 * bpt_conect_to_gravatar
 *
 * @param string $user_email this is used to conect to graratar
 * @return array containg the users twitter_id and url
 *
 * This function and array can be extended to extract more data from gravatar at the moment we only require the
 * twitter information and url
 */	
 
function bpt_conect_to_gravatar($user_email){

	$usermd5=md5( strtolower( trim( $user_email ) ) );	
	$old_track = ini_set('track_errors', '1');
	
	if(!$str = @file_get_contents( 'http://www.gravatar.com/'.$usermd5.'.php' ))
		$profile=0;
	else
		$profile = unserialize( $str );
	
	ini_set('track_errors', $old_track);
	
	//check if the users have a gravatar profile before trying to get their info
	if ($profile != 'User not found'){
		$user_url=($url)?$url:$profile['entry'][0]['urls'][0]['value'];
			foreach((array)$profile['entry'][0]['accounts'] as $account){
				if($account["domain"]=="twitter.com")
					$twitter_id = $account["display"];
			}
			
	$grav_data = array(
		'twitter' => $twitter_id,
		'site url' => $user_url
		);
			
	return $grav_data;
	}
}

/**
 * bpt_preview_divs
 *
 * @return $html featuring div areas
 * @todo make the classes dynamic for when future templates are added
 *  The classes relate to the css style sheet posistion
 *
 * This function is generating the div areas over the preview template which
 * the js populates with the users ticket details as they select their badge
 * settings the div id numbers relate to the template posistion
 */	
function bpt_preview_divs(){

	$html='<div class="show_avatar">';
		$html.='<div id="show_avatar" class="" style="display: block">';
	        $html.= get_avatar( $user_email , '50' ); 
	     $html.='</div>';
	$html.='</div>';
	
	$html.='<div class="area1_container">';
	    $html.='<div id="1" class="area1" style="display: none">';
	      $html.=' <strong></strong>';
	    $html.='</div>';
	$html.='</div>';
	
	$html.='<div class="area2_container">';
	    $html.='<div id="2" class="area2" style="display: none"></div>';
	$html.='</div>';
	
	$html.='<div class="area_container">';
	   	 $html.='<div id="6" class="area6" style="display: none"></div>';
	$html.='</div>';
	
	$html.='<div class="area3_container">';
	    $html.='<div id="3" class="area3" style="display: none">';
	       $html.='<strong></strong>';
	   $html.='</div>';
	$html.='</div>';
	
	$html.='<div class="area_container">';
	    $html.='<div id="4" class="area4" style="display: none"></div>';
	$html.='</div>';
	
	$html.='<div class="area_container">';
	     $html.='<div id="5" class="area5" style="display: none">';
	       $html.='<strong></strong>'; 
	    $html.=' </div>';
	$html.='</div>';
	
	$html.='<div class="area7_container">';
		$html.=' <div id="7" class="area7" style="display: none"></div>';
	$html.='</div>';
	
	return $html;
}

/**
 * bpt_pdf
 *
 * Output attendees list PDF
 * @param $settings array selected feild ids for pdf generation
 *
 */
function bpt_pdf($settings) {

	$pdf=new PDF();
	$pdf->AliasNbPages();
	
	//load data
	$pdf->LoadData($settings);
	$pdf->SetFont( 'Arial', '', 8);
	$pdf->AddPage(L);
	$pdf->PrintTable();
	$pdf->Output();
	exit();
}


/**
 * bpt_badges_pdf 
 *
 * Output attendee badge PDF
 * @param $settings array selected feild ids for pdf generation 
 * @param $files $_FILES array for image upload
 *
 */
function bpt_badges_pdf($settings, $files){

	global $wpdb;

	@ini_set('log_errors','on');
	@ini_set('display_errors','on');
	
	require_once('bpt-functions.php');
	
	@ini_set( 'memory_limit', '128M' );
	@ini_set( 'max_input_time', '240' );
	
	// Set up the new PDF object
	$pdf = new PDF( 'L', 'in', 'Legal' );
	
	//load the attendees
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
	//for here and now we only have one template but we plan on adding more and giving the user a choice
	require('templates/sf2010.php');
	
	// Download and store the gravatar for use, FPDF does not support gravatar formatted image links the 
	// user email has been saved into array[7] ready to go just for this reason!
	$grav_file_raw = WP_CONTENT_DIR.'/plugins/'.WPSC_TICKETS_FOLDER.'/images/temp/' . $attendees[$i][0] . '-' . rand();
	$grav_url = 'http://www.gravatar.com/avatar/' . md5($attendees[$i][7]) . '?s=512&d=mm';
	$grav_data = get_file_by_curl( $grav_url, $grav_file_raw );
	
	
	if ( !$grav_file = bpt_pngtojpg($grav_file_raw) ) {
		$grav_file_extension = bpt_get_image_extension($grav_file_raw);
		$grav_file = $grav_file_raw . $grav_file_extension;
		rename( $grav_file_raw, $grav_file );
	}
	
	//if there is an image then upload it otherwise carry on
	if ($files['logo_upload']['name'] != ""){
		$back_path = $pdf->LoadBadgeImage($files);
	
	// Add the background image for the badge to the page
	$pdf->image($back_path, $background_x, $background_y, 2.8, 1.23);
	
	//set all images to the man.jpg for testing
	
	$pdf->image($grav_file, $avatar_x, $avatar_y, 0.6, 0.6);
	$pdf->SetDrawColor(187,187,187);
	$pdf->Rect($avatar_x - 0.02, $avatar_y - 0.02, 0.64, 0.64);
	
	// Set the co-ordinates, font $attendees[$i][0] [0] relates to template area 1 and so on.
	$pdf->SetXY($text_x, $text_y);
	$pdf->SetFont('helvetica','b',28);
	$pdf->SetTextColor(51,51,51);
	$pdf->MultiCell(0, 0,ucwords(stripslashes($attendees[$i][0])),0,'L');
	
	
	$pdf->SetXY($text_x, $text_y + 0.35);
	$pdf->SetFont('helvetica','',18);
	//change area two font color
	$pdf->SetTextColor(55,153,153);
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
	//change bottom fotter background color
	$pdf->SetFillColor( 55, 153, 153 );
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
}

function get_file_by_curl( $file, $newfilename ) {

    $out = fopen( $newfilename, 'wb' );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_FILE, $out );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_URL, $file );

    $return = curl_exec( $ch );
    curl_close( $ch );
}


/**
 * bpt_get_image_extension 
 *
 * @param $filename
 * @return string file extension
 *
 *Check the file extension
 *
 */
function bpt_get_image_extension($filename) {
	$type_mapping =  array( '1' => 'image/gif', '2' => 'image/jpeg', '3' => 'image/png' );
	@$size = GetImageSize( $filename );

	if ( $size[2] && $type_mapping[$size[2]] ) {
		if ( $type_mapping[$size[2]] == 'image/gif' )
		        return '.gif';

		if ( $type_mapping[$size[2]] == 'image/jpeg' )
			return '.jpg';

		if ( $type_mapping[$size[2]] == 'image/png' )
			return '.png';
	}
	return '.jpg';
}

/**
 * bpt_pngtojpg 
 *
 * @param $filename
 *
 *if the file is a png convert it to a jpeg
 */
function bpt_pngtojpg( $file ) {
	if ( bpt_get_image_extension( $file ) == '.png' ) {
		$image = imagecreatefrompng( $file );
		imagejpeg( $image, $file . '.jpg', 80 );
		return $file . '.jpg';
	} else {
		return false;
	}
}
?>