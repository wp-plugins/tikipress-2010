<?php
// Add a page, but only on a multiple of 6
if ( $counter == 1 || ( $counter % 8 == 1 ) ) {
	$pdf->AddPage('L', 'Legal');
	$counter = 1;
}
// Set the co-ordinates for all items in each of the badges
switch ( $counter ) {
	case 1:
		$background_x = 0.63;
		$background_y = 2.2;
		$avatar_x = 2.72;
		$avatar_y = 0.5;
		$text_x = 0.63;
		$text_y = 0.67;
		$line1_x = 0.63;
		$line1_y = 2.1;
		$infotext_x = 0.63;
		$infotext_y = 1.3;
		$line2_x = 0.72;
		$line2_y = 2.96;
		$years_x = .8;
		$years_y = 1.4;
		$typebox_x = 0.5;
		$typebox_y = 3.6;
	break;
	case 2:
		$background_x = 3.63;
		$background_y = 2.2;
		$avatar_x = 5.72;
		$avatar_y = 0.5;
		$text_x = 3.63;
		$text_y = 0.67;
		$line1_x = 3.63;
		$line1_y = 2.1;
		$infotext_x = 3.63;
		$infotext_y = 1.3;
		$line2_x = 3.72;
		$line2_y = 2.96;
		$years_x = 3.8;
		$years_y = 1.4;
		$typebox_x = 3.5;
		$typebox_y = 3.6;
	break;
	case 3:
		$background_x = 6.63;
		$background_y = 2.2;
		$avatar_x = 8.72;
		$avatar_y = 0.5;
		$text_x = 6.63;
		$text_y = 0.67;
		$line1_x = 6.63;
		$line1_y = 2.1;
		$infotext_x = 6.63;
		$infotext_y = 1.3;
		$line2_x = 6.72;
		$line2_y = 2.96;
		$years_x = 6.8;
		$years_y = 1.4;
		$typebox_x = 6.5;
		$typebox_y = 3.6;
	break;
	case 4:
		$background_x = 9.63;
		$background_y = 2.2;
		$avatar_x = 11.72;
		$avatar_y = 0.5;
		$text_x = 9.63;
		$text_y = 0.67;
		$line1_x = 9.63;
		$line1_y = 2.1;
		$infotext_x = 9.63;
		$infotext_y = 1.3;
		$line2_x = 9.72;
		$line2_y = 2.96;
		$years_x = 9.8;
		$years_y = 1.4;
		$typebox_x = 9.5;
		$typebox_y = 3.6;
	break;
	case 5:
		$background_x = 0.63;
		$background_y = 6.2;
		$avatar_x = 2.72;
		$avatar_y = 4.5;
		$text_x = 0.63;
		$text_y = 4.67;
		$line1_x = 0.63;
		$line1_y = 6.1;
		$infotext_x = 0.63;
		$infotext_y = 5.3;
		$line2_x = 0.72;
		$line2_y = 6.96;
		$years_x = .8;
		$years_y = 5.4;
		$typebox_x = 0.5;
		$typebox_y = 7.6;
	break;
	case 6:
		$background_x = 3.63;
		$background_y = 6.2;
		$avatar_x = 5.72;
		$avatar_y = 4.5;
		$text_x = 3.63;
		$text_y = 4.67;
		$line1_x = 3.63;
		$line1_y = 6.1;
		$infotext_x = 3.63;
		$infotext_y = 5.3;
		$line2_x = 3.72;
		$line2_y = 6.96;
		$years_x = 3.8;
		$years_y = 5.4;
		$typebox_x = 3.5;
		$typebox_y = 7.6;
	break;
	case 7:
		$background_x = 6.63;
		$background_y = 6.2;
		$avatar_x = 8.72;
		$avatar_y = 4.5;
		$text_x = 6.63;
		$text_y = 4.67;
		$line1_x = 6.63;
		$line1_y = 6.1;
		$infotext_x = 6.63;
		$infotext_y = 5.3;
		$line2_x = 6.72;
		$line2_y = 6.96;
		$years_x = 6.8;
		$years_y = 5.4;
		$typebox_x = 6.5;
		$typebox_y = 7.6;
	break;
	case 8:
		$background_x = 9.63;
		$background_y = 6.2;
		$avatar_x = 11.72;
		$avatar_y = 4.5;
		$text_x = 9.63;
		$text_y = 4.67;
		$line1_x = 9.63;
		$line1_y = 6.1;
		$infotext_x = 9.63;
		$infotext_y = 5.3;
		$line2_x = 9.72;
		$line2_y = 6.96;
		$years_x = 9.8;
		$years_y = 5.4;
		$typebox_x = 9.5;
		$typebox_y = 7.6;
	break;
}
?>