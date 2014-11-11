/* Theam switcher js file */
jQuery(document).ready(function() {
// Pattern Selector function//////////////////////////////////	
	jQuery('.patterns a').click(function(e) {
		e.preventDefault();
			jQuery(this).parent().find('img').removeClass('active');
			jQuery(this).find('img').addClass('active');

			var name = jQuery(this).attr('name');
				jQuery('body').css('background', 'url(themes/switch/images/pattern/'+name+'.png) repeat center center scroll');
				jQuery('body').css('background-size', 'auto');
	});
// Style Selector function ////////////////////////////////////
	jQuery('.style a').click(function(e) {
		e.preventDefault();
		jQuery(this).parent().find('img').removeClass('active');
		jQuery(this).find('img').addClass('active');

		var name = jQuery(this).attr('name');

		if(name == 'green') {
			jQuery('#callCss').attr('href', '');
		} else {
			jQuery('#callCss').attr('href', 'themes/'+name+'/bootstrap.min.css');
		}

	});
	
	/* Settings Button */
	$('#themesBtn').click(function() {
	  $('#secectionBox').animate({
		right:'0'
	  }, 500, function() {
		// Animation complete.
	  });
	  $('#themesBtn').animate({
		right:'-80'
	  }, 100, function() {
		// Animation complete.
	  });
	}); 


	$('#hideme').click(function() {
		$('#secectionBox').animate({
		right:'-999'
	  }, 500, function() {
		// Animation complete.
	  });
	  
	  $('#themesBtn').animate({
		right:'0'
	  }, 700, function() {
		// Animation complete.
	  }); 
	});

});


