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


/* This is used for the badge preview generator It disables each option as the template area is selected and displays a hidden div, arrValues is an array of user info generated on the badges page. */

function reEnableItems() {

    for (i = 1; i <= 14; i++) {
        var ddl;
        ddl = document.getElementById('Select' + i);
        var k = 0;

        var shdiv;
        shdiv = document.getElementById(i);
        if (shdiv) {
            shdiv.style.display = "none";
        }
      
        for (k = 0; k < ddl.length; k++) {
            ddl.options[k].removeAttribute("disabled");
        }
    }

}


function SelectBoxes(box) {
	
	reEnableItems();
	
	var background = document.getElementById('background_badge');
	background.style.display = "none";
	
	var i = 0;
	for (i = 1; i <= 14; i++) {
		var ddl;
		ddl = document.getElementById('Select' + i);
		
		if(ddl){
			var shdiv;
			shdiv = document.getElementById(ddl.value);
			
			if (shdiv) {
				shdiv.innerHTML=arrValues[i-1];
				shdiv.style.display = "block";
			}
			
			var j = 0;
			for (j = 1; j <= 14; j++) {
				var ddl2
				ddl2 = document.getElementById('Select' + j);
				
				var k = 0;
				for (k = 0; k < ddl2.length; k++) {
				
					
					if (ddl.value == ddl2.options[k].value 	&& ddl2.options[k].value != 'exclude'){
						ddl2.options[k].setAttribute("disabled","disabled");
					}
						 if (ddl.value == ddl2.options[k].value && box == ddl2){
						 	ddl.options[k].removeAttribute("disabled");
	                       	ddl.options[k].setAttribute("selected","selected");
	                       		
	                     }
				}
			}
		}
	}
}


/* show hide avatar */
jQuery(document).ready(function(){
		
		jQuery("#show_avatar").hide(10);
  		
         jQuery("tr#grav input:radio:eq(0)").click(function(){
             jQuery("#show_avatar").show(10);
          });

          jQuery("tr#grav input:radio:eq(1)").click(function(){
             jQuery("#show_avatar").hide(10);
          });         
});