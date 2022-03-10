(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function() {
		var qty = 1;
		var yp_link = $('#youpay-share-box a').attr('href');

		//check if youpay share box exists
		if($("#youpay-share-box").length){
			
			$('input.variation_id').change(function(){
				if( '' != $(this).val() ) {
					//quantity
					if ($('input[name="quantity"]').length){
						qty = parseInt($('input[name="quantity"]').val());
					}
				   	var variant_id = parseInt($(this).val());
				   	//add variant id to yp callback				   
				   	$('#youpay-share-box a').attr('href',yp_link + '&variant_id='+variant_id+'&qty='+qty);
					$('#youpay-share-box a').removeAttr('onClick');
					//enabled button
				   	$('#youpay-share-box a').removeAttr('disabled');
				}
			});
		}

		//check for qty change
		if ($('input[name="quantity"]').length){
			$('input[name="quantity"]').change(function(){
				qty = parseInt($('input[name="quantity"]').val());
				//check for variation
				if($('input.variation_id').length) {
				   	var variant_id = parseInt($('input.variation_id').val());
				   	//add variant id to yp callback				   
				   	$('#youpay-share-box a').attr('href',yp_link + '&variant_id='+variant_id+'&qty='+qty);
				}else{
					$('#youpay-share-box a').attr('href',yp_link + '&qty='+qty);
				}
				$('#youpay-share-box a').removeAttr('onClick');
				//enabled button
				$('#youpay-share-box a').removeAttr('disabled');
				
			});
		}


		//if variant product, disable youpay button until variant selected
		if($('input.variation_id').length){
			if($('input.variation_id').val() == 0){
				$('#youpay-share-box a').removeAttr('href');
				$('#youpay-share-box a').attr('onClick','return false;');
			}
		}
	});

})( jQuery );
