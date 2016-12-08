/* globals $: false */

"use strict";

var Shop = {
    'addBatchToCart' : function(data, callback) {
        $.ajax({
            'data' : {'products': data},
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) === 'function') {
                    callback(data);
                }
            },
            'type' : 'post',
            'url' : '/shop/cart/add'
        });
    },
    'addToCart' : function(productId, quantity, callback, children, customName) {
        var item = {
            'id' : productId,
            'quantity' : typeof(quantity) !== 'undefined' ? quantity : 1,
            'customName' : typeof(customName) !== 'undefined' ? customName : ''
        };
        if (typeof(children) !== 'undefined') {
            item.children = children;
        }
        this.addBatchToCart([item], callback);
    },
    'changeAmount' : function(orderItemId, quantity, callback) {
        var data = {
            'id' : orderItemId,
            'quantity' : quantity
        };
        $.ajax({
            'data' : data,
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) === 'function') {
                    callback(data);
                }
            },
            'type' : 'post',
            'url' : '/shop/cart/change-quantity'
        });
    },
    'removeFromCart': function(orderItemId, callback) {
        $.ajax({
            'data' : {'id': orderItemId},
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) === 'function') {
                    callback(data);
                }
            },
            'type' : 'get',
            'url' : '/shop/cart/delete?id=' + orderItemId
        });
    },
    'clearCart': function(callback) {
        $.ajax({
            'data' : {},
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) === 'function') {
                    callback(data);
                }
            },
            'type' : 'get',
            'url' : '/shop/cart/clear'
        });
    },
    'addToWishlist': function(productId, wishlistId, title, callback) {
        $.ajax({
            'data' : {'id' : productId, 'wishlistId' : wishlistId, 'title' : title},
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) === 'function') {
                    callback(data);
                }
            },
            'type' : 'post',
            'url' : '/shop/wishlist/add'
        });
    },
    'getPriceWishlist': function(wishlistId, selections, callback) {
        $.ajax({
            'data' : {'wishlistId' : wishlistId, 'selections' : selections},
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) === 'function') {
                    callback(data);
                }
            },
            'type' : 'post',
            'url' : '/shop/wishlist/price'
        });
    },
    'addToCompare': function(productId,callback) {
        $.ajax({
            'data' : {'id': productId},
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) === 'function') {
                    callback(data);
                }
            },
            'type' : 'post',
            'url' : '/shop/product-compare/add'
        });
    }
};

var DotPlant = {
    'setPreference' : function(key, value) {
        $.ajax({
            'data' : {
                'key': key,
                'value': value
            },
            'dataType' : 'json',
            'success' : function(data) {
                location.reload(true);
                return false;
            },
            'url' : '/shop/user-preferences/set'
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


$(function() {
    $('#print-page').click(function() {
        window.print();
        return false;
    });

    $('[data-action=delete]').click(function() {
        //var $link = $(this);
        //$.ajax({
        //    'dataType' : 'json',
        //    'success' : function(data) {
        //        if (data['success']) {
        //            location.reload();
        //        }
        //    },
        //    'url' : $link.data('url')
        //});
        var $this = $(this);
        var $body = $('body');
        $body.trigger({
            'type': 'removeFromCartClicked',
            'orderItemId': $this.data('id'),
            'button': $this
        });
        Shop.removeFromCart($this.data('id'), function(data) {
            $body.trigger({
                'type': 'removeFromCart',
                'orderItemId': $this.data('id'),
                'orderData': data,
                'button': $this
            });
            window.location.reload();
        });

        return false;
    });

    $('[data-action="clear-cart"]').click(function(event) {
        var $this = $(this);
        var $body = $('body');

        Shop.clearCart(function(data) {
            $body.trigger({
                'type': 'clearCart',
                'orderData': data,
                'button': $this
            });
            window.location.reload();
        });
    });

    $('body').on('click', '[data-action="add-to-cart"]', function() {
        var $this = $(this);
        var quantity = typeof($this.data('quantity')) !== 'undefined' ? parseFloat($this.data('quantity')) : 1;
        var customName = typeof($this.data('custom-name')) !== 'undefined' ? $this.data('custom-name') : '';
        if (isNaN(quantity) || quantity < 1) {
            quantity = 1;
        }
        var $body = $('body');
        $body.trigger({
            'type': 'addToCartClicked',
            'productId': $this.data('id'),
            'quantity': quantity,
            'button': $this
        });
        Shop.addToCart($this.data('id'), quantity, function(data) {
            $body.trigger({
                'type': 'addToCart',
                'productId': $this.data('id'),
                'quantity': quantity,
                'orderData': data,
                'button': $this
            });
            var $widget = $('#cart-info-widget');
            if ($widget.length === 0) {
                $widget = $(".btn-show-cart");
            }
            if ($widget.length > 0) {
                $widget.find('.total-price').html(data['totalPrice']);

                $widget.find('.items-count').html(data['itemsCount']);


                var imgtofly = $this.hasClass('fly-out') ? $this : $($this.closest('.fly-out'));
                if (imgtofly.length === 0) {
                    imgtofly = $this;
                }
                if (imgtofly.length) {

                    var imgclone = imgtofly.clone()
                        .offset({top: imgtofly.offset().top, left: imgtofly.offset().left})
                        .css({
                            'opacity': '0.2',
                            'position': 'absolute',
                            'background': 'white',
                            'z-index': '1000',
                            'transform': 'scale(0.5,0.5)'
                        })
                        .appendTo($('body'))
                        .animate({
                            'top': $widget.offset().top + 10,
                            'left': $widget.offset().left + 30
                        }, 550, 'linear');
                    imgclone.animate({'width': 0, 'height': 0}, function () {
                        $(this).detach();
                        $body.trigger({
                            'type': 'productFlown',
                            'productId': $this.data('id'),
                            'quantity': quantity,
                            'data': data,
                            'button': $this
                        });
                    });
                }

            }
            return false;


        }, [], customName);
        return false;
    });

    $('.wishlist-item').on('click', '[data-action="add-batch-to-cart"]', function() {
        var $this = $(this);
        var quantity = typeof($this.data('quantity')) !== 'undefined' ? parseFloat($this.data('quantity')) : 1;
        var customName = typeof($this.data('custom-name')) !== 'undefined' ? $this.data('custom-name') : '';
        if (isNaN(quantity) || quantity < 1) {
            quantity = 1;
        }
        var products = [];
        var selections = $this.parents('.wishlist-item').find('[type=checkbox]:checked');
        if (selections.length !== 0){
            var items = [];
            $.each(selections, function(){
                items.push($(this).val());
            });
            $this.data('id', items);
        }
        $.each($this.data('id'), function(i){
            products[i] = {
                'id' : $this.data('id')[i],
                'quantity' : quantity,
                'customName' : customName,
                'children' : []
            };
        });

        var $body = $('body');
        $body.trigger({
            'type': 'addBatchToCartClicked',
            'productId': $this.data('id'),
            'button': $this
        });
        Shop.addBatchToCart(products, function(data) {
            $body.trigger({
                'type': 'addBatchToCart',
                'productId': $this.data('id'),
                'quantity': quantity,
                'orderData': data,
                'button': $this
            });
            var $widget = $('#cart-info-widget');
            if ($widget.length === 0) {
                $widget = $(".btn-show-cart");
            }
            if ($widget.length > 0) {
                $widget.find('.total-price').html(data['totalPrice']);

                $widget.find('.items-count').html(data['itemsCount']);


                var imgtofly = $this.hasClass('fly-out') ? $this : $($this.closest('.fly-out'));
                if (imgtofly.length === 0) {
                    imgtofly = $this;
                }
                if (imgtofly.length) {

                    var imgclone = imgtofly.clone()
                        .offset({top: imgtofly.offset().top, left: imgtofly.offset().left})
                        .css({
                            'opacity': '0.2',
                            'position': 'absolute',
                            'background': 'white',
                            'z-index': '1000',
                            'transform': 'scale(0.5,0.5)'
                        })
                        .appendTo($body)
                        .animate({
                            'top': $widget.offset().top + 10,
                            'left': $widget.offset().left + 30
                        }, 550, 'linear');
                    imgclone.animate({'width': 0, 'height': 0}, function () {
                        $(this).detach();
                        $body.trigger({
                            'type': 'productFlown',
                            'productId': $this.data('id'),
                            'quantity': quantity,
                            'data': data,
                            'button': $this
                        });
                    });
                }

            }
            return false;


        });
        return false;
    });

    $('.wishlist-item').on('change', '[type=checkbox]', function() {
        var wishlist = $(this).parents('.wishlist-item');
        var selections = wishlist.find('[type=checkbox]:checked');
        var wishlistId = wishlist.data('id');
        var items = [];
        $.each(selections, function(){
            items.push($(this).val());
        });
        Shop.getPriceWishlist(wishlistId, items, function(data){
            wishlist.find('.wishlist-price-content').html(data);
        });
    });

    $('input[data-type=quantity]').blur(function() {
        var $body = $('body');
        var $input = $(this);
        var quantity = parseFloat($input.val());
        var nominal = parseFloat($input.data('nominal'));
        if (isNaN(quantity) || quantity < nominal) {
            quantity = nominal;
        }
        Shop.changeAmount($input.data('id'), quantity, function(data) {
            $body.trigger({
                'type': 'cartChangeQuantity',
                'orderItemId': $input.data('id'),
                'orderData': data,
                'button': $input
            });

            if (data.success) {
                $('#cart-table .total-price, #cart-info-widget .total-price').html(data.totalPrice);
                $('#cart-table .items-count, #cart-info-widget .items-count').html(data.itemsCount);
                $input.parents('tr').eq(0).find('.item-price').html(data.itemPrice);
                $input.val(data.calculatedQuantity);
            }
        });
    });

    $('#cart-table [data-action="change-quantity"]').click(function() {
        var $body = $('body');
        var $this = $(this);
        var $input = $this.parents('td').eq(0).find('input[data-type=quantity]');
        var quantity = parseFloat($input.val());
        var nominal = parseFloat($input.data('nominal'));
        if (isNaN(quantity)) {
            quantity = nominal;
        }
        if ($this.hasClass('plus')) {
            quantity += nominal;
        } else {
            if (quantity > nominal) {
                quantity -= nominal;
            }
        }
        Shop.changeAmount($input.data('id'), quantity, function(data) {
            $body.trigger({
                'type': 'cartChangeQuantity',
                'orderItemId': $this.data('id'),
                'orderData': data,
                'button': $this
            });

            if (data.success) {
                $('#cart-table .total-price, #cart-info-widget .total-price').html(data.totalPrice);
                $('#cart-table .items-count, #cart-info-widget .items-count').html(data.itemsCount);
                $input.parents('tr').eq(0).find('.item-price').html(data.itemPrice);
                $input.val(data.calculatedQuantity);
            }
        });
        return false;
    });
    
    $('[data-dotplant-listViewType]').click(function(){
        var $this = $(this);

        DotPlant.setPreference('listViewType', $this.data('dotplantListviewtype'));
        return false;
    });

    $('body').on('change', 'select[data-userpreference]', function(){
        var $this = $(this);
        DotPlant.setPreference($this.data('userpreference'), $this.val());
        return false;
    });

    $('body').on('click', '[data-action="add-to-compare"]', function() {
        var $this = $(this);
        var id = $this.data('id');

        var $body = $('body');
        $body.trigger({
            'type': 'addToCompareClicked',
            'productId': id,
            'button': $this
        });
        Shop.addToCompare(id, function(data) {
            $body.trigger({
                'type': 'addToCompare',
                'productId': id,
                'button': $this
            });
            var $widget = $('.btn-compare');
            if ($widget.length > 0) {
                $widget.find('.items-count').html(data.items);

                var imgtofly = $this.hasClass('fly-out') ? $this : $($this.closest('.fly-out'));
                if (imgtofly.length === 0) {
                    imgtofly = $this;
                }
                if (imgtofly.length) {

                    var imgclone = imgtofly.clone()
                        .offset({top: imgtofly.offset().top, left: imgtofly.offset().left})
                        .css({
                            'opacity': '0.2',
                            'position': 'absolute',
                            'background': 'white',
                            'z-index': '1000',
                            'transform': 'scale(0.5,0.5)'
                        })
                        .appendTo($('body'))
                        .animate({
                            'top': $widget.offset().top + 10,
                            'left': $widget.offset().left + 30
                        }, 550, 'linear');
                    imgclone.animate({'width': 0, 'height': 0}, function () {
                        $(this).detach();
                        $body.trigger({
                            'type': 'productFlown',
                            'productId': $this.data('id'),
                            'data': data,
                            'button': $this
                        });
                    });
                }
            }
            return false;
        });
        return false;
    });

    $('body').on('click', '[data-action="add-to-wishlist"]', function() {
        var $this = $(this);
        var id = $this.data('id');
        var wishlistId = $this.siblings().find('[type=radio]:checked').val();
        var textInput = $this.siblings().find('[type=text]');
        var title = textInput.val();

        if($.trim(title) == '' && wishlistId == 0){
            return false;
        }
        $('#wishlist.in .close').trigger('click');

        var $body = $('body');
        $body.trigger({
            'type': 'addToWishlistClicked',
            'productId': id,
            'wishlistId': wishlistId,
            'title': title,
            'button': $this
        });
        Shop.addToWishlist(id, wishlistId, title, function(data) {

            if (data.isSuccess === false) {
                $('.content-part').prepend('<div id="w100-warning" class="alert-warning alert fade in"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' + data.errorMessage +'</div>');
                return false;
            }
            $body.trigger({
                'type': 'addToWishlist',
                'productId': id,
                'wishlistId': wishlistId,
                'title': title,
                'button': $this
            });
            var $widget = $('.btn-wishlist');
            if ($widget.length > 0) {
                $widget.find('.items-count').html(data.items);

                var imgtofly = $this.hasClass('fly-out') ? $this : $($this.closest('.fly-out'));
                if (imgtofly.length === 0) {
                    imgtofly = $this;
                }
                if (imgtofly.length) {

                    var imgclone = imgtofly.clone()
                        .offset({top: imgtofly.offset().top, left: imgtofly.offset().left})
                        .css({
                            'opacity': '0.2',
                            'position': 'absolute',
                            'background': 'white',
                            'z-index': '1000',
                            'transform': 'scale(0.5,0.5)'
                        })
                        .appendTo($('body'))
                        .animate({
                            'top': $widget.offset().top + 10,
                            'left': $widget.offset().left + 30
                        }, 550, 'linear');
                    imgclone.animate({'width': 0, 'height': 0}, function () {
                        $(this).detach();
                        $body.trigger({
                            'type': 'productFlown',
                            'productId': $this.data('id'),
                            'data': data,
                            'button': $this
                        });
                    });
                }
            }
            return false;
        });
        return false;
    });
});

(function($){
    "use strict";
    $.fn.dotPlantSmartFilters = function() {
        var thatFilters = $(this),
            datId = '#'+thatFilters.attr('id'),
            catchTimeout = false,
            overlay = thatFilters.find('.overlay');
        var doFiltration = function() {
            if (catchTimeout !== false) {
                clearTimeout(catchTimeout);
            }
            var block = $("#product-list-block"),
                form = thatFilters.find('form');
            block.css('height', block.height()+'px');
            block.empty().html('Loading...');
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(data) {
                    form.html($(data.filters).find('form').html());

                    $('.filter-property div[id ^= range-widget-]').each(function () {
                        var slider = $(this).find('div[id ^= slider-id-]');
                        var minInput = $(this).find('input[id $= _minAttribute]');
                        var maxInput = $(this).find('input[id $= _maxAttribute]');

                        slider.slider({
                            min: $(this).data('min'),
                            max: $(this).data('max'),
                            values: [minInput.val(), maxInput.val()],
                            range: true,
                            step : $(this).data('step'),
                            stop: function(event, ui) {
                                minInput.val(slider.slider("values",0));
                                maxInput.val(slider.slider("values",1));
                            },
                            slide: function(event, ui){
                                minInput.val(slider.slider("values",0));
                                maxInput.val(slider.slider("values",1));
                            }
                        });
                    });

                    var elem = $(data.content);
                    block.empty().css('height', 'auto').append(elem);
                    document.title = data.title;
                    if (history) {
                        if (history.pushState) {
                            history.pushState(
                                null,
                                data.title,
                                data.url
                            );
                        }
                    }
                    overlay.hide();
                    thatFilters.trigger('filtration', {'data':data});
                }
            })
        };
        $('.filter-sets-widget').on('click', datId + ' .filter-link', function() {
            overlay.show();
            if (catchTimeout !== false) {
                clearTimeout(catchTimeout);
            }
            var that = $(this),
                selectionId = that.data('selectionId'),
                checkbox = $('#filter-check-'+selectionId),
                propertyId = checkbox.data('propertyId');
            checkbox.prop('checked', !checkbox.prop('checked'));
            catchTimeout = setTimeout(doFiltration, 1000);
            return false;
        });
        thatFilters.on('click', '.filter-check', function() {
            overlay.show();
            if (catchTimeout !== false) {
                clearTimeout(catchTimeout);
            }
            catchTimeout = setTimeout(doFiltration, 1000);
            return true;
        });
        thatFilters.find('form').submit(function() {
            overlay.show();
            doFiltration();
            return false;
        });
    }
}(jQuery));
