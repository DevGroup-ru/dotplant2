<?php

/**
 * @var \app\modules\shop\models\Order $order
 */

$mainCurrency = \app\modules\shop\models\Currency::getMainCurrency();

if (is_null($order)) {
    $itemsCount = 0;
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
        <a href="<?= \yii\helpers\Url::toRoute(['/shop/cart']) ?>">
            <span class="btn btn-mini btn-primary">
                <i class="fa fa-shopping-cart"></i>
                <?=
                Yii::t(
                    'app',
                    '[ {count} ] Itemes in your cart',
                    ['count' => \kartik\helpers\Html::tag('span', $itemsCount, ['class' => 'items-count'])]
                )
                ?>
            </span>
        </a>
    </div>
</div>
