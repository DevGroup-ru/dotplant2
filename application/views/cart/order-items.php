<?php

/** @var \app\models\Order $order */

use kartik\helpers\Html;

?>
<table class="table table-bordered" id="cart-table">
    <thead>
    <tr>
        <th></th>
        <th><?= Yii::t('shop', 'Name') ?></th>
        <th><?= Yii::t('shop', 'Price') ?></th>
        <th><?= Yii::t('shop', 'Quantity') ?></th>
        <th><?= Yii::t('shop', 'Sum') ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($order->items as $item): ?>
        <tr>
            <td>
                <?=
                \app\widgets\ImgSearch::widget(
                    [
                        'limit' => 1,
                        'objectId' => \app\models\Object::getForClass(\app\models\Product::className())->id,
                        'objectModelId' => $item->product_id,
                        'viewFile' => 'img-thumbnail',
                    ]
                )
                ?>
            </td>
            <td><?= $item->product->name ?></td>
            <td><?= Yii::$app->formatter->asDecimal($item->product->price, 2) ?> <?= Yii::$app->params['currency'] ?></td>
            <td><?= $item->quantity ?></td>
            <td><span class="item-price"><?= Yii::$app->formatter->asDecimal($item->product->price * $item->quantity, 2) ?></span> <?= Yii::$app->params['currency'] ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (isset($order->shippingOption)): ?>
        <tr>
            <td colspan="4"><?= Html::encode($order->shippingOption->name) ?></td>
            <td><?= Yii::$app->formatter->asDecimal($order->shippingOption->cost, 2) ?> <?= Yii::$app->params['currency'] ?></td>
        </tr>
    <?php endif; ?>
    <tr>
        <td colspan="3"></td>
        <td><strong><span class="items-count"><?= $order->items_count ?></span></strong></td>
        <td class="label label-important" style="display:block"> <strong><span class="total-price"><?= Yii::$app->formatter->asDecimal($order->fullPrice, 2) ?></span> <?= Yii::$app->params['currency'] ?></strong></td>
    </tr>
    </tbody>
</table>
<style>
@media print {
    header, .header, footer, .footer, .quantity {
        display: none;
    }
    input[data-type=quantity] {
        border: none;
        width: 100px;
    }
}
</style>