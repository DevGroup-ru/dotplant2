<?php

/** @var $this yii\web\View */
/** @var $cart \app\modules\shop\models\Cart */

use kartik\helpers\Html;

$this->title = Yii::t('app', 'Cart');

?>
<h1><?= Yii::t('app', 'Cart') ?></h1>
<?php if (!is_null($cart) && $cart->items_count > 0): ?>
    <?=
        $this->render(
            '_items',
            [
                'items' => $cart->toOrderItems(),
                'immutable' => false,
                'totalQuantity' => $cart->items_count,
                'totalPrice' => $cart->total_price,
            ]
        );
    ?>
    <?=
        Html::a(
            Yii::t('app', 'Checkout'),
            [
                '/cart/shipping-option',
            ],
            [
                'class' => 'btn btn-primary pull-right',
            ]
        )
    ?>
    <?=
        Html::a(
            Yii::t('app', 'Print'),
            '#',
            [
                'class' => 'btn btn-default',
                'id' => 'print-page',
            ]
        )
    ?>
<?php else: ?>
    <p><?= Yii::t('app', 'Your cart is empty'); ?></p>
<?php endif; ?>
<script>

jQuery('input[data-type=quantity]').blur(function() {
    var $input = jQuery(this);
    var quantity = parseInt($input.val());
    if (isNaN(quantity) || quantity < 1) {
        quantity = 1;
    }
    Shop.changeAmount($input.data('id'), quantity, function(data) {
        if (data.success) {
            jQuery('#cart-table .total-price').text(data.totalPrice);
            jQuery('#cart-table .items-count').text(data.itemsCount);
            $input.parents('tr').eq(0).find('.item-price').text(data.itemPrice);
            $input.val(quantity);
        }
    });
});
jQuery('#cart-table [data-action="change-quantity"]').click(function() {
    var $this = jQuery(this);
    var $input = $this.parents('td').eq(0).find('input[data-type=quantity]');
    var quantity = parseInt($input.val());
    if (isNaN(quantity)) {
        quantity = 1;
    }
    if ($this.hasClass('plus')) {
        quantity++;
    } else {
        if(quantity > 1) {
            quantity--;
        }
    }
    Shop.changeAmount($input.data('id'), quantity, function(data) {
        if (data.success) {
            jQuery('#cart-table .total-price').text(data.totalPrice);
            jQuery('#cart-table .items-count').text(data.itemsCount);
            $input.parents('tr').eq(0).find('.item-price').text(data.itemPrice);
            $input.val(quantity);
        }
    });
    return false;
});
</script>