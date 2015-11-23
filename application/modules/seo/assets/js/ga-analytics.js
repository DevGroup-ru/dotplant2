'use strict';
jQuery(function($){
    var $body = $('body');

    $body.on('addToCart', function(data) {
        var $data = data.orderData.additional;
        if ($data.hasOwnProperty('ecGoogle')) {
            try {
                $data = $data.ecGoogle;

                ga('set', '&cu', $data.currency);
                for(var i = 0; i < $data.products.length; i++) {
                    ga('ec:addProduct', $data.products[i]);
                    ga('ec:setAction', 'add');
                    ga('send', 'event', 'UX', 'click', 'add to cart');
                }
            } catch (e) {console.log('Google e-commerce error: ' + e);}
        }
    });

    $body.on('removeFromCart', function(data) {
        var $data = data.orderData.additional;
        if ($data.hasOwnProperty('ecGoogle')) {
            try {
                $data = $data.ecGoogle;

                ga('set', '&cu', $data.currency);
                for(var i = 0; i < $data.products.length; i++) {
                    ga('ec:addProduct', $data.products[i]);
                    ga('ec:setAction', 'remove');
                    ga('send', 'event', 'UX', 'click', 'remove from cart');
                }
            } catch (e) {console.log('Google e-commerce error: ' + e);}
        }
    });

    $body.on('cartChangeQuantity', function(data) {
        var $data = data.orderData.additional;
        if ($data.hasOwnProperty('ecGoogle')) {
            try {
                $data = $data.ecGoogle;

                ga('set', '&cu', $data.currency);
                $($data.products).each(function(k, v) {
                    if (v.quantity > 0) {
                        ga('ec:addProduct', v);
                        ga('ec:setAction', 'add');
                        ga('send', 'event', 'UX', 'click', 'add to cart');
                    } else {
                        v.quantity = -1 * v.quantity;
                        ga('ec:addProduct', v);
                        ga('ec:setAction', 'remove');
                        ga('send', 'event', 'UX', 'click', 'remove from cart');
                    }
                });
            } catch (e) {console.log('Google e-commerce error: ' + e);}
        }
    });

    $body.on('clearCart', function(data) {
        var $data = data.orderData.additional;
        if ($data.hasOwnProperty('ecGoogle')) {
            try {
                $data = $data.ecGoogle;

                ga('set', '&cu', $data.currency);
                ga('ec:addProduct', $data.products);
                ga('ec:setAction', 'remove');
                ga('send', 'event', 'UX', 'click', 'clear cart');
            } catch (e) {console.log('Google e-commerce error: ' + e);}
        }
    });

    if (window.DotPlantParams.hasOwnProperty('ecGoogle')) {
        try {
            var $data = window.DotPlantParams.ecGoogle;

            if ('detail' === $data.action) {
                ga('set', '&cu', $data.currency);
                ga('ec:addProduct', $data.products);
                ga('ec:setAction', 'detail');
                ga('send', 'pageview');
            } else if ('action' === $data.action) {
                // each cart action should have list of product for ga
                ga('set', '&cu', $data.currency);
                for(var i = 0; i < $data.products.length; i++){
                    ga('ec:addProduct', $data.products[i]);
                }
                ga('ec:setAction', $data.type, {
                    'step': $data.step
                });
                ga('send', 'pageview');
            }
        } catch (e) {console.log('Google e-commerce error: ' + e);}
    }
}(jQuery));
