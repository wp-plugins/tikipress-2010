<?php
function bpt_is_ticket_code_valid( $code ) {
	global $bp, $current_user, $wpdb;

	get_currentuserinfo();
	$purchase_id = 0;

	$matches = $wpdb->get_results( $wpdb->prepare( "select meta_id, meta_value, object_id from " . WPSC_TABLE_META . " as meta where object_type = %s and meta_key = %s", 'bp_ticketing', wpsc_sanitize_meta_key( $current_user->user_email ) ) );
	foreach ( (array)$matches as $match ) {
		if ( $match->meta_value == $code ) {
			$purchase_id = $match->object_id;
			break;
		}
	}

	if ( !$purchase_id )
		return apply_filters( 'bpt_is_ticket_code_valid', false );
	return apply_filters( 'bpt_is_ticket_code_valid', $purchase_id );
}

function bpt_is_wpec_3_8(){
	if((float)WPSC_VERSION >= 3.8)
		return true;
	else
		return false;
}


/**
 * bpt_get_all_eventpress_event_details
 *
 * @return array containing details of all the events created with eventpress
 */
function bpt_get_all_eventpress_event_details(){
	global $wpdb;
	$sql = $wpdb->prepare("SELECT `posts`.`ID`, `posts`.`post_title`, `posts`.`post_status` FROM `" . $wpdb->prefix . "posts` `posts` WHERE `posts`.`post_type` = 'ep_event'");
	$events = $wpdb->get_results( $sql ) ;
	return apply_filters( 'bpt_get_all_event_details', $wpdb->get_results( $sql ) );
}

/**
 * Get ticket fields
 *
 * Selects all the buddy press profile fields that are used for tickets
 * @return $fields array of ticket fields
 */
function bpt_get_ticket_fields() {
	global $wpdb, $bp;
	$fields=$wpdb->get_results( 'SELECT `fields`.`name`, `fields`.`id` FROM `' . $bp->table_prefix . 'bp_xprofile_fields` `fields` WHERE `fields`.`parent_id`=0' );
	return $fields;
}


/**
 * bpt_get_ticket_total
 *
 * @param int $product_id this product/ticket ID
 * @return Ticket total int wpec stock quantity for this product
 */
function bpt_get_ticket_total($product_id){
	global $wpdb;
	
	if(bpt_is_wpec_3_8())
		$ticket_total = get_post_meta($product_id, '_wpsc_stock', true);
	else
		$ticket_total = $wpdb->get_var( 'SELECT `quantity` FROM `'.$wpdb->prefix . 'wpsc_product_list` WHERE `id` = '.$product_id) ;
	
	return $ticket_total;
}



/**
 * bpt_get_users_profile_data
 *
 * @param int $user_id
 * @return array containing field_id and value
 */
function bpt_get_users_profile_data($user_id){
	global $wpdb, $bp;
	$sql =$wpdb->prepare('SELECT `field_id` , `value` FROM `'.$bp->table_prefix.'bp_xprofile_data` WHERE `user_id` = '.$user_id);
	return apply_filters( 'bpt_get_users_profile_data', $wpdb->get_results( $sql, ARRAY_A ) );
}

/**
 * bpt_get_user_profile_data
 *
 * @param int $user_id
 * @return array containing field name and value
 */
function bpt_get_user_profile_data($user_id){
	global $wpdb, $bp;
	$sql =$wpdb->prepare('SELECT `fields`.`name`, `data`.`value` FROM `' . $bp->table_prefix . 'bp_xprofile_data` `data` INNER JOIN `' . $bp->table_prefix . 'bp_xprofile_fields` `fields` ON `data`.`field_id` = `fields`.`id` WHERE `data`.`user_id`=' . $user_id . ' AND `fields`.`name` != "Name"');
	return apply_filters( 'bpt_get_user_profile_data', $wpdb->get_results( $sql ) );

}

function bpt_get_ticket_purchases( $user_id=0 ) {
	global $bp, $wpdb;

	if ( !$user_id )
	 	$user_id = $bp->loggedin_user->id;

	$sql = $wpdb->prepare( "select cart.id, cart.prodid as product_id, cart.name as product_name from " . WPSC_TABLE_PURCHASE_LOGS . " as logs, " . WPSC_TABLE_CART_CONTENTS . " as cart,  " . WPSC_TABLE_ITEM_CATEGORY_ASSOC . " as category WHERE logs.id = cart.purchaseid AND cart.prodid = category.product_id AND category.category_id = %d AND logs.user_ID = %d", bpt_get_ticketing_category(), $user_id );
	return apply_filters( 'bpt_get_ticket_purchases', $wpdb->get_results( $sql ), $user_id );
}

function bpt_wpsc_get_product_meta( $product_id, $meta_key ) {
	global $wpdb;

	$sql = $wpdb->prepare( "select meta_value from " . WPSC_TABLE_PRODUCTMETA . " where product_id = %d and meta_key = %s", $product_id, $meta_key );
	return apply_filters( 'bpt_wpsc_get_product_meta', maybe_unserialize( $wpdb->get_var( $sql ) ), $product_id, $meta_key );
}

//returns the product id (ticket product) when we know the eventid
function bpt_wpsc_get_product_id_from_event( $event_id) {
	global $wpdb;

	$sql = $wpdb->prepare( "
	SELECT `post_id`
	FROM `".$wpdb->postmeta."`
	WHERE `meta_value` = $event_id
	AND `meta_key` = '_bpt_event_prod_id'");
	return apply_filters( 'bpt_wpsc_get_registered_users_by_event', maybe_unserialize( $wpdb->get_var( $sql ), $event_id ));
}


// This taken from tikipress
function bpt_wpsc_get_categories() {
	global $wpdb;

	if((float)WPSC_VERSION >= 3.8 ){
	$sql = "SELECT ".$wpdb->prefix."terms.name, ".$wpdb->prefix."terms.term_id FROM ".$wpdb->prefix."term_taxonomy LEFT JOIN ".$wpdb->prefix."terms ON ".$wpdb->prefix."term_taxonomy.term_id = ".$wpdb->prefix."terms.term_id WHERE ".$wpdb->prefix."term_taxonomy.taxonomy = 'wpsc_product_category'";
	}else {
		$sql = "SELECT `id`, `name` FROM `" . WPSC_TABLE_PRODUCT_CATEGORIES . "` WHERE `active`='1'";
	}
	return apply_filters( 'bpt_wpsc_get_categories', $wpdb->get_results( $sql, ARRAY_A ) );
}

//Returns ticket name for use in the emails
function bpt_get_product_name( $purchase_id ) {
	global $wpdb;

	$sql = $wpdb->prepare( "select cart.name from " . WPSC_TABLE_PURCHASE_LOGS . " as logs, " . WPSC_TABLE_CART_CONTENTS . " as cart WHERE logs.id = cart.purchaseid and logs.id = %d", $purchase_id );
	return apply_filters( 'bpt_get_product_name', $wpdb->get_var( $sql ), $purchase_id );
}

//get the product id from the purchase id
function bpt_get_product_id( $purchase_id ) {
	global $wpdb;

	$sql = $wpdb->prepare( "select cart.prodid from " . WPSC_TABLE_PURCHASE_LOGS . " as logs, " . WPSC_TABLE_CART_CONTENTS . " as cart WHERE logs.id = cart.purchaseid and logs.id = %d", $purchase_id );
	return apply_filters( 'bpt_get_product_name', $wpdb->get_var( $sql ), $purchase_id );
}

//used to select users that have registered their ticket details
function bpt_get_registered_users($product_id, $id_only=false){
	global $wpdb;
	$tickets=array();
	foreach((array)$product_id as $prod){
		$tickets[]='"ticket'.$prod.'"';
	}
	if($id_only){
		$columns = '`users`.`ID`';
	}else{
		$columns = '`users`.`ID`, `users`.`display_name`, `users`.`user_email`';
	}
	$sql = $wpdb->prepare( 'SELECT DISTINCT
		 '.$columns.'
		 FROM `'.$wpdb->users.'` `users`
		 INNER JOIN `'.$wpdb->usermeta.'` `usermeta` ON `users`.`ID` = `usermeta`.`user_id`
		 WHERE `users`.`ID` IN
		 (
			 SELECT DISTINCT
			 `users`.`ID`
			 FROM `'.$wpdb->users.'` `users`
			 INNER JOIN `'.$wpdb->usermeta.'` `usermeta` ON `users`.`ID` = `usermeta`.`user_id`
			 LEFT JOIN `'.$wpdb->prefix.'wpsc_purchase_logs` `purch_logs` ON `users`.`ID` = `purch_logs`.`user_ID`
			 INNER JOIN `'.$wpdb->prefix.'wpsc_cart_contents` `cart_cont`  ON `purch_logs`.`id` = `cart_cont`.`purchaseid`
			 WHERE `cart_cont`.`prodid` IN ('.implode(',',(array)$product_id).') AND `purch_logs`.`processed` IN (2, 3, 4)
		 )
		 OR 
		 (`usermeta`.`meta_key` IN ('.implode(',', $tickets).'))' );
	if($id_only){
		return $wpdb->get_col( $sql );
	}else{
		return $wpdb->get_results( $sql );
	
	}

}


/*
 *  Class that forms PDF for output
 *	uses fpdf.php lib included with wp-e-commerce
 */

if( ! WPSC_FILE_PATH ) {
	wp_die('Please install wp-e-commerce first!');
}

include_once( WPSC_FILE_PATH . '/wpsc-includes/fpdf/fpdf.php' );
 
class PDF extends FPDF
{
	var $bpt_data;

function LoadBadgeImage($files){

//exit('<pre> imagesss'.print_r($files,1).'</pre>');

if((!empty($files["logo_upload"])) && ($files['logo_upload']['error'] == 0)) {
  //Check if the file is JPEG image and it's size is less than 350Kb
  $filename = basename($_FILES['logo_upload']['name']);
  $ext = substr($filename, strrpos($filename, '.') + 1);
  if (($ext == "jpg") && ($_FILES["logo_upload"]["type"] == "image/jpeg") && 
    ($_FILES["logo_upload"]["size"] < 350000)) {
    //Determine the path to which we want to save this file
      $newname = WP_CONTENT_DIR.'/plugins/'.WPSC_TICKETS_FOLDER.'/images/'.$filename;
      //Check if the file with the same name is already exists on the server
      if (!file_exists($newname)) {
        //Attempt to move the uploaded file to it's new place
        if ((move_uploaded_file($files['logo_upload']['tmp_name'],$newname))) {
           return $newname;
        } else {
           echo "Error: A problem occurred during file upload!";
        }
      } else {
         return $newname;
      }
  } else {
     echo "Error: Only .jpg images under 350Kb are accepted for upload";
  }
} else {
 echo "Error: No file uploaded";
}



}

function LoadBadgesData($settings){
	global $wpdb;
/*commenting this out as it will break - incompleted code
	if ($_POST['fUpload'])
		$this->upload_logo();
*/
	//echo('<pre>'.print_r($_POST,1).'</pre>');
	$event_id = $_POST['events_dropdown'];
	$product_id = bpt_wpsc_get_product_id_from_event($event_id);
	$user_ids = bpt_get_registered_users( $product_id, true );
		
	$i=0;
	
	//dont need this line?
	$users_data = array();
		
	foreach ($user_ids as $user_id){
	
		$twitter_id = '';
		$user = get_userdata($user_id);
		$user_email = $user->user_email;
		//set up default aray 7 badge template areas array7 is just for the email address.
		$user_data = array(0=>'',1=>'',2=>'',3=>'',4=>'',5=>'',6=>'',7=>'');
		$template_data = array(0=>'',1=>'',2=>'',3=>'',4=>'',5=>'',6=>'',7=>'');
		$field_data = bpt_get_users_profile_data($user_id);
		
		
		
	/* connect to gravatar to get the user site address, twitter id*/
	$grav_data = bpt_conect_to_gravatar($user_email);
	$twitter_id = $grav_data['twitter'];
	$user_url = $grav_data['site url'];
	
			//$fields = $settings;
		$fields = $_POST['bpt']['fields'];

		//remove excluded feilds from final array
		$excluded = array_keys($fields,'exclude');

		foreach($excluded as $exclude){
			unset($fields[$exclude]);
		}
		//put the field vales that are been included into the template array we want to make the feild value as the key and the key as the value
		foreach ($fields as $key => $val) {
    		$template[$val-1]=$key;
    	}

		foreach ($field_data as $data){
		//j is looping the feilds the count 7 relates to the template posistions will need to create this dynamically if more templates are introuduced.
			for ($j=0; $j<=7; $j++){
				switch ($template[$j]){
					case 'badges_twitter':
						if (!$twitter_id)
							$twitter_id = "";
						$user_data[$j] = $twitter_id;
						break;
					case 'badges_site':
						if (!$user_url)
							$user_url = "";
						$user_data[$j] = $user_url;
						break;
					case 'badges_email':
						$user_data[$j] = $user_email;
						break;
					default:
						if($data['field_id'] == $template[$j]){
							$value =$data['value'];
							$user_data[$j] = $value;
						}
						
					break;
						
				}
	
			}	
	      
		}
		//put user email  into array at posistion 7 if true will get avatar out with this
		if ($_POST['badges_gravatar']==1)
			$user_data[7]= $user_email;

		$users_data[$i] = $user_data;
		$i++;	
	}
		
	return $users_data;
}


function LoadData( $settings )
	{
		global $wpdb, $bp;
		$event_id = $_POST['events_dropdown'];
		
		$headers = $wpdb->get_results( 'SELECT `fields`.`name`, `fields`.`id` FROM `' . $bp->table_prefix . 'bp_xprofile_fields` `fields` WHERE `fields`.`id` IN (' . implode( ',', $settings['fields'] ) . ')' );
		
		 $product_id = bpt_wpsc_get_product_id_from_event($event_id);

		$users = bpt_get_registered_users( $product_id, true );
		//exit('<pre> hello'.print_r($users,1).'</pre>');
		//$users_last_name=$wpdb->get_col( 'SELECT `users`.`id` FROM `' . $bp->table_prefix . 'users` `users` JOIN `' . $wpdb->prefix . 'usermeta` `meta` ON users.id = `meta`.user_id WHERE meta_key = "last_name" AND `users`.`id` IN (' . implode( ',', $users ) . ') ORDER BY meta_value ASC' );
		
		//$users_no_lastname = $wpdb->get_col( 'SELECT `users`.`id` FROM `' . $bp->table_prefix.'users` `users` WHERE `users`.`id` IN (' . implode( ',', $users ) . ') AND `users`.`id` NOT IN (' . implode( ',', $users_last_name ) . ') ORDER BY user_login ASC' );
		
		//$users = array_merge( $users_last_name, $users_no_lastname );
		$this->bpt_data = array( 'headers' => $headers, 'users' => $users );
		//exit('<pre> hello'.print_r($this->bpt_data,1).'</pre>');
	}

	function Header(){
	/* check if header has been called from pdf tab and not badges, badges do not require headers but they are called automatically */
		if ($header=$this->bpt_data['headers']) {
	     //Colors, line width and bold font
	    $this->SetFillColor(120);
	    $this->SetTextColor(255);
	    $this->SetDrawColor(0);
	    $this->SetLineWidth(.3);
	    $this->SetFont( '','B','8' );
	    
	    $x = $this->GetX();
		$y = $this->GetY();
		$leftMargin = $x;
		
	    //Header
	    $header=$this->bpt_data['headers'];
	    
	   // exit('<pre>'.print_r($header,true).'</pre>');
	   //	$w=$this->GetStringWidth($header[$i]->name)+6;

		//$this->SetX((210-$w)/2);
	   //$w=$this->GetStringWidth($header)+6;
	    $width=floor(250 / count($header));
	    for($i=0;$i<count($header);$i++){
	    
	      		$this->SetY($y); //set pointer back to previous values
				$this->SetX($x);
				$x=$this->GetX()+$width;
				$y=$this->GetY();
				
	        $this->MultiCell($width,7,$header[$i]->name,1,0,'C',true);
	   // $this->Ln();
	   	 }
	    }
	}

/* do the same check for footer - we don't want this on our badges! */
	function Footer(){
		if ($header=$this->bpt_data['headers']){
		     //Colors, line width and bold font
		    $this->SetFillColor(120);
		    $this->SetTextColor(255);
		    $this->SetDrawColor(0);
		    $this->SetLineWidth(.3);
		    $this->SetFont('','B','8');
		    //Header
		    $header=$this->bpt_data['headers'];
		   // $w=$this->GetStringWidth($header)+6;
		 $w=floor(250 / count($header));
		    for($i=0;$i<count($header);$i++)
		        $this->Cell($w,7,$header[$i]->name,1,0,'C',true);
		    $this->Ln();
		    $this->SetFillColor(255);
		    $this->SetTextColor(0);
		    $this->SetDrawColor(0);
		    $this->SetLineWidth(0);
		    $this->MultiCell($w,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		   // $this->Ln();
		}

	}
	

		//Colored table
	function PrintTable()
		{
		global $wpdb, $bp;
		 
	    $width=floor(250 / count($this->bpt_data['headers']));
	    //Color and font restoration
	    $this->SetFillColor(224);
	    $this->SetTextColor(0);
	    $this->SetFont('','','8');
	    //Data
	    
	 //exit('<pre>'.print_r($this->bpt_data,true) . '</pre>');
		$fill=false;
		//row
	    foreach((array)$this->bpt_data['users'] as $user)
	    {	
	    //collums
	    $col = 0;
	    	foreach($this->bpt_data['headers'] as $column){
		    	if($col){
		    		$x = $this->GetX();
		    		$y = $this->GetY();
		    		$this->SetY($y-6);
		    		$this->SetX($x+($col*$width));
		    	}
		    	$col++;
	    		$value=$wpdb->get_var('SELECT `data`.`value` FROM `'.$bp->table_prefix.'bp_xprofile_data` `data` WHERE `data`.`user_id` = '.$user.'  AND `data`.`field_id` ='. $column->id);
	    		$value=maybe_unserialize($value);
	    	$fill=false;
	    		if(is_array($value))
	    			$value=implode(', ', $value);
	    		$this->MultiCell($width,6,$value,'LR',0,'L',$fill);
	    	}
	 		
		    $fill != $fill;
	    }
	}
}

/**
 * Create counter for tickets sold..
 * This function is not called anywhere but I didnt want to remove it just encase it has later use
 *
 */
/*
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
*/