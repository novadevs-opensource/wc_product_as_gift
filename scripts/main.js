/*global wc_add_to_cart_variation_params */
;(function ( $, window, document, undefined ) {
    
    $('document').ready(function() {
        setVariant();
    });


    $('.reset_variations').on('click', function(){
        $( "#gift-switch" ).prop( "checked", false );
    });

    $('.switch-text').on('click', function() {
        // $('#gift-switch').trigger('click');
    })

    $('#gift-switch').on('click', setVariant);
    

    function setVariant(e) {

        if ($(this).is(":checked")){
            $(this).data('target', "true");
        } else {
            $(this).data('target', "false");
        }

        target = '#' + $(this).data('target');
        $hiddenTgt = $(target).data('target');
        value = $(target).data('value');
        form = $('form');


        $($hiddenTgt+' option[value='+value+']').attr('selected', true);
        form.change();
        form.find( 'input[name="variation_id"], input.variation_id' ).val( '' ).change();
		form.find( '.wc-no-matching-variations' ).remove();

		if ( form.useAjax ) {
			form.trigger( 'check_variations' );
		} else {
			form.trigger( 'woocommerce_variation_select_change' );
			form.trigger( 'check_variations' );
		}

		//Custom event for when variation selection has been changed
        form.trigger( 'woocommerce_variation_has_changed' );
        console.log('fin1');
    }
    

})( jQuery, window, document );
