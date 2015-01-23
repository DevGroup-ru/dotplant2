<?php

/**
 * @var \app\models\Cart $order
 */

if ($order === null) {
    $itemsCount = '0';
    $totalPrice = Yii::$app->formatter->asDecimal(0, 2);
} else {
    $itemsCount = $order->items_count;
    $totalPrice = Yii::$app->formatter->asDecimal($order->total_price, 2);
}

?>
<div class="span3" id="cart-info-widget">
    <div class="pull-right">
        <span class="btn btn-mini"><i class="fa fa-rub"></i><span class="total-price"><?= $totalPrice ?></span> <?= Yii::$app->params['currency'] ?></span>
        <a href="<?= Yii::$app->request->baseUrl ?>/cart"><span class="btn btn-mini btn-primary"><i class="icon-shopping-cart icon-white"></i> <?= Yii::t('shop', '[ {count} ] Itemes in your cart', ['count' => \kartik\helpers\Html::tag('span', $itemsCount, ['class' => 'items-count'])]) ?> </span> </a>
    </div>
</div>
