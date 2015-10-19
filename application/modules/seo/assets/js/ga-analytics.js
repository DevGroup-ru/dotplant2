'use strict';
jQuery(function($){
    var $body = $('body');

    $body.on('addToCart', function(data) {
        var $data = data.orderData.additional;
        if ($data.hasOwnProperty('ecGoogle')) {
            try {
                $data = $data.ecGoogle;

                ga('set', '&cu', $data.currency);
                ga('ec:addProduct', $data.products);
                ga('ec:setAction', 'add');
                ga('send', 'event', 'UX', 'click', 'add to cart');
            } catch (e) {console.log('Google e-commerce error: ' + e);}
        }
    });

    $body.on('removeFromCart', function(data) {
        var $data = data.orderData.additional;
        if ($data.hasOwnProperty('ecGoogle')) {
            try {
                $data = $data.ecGoogle;

            } catch (e) {console.log('Google e-commerce error: ' + e);}
        }
    });
}(jQuery));
