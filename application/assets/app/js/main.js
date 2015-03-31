/* globals jQuery: false */

"use strict";

var Shop = {
    'addToCart' : function(productId, quantity, callback, additionalParams) {
        var data = {
            'id' : productId,
            'quantity' : typeof(quantity) !== 'undefined' ? quantity : 1,
            'additionalParams' : typeof(additionalParams) !== 'undefined' ? additionalParams : '{"additionalPrice":0}'
        };
        jQuery.ajax({
            'data' : data,
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) === 'function') {
                    callback(data);
                }
            },
            'type' : 'post',
            'url' : '/cart/add'
        });
    },
    'changeAmount' : function(orderItemId, quantity, callback) {
        var data = {
            'id' : orderItemId,
            'quantity' : quantity
        };
        jQuery.ajax({
            'data' : data,
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) === 'function') {
                    callback(data);
                }
            },
            'type' : 'post',
            'url' : '/cart/change-quantity'
        });
    },
    'changeAdditionalParams' : function(orderItemId, params, callback) {
        var data = {
            'id' : orderItemId,
            'additionalParams' : params
        };
        jQuery.ajax({
            'data' : data,
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) === 'function') {
                    callback(data);
                }
            },
            'type' : 'post',
            'url' : '/cart/change-additional-params'
        });
    }
};

var DotPlant = {
    'setPreference' : function(key, value) {
        jQuery.ajax({
            'data' : {
                'key': key,
                'value': value
            },
            'dataType' : 'json',
            'success' : function(data) {
                location.reload(true);
                return false;
            },
            'url' : '/user-preferences/set'
        });
        return false;
    },
    'setCookie' : function setcookie(name, value, expires, path, domain, secure) {
        expires instanceof Date ? expires = expires.toGMTString() : typeof(expires) == 'number' && (expires = (new Date(+(new Date) + expires * 1e3)).toGMTString());
        var r = [name + "=" + escape(value)], s, i;
        for(i in s = {expires: expires, path: path, domain: domain}){
            s[i] && r.push(i + "=" + s[i]);
        }
        return secure && r.push("secure"), document.cookie = r.join(";"), true;
    },
    'getCookie' : function getCookie(name) {
        var matches = document.cookie.match(new RegExp("(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }
};

var Order = {

    getDeliveryPrice:  function( shipping_option_id) {

        var self = this;

        $.ajax({
            'data' : { 'shipping_option_id':shipping_option_id },
            'dataType' : 'json',
            'success' : function(data){
                self.setContentDeliveryPrice(data);
            } ,
            'type' : 'post',
            'url' : '/cart/get-delivery-price'
        });

    },

    setContentDeliveryPrice: function(data) {

        if (data.success === true) {

            $('.shipping-data .name').html(data.name);
            $('.shipping-data .price').html(data.shipping_price + ' ' + data.currency);
            $('.total-price').html(data.full_price);

            $('.shipping-data').show();
        }

    }

};


jQuery(function() {
    jQuery('#print-page').click(function() {
        window.print();
        return false;
    });
    jQuery('[data-action=delete]').click(function() {
        var $link = jQuery(this);
        jQuery.ajax({
            'dataType' : 'json',
            'success' : function(data) {
                if (data['success']) {
                    location.reload();
                }
            },
            'url' : $link.data('url')
        });
        return false;
    });
    jQuery('[data-action="add-to-cart"]').click(function() {
        var $this = jQuery(this);
        var quantity = typeof($this.attr('data-quantity')) !== 'undefined' ? parseFloat($this.attr('data-quantity')) : 1;
        if (isNaN(quantity) || quantity < 1) {
            quantity = 1;
        }

        Shop.addToCart($this.attr('data-id'), quantity, function(data) {
            var $widget = jQuery('#cart-info-widget');
            $widget.find('.total-price').text(data['totalPrice']);
            $widget.find('.items-count').text(data['itemsCount']);


            var imgtofly = jQuery($this.closest('div[itemtype="http://schema.org/Product"]'));
            if (imgtofly.length) {

                var imgclone = imgtofly.clone()
                    .offset({ top:imgtofly.offset().top, left:imgtofly.offset().left })
                    .css({'opacity':'0.2', 'position':'absolute','background':'white','z-index':'1000','transform': 'scale(0.5,0.5)'})
                    .appendTo($('body'))
                    .animate({
                        'top':$widget.offset().top + 10,
                        'left':$widget.offset().left + 30,
                        'width':50,
                        'height':50
                    }, 1000, 'linear');
                imgclone.animate({'width':0, 'height':0}, function(){ jQuery(this).detach(); });
            }
            return false;


        });
        return false;
    });

    jQuery('[data-dotplant-listViewType]').click(function(){
        var $this = jQuery(this);

        DotPlant.setPreference('listViewType', $this.data('dotplantListviewtype'));
        return false;
    });

    jQuery('select[data-userpreference]').change(function(){
        var $this = jQuery(this);
        DotPlant.setPreference($this.data('userpreference'), $this.val());
        return false;
    });
});
