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

        if (data.success  == true) {

            $('.shipping-data .name').html(data.name);
            $('.shipping-data .price').html(data.shipping_price + ' ' + data.currency);
            $('.total-price').html(data.full_price);

            $('.shipping-data').show();
        }

    }

};
