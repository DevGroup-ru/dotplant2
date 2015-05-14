<?php
/** @var \app\models\Order $order */
use yii\helpers\Html;
$mainCurrency = \app\modules\shop\models\Currency::getMainCurrency();
?>
<h1><?= Yii::t('app', 'Order #{orderId}', ['orderId' => $order->id]) ?></h1>
<h2><?= Yii::t('app', 'Order information') ?></h2>
<table style="width: 800px;" border="1" bordercolor="#ddd" cellspacing="0">
    <tr>
        <th style="text-align: left;"><?= $order->getAttributeLabel('start_date') ?></th>
        <td><?= $order->start_date ?></td>
    </tr>
    <tr style="background: #f5f5f5;">
        <th style="text-align: left;"><?= $order->getAttributeLabel('order_status_id') ?></th>
        <td>
            <?=
            isset($order->status) ? $order->status->short_title : Html::tag('em', Yii::t('yii', '(not set)'))
            ?>
        </td>
    </tr>
    <tr>
        <th style="text-align: left;"><?= $order->getAttributeLabel('shipping_option_id') ?></th>
        <td>
            <?=
            isset($order->shippingOption)
                ? $order->shippingOption->name
                : Html::tag('em', Yii::t('yii', '(not set)'))
            ?>
        </td>
    </tr>
    <?php $i = 0; ?>
    <?php foreach($order->abstractModel->attributes as $attribute => $value): ?>
        <tr style="background: <?= $i % 2 == 0 ? '#f5f5f5' : '#fff' ?>;">
            <th style="text-align: left;"><?= $order->abstractModel->getAttributeLabel($attribute) ?></th>
            <td>
                <?=
                !empty($value)
                    ? Html::encode($value)
                    : Html::tag('em', Yii::t('yii', '(not set)'))
                ?>
            </td>
        </tr>
        <?php $i++; ?>
    <?php endforeach; ?>
</table>
<h2><?= Yii::t('app', 'Order items') ?></h2>
<table style="width: 800px;" border="1" bordercolor="#ddd" cellspacing="0">
    <thead>
        <tr>
            <th><?= Yii::t('app', 'Name') ?></th>
            <th><?= Yii::t('app', 'Price') ?></th>
            <th><?= Yii::t('app', 'Quantity') ?></th>
            <th><?= Yii::t('app', 'Price sum') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($order->items as $i => $item): ?>
            <tr style="background: <?= $i % 2 == 0 ? '#f5f5f5' : '#fff' ?>;">
                <td><?= $item->product->name ?></td>
                <td><?= $item->product->formattedPrice(null, false, false) ?></td>
                <td><?= $item->quantity ?></td>
                <td>
                    <?=
                    $mainCurrency->format(
                        $item->product->convertedPrice() * $item->quantity
                    );
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (isset($order->shippingOption)): ?>
            <tr style="background: <?= count($order->items) % 2 == 0 ? '#f5f5f5' : '#fff' ?>;">
                <td colspan="3"><?= Html::encode($order->shippingOption->name) ?></td>
                <td><?= $mainCurrency->format($order->shippingOption->cost) ?></td>
            </tr>
        <?php endif; ?>
        <tr style="background: #f0f0f0;">
            <th colspan="2">&nbsp;</th>
            <th><?= $order->items_count ?></th>
            <th><?= $mainCurrency->format($order->total_price) ?></th>
        </tr>
    </tbody>
</table>

<p>
    <?=
    \Yii::t(
        'app',
        'See your order status <a href="{url}">here</a>.',
        [
            'url' => \yii\helpers\Url::toRoute(['/cabinet/order', 'id' => $order->hash], true),
        ]
    );
    ?>
</p>