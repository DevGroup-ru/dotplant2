<?php

/**
 * @var \app\models\Cart $order
 */

$mainCurrency = \app\models\Currency::getMainCurrency();

if ($order === null) {
    $itemsCount = '0';
    $totalPrice = $mainCurrency->format(0);
} else {
    $itemsCount = $order->items_count;
    $totalPrice = $mainCurrency->format($order->total_price);
}



?>
<div class="span3" id="cart-info-widget">
    <div class="pull-right">
        <span class="btn btn-mini">
            <span class="total-price"><?= $totalPrice ?></span>
        </span>
        <a href="/cart">
            <span class="btn btn-mini btn-primary">
                <i class="fa fa-shopping-cart"></i>
                <?= Yii::t('shop', '[ {count} ] Itemes in your cart', ['count' => \kartik\helpers\Html::tag('span', $itemsCount, ['class' => 'items-count'])]) ?>
            </span>
        </a>
    </div>
</div>
