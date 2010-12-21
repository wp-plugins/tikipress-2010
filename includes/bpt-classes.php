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

	//$wpdb->get_results( $wpdb->prepare( "delete from " . WPSC_TABLE_META . " where meta_id = %d limit 1", $purchase_id ) );
	return apply_filters( 'bpt_is_ticket_code_valid', $purchase_id );
}

//gets the users tikipress feilds
function bpt_get_users_profile_data($user_id){
	global $wpdb;
	$sql =$wpdb->prepare('SELECT `field_id` , `value` FROM `'.$wpdb->prefix.'bp_xprofile_data` WHERE `user_id` = '.$user_id);
	//echo exit($sql);
	return apply_filters( 'bpt_get_users_profile_data', $wpdb->get_results( $sql, ARRAY_A ) );
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
	$sql = "SELECT wp_terms.name, wp_terms.term_id FROM wp_term_taxonomy LEFT JOIN wp_terms ON wp_term_taxonomy.term_id = wp_terms.term_id WHERE wp_term_taxonomy.taxonomy = 'wpsc_product_category'";
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

//is required compaires the post array with all the possible options for badges and returns an array 1-7 of the selected option ids this can then be used to load the matching user data
	function isRequired($arr1, $arr2){
		$requiredItems = $arr1['bpt']['fields'];
		//echo '<pre>' . print_r ($requiredItems,1) . '</pre>';
		for ($i = 1; $i <= count($requiredItems); $i++){
			if($arr2['field_id']== $requiredItems[$i])
				return $i;
		}
		return -1;
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
	$users_data = array();
		
	foreach ($user_ids as $user_id){
	
		$twitter_id = '';
		$user = get_userdata($user_id);
		$user_email = $user->user_email;
		//set up default aray 7 badge template areas array7 is just for the email address.
		$user_data = array(0=>'',1=>'',2=>'',3=>'',4=>'',5=>'',6=>'',7=>'');
		$field_data = bpt_get_users_profile_data($user_id);
		
		
		
	/* connect to gravatar to get the user site address, twitter id*/

		$usermd5=md5( strtolower( trim( $user->user_email ) ) );	
		$old_track = ini_set('track_errors', '1');
		if(!$str = @file_get_contents( 'http://www.gravatar.com/'.$usermd5.'.php' ))
			$profile=0;
		else
			$profile = unserialize( $str );
		
		ini_set('track_errors', $old_track);
		
		$user_url=($url)?$url:$profile['entry'][0]['urls'][0]['value'];
		
			foreach((array)$profile['entry'][0]['accounts'] as $account){
				if($account["domain"]=="twitter.com")
					$twitter_id = $account["display"];
			}
		

		foreach ($field_data as $data){
		//is required checks if the field name or id is in the post array for the template
			$templatePosistion = $this->isRequired($_POST, $data);
			
			if ($templatePosistion > -1)
	        	$user_data[$templatePosistion-1]=$data['value'];
	      
	           //j loop is doing the feilds i loop is doing the users
			//echo '<pre>' . print_r($data,true). '</pre>';
		}
			
			for ($j=1; $j<= count($_POST['bpt']['fields']); $j++){
			
				switch (trim($_POST['bpt']['fields'][$j])){
					case 'badges_twitter':
						$user_data[$j-1] = $twitter_id;
						break;
					case 'badges_site':
						$user_data[$j-1] = $user_url;
						break;
					case 'badges_email':
						$user_data[$j-1] = $user_email;
						break;
				}
	
			}
		//put user email  into array at posistion 7 if true will get avatar out with this
		if ($_POST['badges_gravatar']==1)
			$user_data[7]= $user_email;
			//$user_data[7]= get_avatar( $user->user_email , '50' );
			
		$users_data[$i] = $user_data;
		$i++;	
	}
	return $users_data;
}
//loads the pdf data for the attendee infomation	
function LoadData( $settings)
	{
		global $wpdb;
		$event_id = $_POST['events_dropdown'];
		
		$headers = $wpdb->get_results( 'SELECT `fields`.`name`, `fields`.`id` FROM `' . $wpdb->prefix . 'bp_xprofile_fields` `fields` WHERE `fields`.`id` IN (' . implode( ',', $settings['fields'] ) . ')' );
		
		 $product_id = bpt_wpsc_get_product_id_from_event($event_id);

		$users = bpt_get_registered_users( $product_id, true );

		$users_last_name=$wpdb->get_col( 'SELECT `users`.`id` FROM `' . $wpdb->prefix . 'users` `users` JOIN `' . $wpdb->prefix . 'usermeta` `meta` ON users.id = `meta`.user_id WHERE meta_key = "last_name" AND `users`.`id` IN (' . implode( ',', $users ) . ') ORDER BY meta_value ASC' );
		
		$users_no_lastname = $wpdb->get_col( 'SELECT `users`.`id` FROM `' . $wpdb->prefix.'users` `users` WHERE `users`.`id` IN (' . implode( ',', $users ) . ') AND `users`.`id` NOT IN (' . implode( ',', $users_last_name ) . ') ORDER BY user_login ASC' );
		
		$users = array_merge( $users_last_name, $users_no_lastname );
		$this->bpt_data = array( 'headers' => $headers, 'users' => $users );
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
	    $this->Ln();
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
		    $this->Ln();
		}

	}
		
	//Colored table
	function PrintTable()
	{
		global $wpdb;
	
	    $width=floor(250 / count($this->bpt_data['headers']));
	    //Color and font restoration
	    $this->SetFillColor(224);
	    $this->SetTextColor(0);
	    $this->SetFont('','','8');
	    //Data
	    $fill=false;
	 	//exit('<pre>'.print_r($this->bpt_data,true) . '</pre>');
		$x = $this->GetX();
		$y = $this->GetY();
		$leftMargin = $x;
		//row
	    foreach((array)$this->bpt_data['users'] as $user)
	    {	
	    //collums
	    	foreach($this->bpt_data['headers'] as $column){
	    	
			    $this->SetY($y); //set pointer back to previous values
				$this->SetX($x);
				$x=$this->GetX()+$width;
				$y=$this->GetY();
	    	
	    		$value=$wpdb->get_var('SELECT `data`.`value` FROM `'.$wpdb->prefix.'bp_xprofile_data` `data` WHERE `data`.`user_id` = '.$user.'  AND `data`.`field_id` ='. $column->id);
	    		$value=maybe_unserialize($value);
	    	
	    //$w=$this->GetStringWidth($value)+6;
	    	
	    		if(is_array($value))
	    			$value=implode(', ', $value);
	    		$this->MultiCell($width,6,$value,'LR',0,'L',$fill);
	    		//$this->Cell(20,10,'Title',1,1,'C');
	    	}
	    	
	    	$y += 6;
	    	$x = $leftMargin; // after columns set next line left margin to orginal margin.
	    	
	    	$this->Ln();
		    $fill=!$fill;
	    }
	}
}
?>