/*global wc_add_to_cart_variation_params */
;(function ( $, window, document, undefined ) {
    
    $('document').ready(function(){
        $('#product-combinations button').on('click', setVariant);
    });


    $('.reset_variations').on('click', function(){
        $('#product-combinations button').removeClass('btn-danger');
        $('#product-combinations button').removeClass('btn-success');
    });
    

    function setVariant(e) {
        e.preventDefault();
        target = $(this).data('target');
        value = $(this).data('value');
        form = $('form');

        $('#product-combinations button').removeClass('active');
        $(this).addClass('active');
        if (value == "no" || value == 0) {
            $('#product-combinations button').removeClass('btn-danger');
            $('#product-combinations button').removeClass('btn-success');
            if ($(this).hasClass('active')) {
                $(this).addClass('btn-danger');
            }
        } else {
            $('#product-combinations button').removeClass('btn-danger');
            $('#product-combinations button').removeClass('btn-success');
            if ($(this).hasClass('active')) {
                $(this).addClass('btn-success');
            }
        }

        $(target+' option[value='+value+']').attr('selected', true);
        form.change();
        console.log(target+' option[value='+value+']');

        form.find( 'input[name="variation_id"], input.variation_id' ).val( '' ).change();
		form.find( '.wc-no-matching-variations' ).remove();

		if ( form.useAjax ) {
			form.trigger( 'check_variations' );
		} else {
			form.trigger( 'woocommerce_variation_select_change' );
			form.trigger( 'check_variations' );
		}

		// Custom event for when variation selection has been changed
        form.trigger( 'woocommerce_variation_has_changed' );
        console.log('fin1');
    }
    

})( jQuery, window, document );
