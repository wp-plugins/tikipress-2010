jQuery(document).ready( function() {

	jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

	jQuery('div.initially-hidden').each( function() { 
		jQuery(this).hide();
	});

	jQuery('#bpt-admin-metaboxes-general input').click( function() {
		var button = jQuery(this);
		var config = jQuery('div.setting-' + button.attr('class'));

		if ( 1 == button.attr('value') )
			config.css('background-color', 'rgb(255,255,224)').slideDown('fast').animate( { backgroundColor: 'rgb(255,255,255)' }, 1600);
		else
			config.stop(true).slideUp();
	});

});