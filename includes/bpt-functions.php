<?php
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

function get_image_extension($filename) {
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

function pngtojpg( $file ) {
	if ( get_image_extension( $file ) == '.png' ) {
		$image = imagecreatefrompng( $file );
		imagejpeg( $image, $file . '.jpg', 80 );
		return $file . '.jpg';
	} else {
		return false;
	}
}
?>