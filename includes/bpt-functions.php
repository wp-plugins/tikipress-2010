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