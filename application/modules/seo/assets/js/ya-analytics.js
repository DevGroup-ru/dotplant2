'use strict';
jQuery(function($){
    window.dataLayer = window.dataLayer || [];
    window.DotPlantParams = window.DotPlantParams || {};

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

    $body.on('cartChangeQuantity', function(data) {
        var $data = data.orderData.additional;
        if ($data.hasOwnProperty('ecYandex')) {
            try {
                $data = $data.ecYandex;
                $($data.products).each(function(k, v) {
                    if (v.quantity > 0) {
                        window.dataLayer.push({
                            'ecommerce': {
                                'currencyCode': $data.currency,
                                'add': {'products': [v]}
                            }
                        });
                    } else {
                        v.quantity = -1 * v.quantity;
                        window.dataLayer.push({
                            'ecommerce': {
                                'currencyCode': $data.currency,
                                'remove': {'products': [v]}
                            }
                        });
                    }
                });
            } catch (e) {console.log('Yandex e-commerce error: ' + e);}
        }
    });

    $body.on('clearCart', function(data) {
        var $data = data.orderData.additional;
        if ($data.hasOwnProperty('ecYandex')) {
            try {
                $data = $data.ecYandex;
                $($data.products).each(function(k, v) {
                    window.dataLayer.push({
                        'ecommerce': {
                            'currencyCode': $data.currency,
                            'remove': {'products': [v]}
                        }
                    });
                });
            } catch (e) {console.log('Yandex e-commerce error: ' + e);}
        }
    });

    if (window.DotPlantParams.hasOwnProperty('ecYandex')) {
        try {
            var $data = window.DotPlantParams.ecYandex;

            if ('detail' === $data.action) {
                window.dataLayer.push({
                    'ecommerce': {
                        'currencyCode': $data.currency,
                        'detail': {'products': $data.products}
                    }
                });
            } else if ('purchase' === $data.action) {
                window.dataLayer.push({
                    'ecommerce': {
                        'currencyCode': $data.currency,
                        'purchase': {
                            'actionField': {'id': $data.orderId},
                            'products': $data.products
                        }
                    }
                });
            }
        } catch (e) {console.log('Yandex e-commerce error: ' + e);}
    }
}(jQuery));
