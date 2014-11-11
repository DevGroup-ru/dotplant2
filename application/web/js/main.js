Shop = {
    'addToCart' : function(productId, quantity, callback) {
        var data = {
            'id' : productId,
            'quantity' : typeof(quantity) != 'undefined' ? quantity : 1
        };
        jQuery.ajax({
            'data' : data,
            'dataType' : 'json',
            'success' : function(data) {
                if (typeof(callback) == 'function') {
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
                if (typeof(callback) == 'function') {
                    callback(data);
                }
            },
            'type' : 'post',
            'url' : '/cart/change-quantity'
        });
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
        Shop.addToCart($this.attr('data-id'), 1, function(data) {
            var $widget = jQuery('#cart-info-widget');
            $widget.find('.total-price').text(data['totalPrice']);
            $widget.find('.items-count').text(data['itemsCount']);
        });
        return false;
    });
});