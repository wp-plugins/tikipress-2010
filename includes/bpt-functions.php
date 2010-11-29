<?php
function get_file_by_curl( $file, $newfilename ) {
//	exit('Under Construction'.$newfilename);
    $out = fopen( $newfilename, 'wb' );
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_FILE, $out );
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
    curl_setopt( $ch, CURLOPT_HEADER, 0 );
    curl_setopt( $ch, CURLOPT_URL, $file );

    $return = curl_exec( $ch );
    curl_close( $ch );
//    exit($return);
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