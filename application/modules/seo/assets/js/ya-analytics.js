'use strict';
jQuery(function($){
    window.dataLayer = window.dataLayer || [];

    var $body = $('body');

    $body.on('addToCart', function(data) {
        var $data = data.orderData.additional;
        if ($data.hasOwnProperty('ecYandex')) {
            try {
                $data = $data.ecYandex;
                window.dataLayer.push({
                    'ecommerce': {
                        'currencyCode': $data.currency,
                        'add': {'products': $data.products}
                    }
                });
            } catch (e) {console.log('Yandex e-commerce error: ' + e);}
        }
    });

    $body.on('removeFromCart', function(data) {
        var $data = data.orderData.additional;
        if ($data.hasOwnProperty('ecYandex')) {
            try {
                $data = $data.ecYandex;
                window.dataLayer.push({
                    'ecommerce': {
                        'currencyCode': $data.currency,
                        'remove': {'products': $data.products}
                    }
                });
            } catch (e) {console.log('Yandex e-commerce error: ' + e);}
        }
    });
}(jQuery));
