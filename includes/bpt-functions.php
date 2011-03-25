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