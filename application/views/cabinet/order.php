<?php

/**
 * @var \app\models\Order $order
 * @var \yii\web\View $this
 */

use kartik\helpers\Html;

$this->title = Yii::t('shop', 'Order #{order}', ['order' => $order->id]);
$this->params['breadcrumbs'] = [
    [
        'label' => Yii::t('app', 'Personal cabinet'),
        'url' =>  Yii::$app->request->baseUrl.'/cabinet'
    ],
    $this->title,
];

?>
<h1><?= $this->title ?></h1>
<h2><?= Yii::t('shop', 'Order information') ?></h2>
<table class="table table-bordered table-striped">
    <tbody>
        <tr>
            <th><?= $order->getAttributeLabel('start_date') ?></th>
            <td><?= Html::encode($order->start_date) ?></td>
        </tr>
        <tr>
            <th><?= $order->getAttributeLabel('order_status_id') ?></th>
            <td>
                <?=
                isset($order->status)
                    ? Html::tag('span', Html::encode($order->status->short_title), ['class' => $order->status->label])
                    : Yii::t('yii', '(not set)')
                ?>
            </td>
        </tr>
        <tr>
            <th><?= $order->getAttributeLabel('shipping_option_id') ?></th>
            <td>
                <?=
                Html::encode(isset($order->shippingOption) ? $order->shippingOption->name : Yii::t('yii', '(not set)'))
                ?>
            </td>
        </tr>
        <tr>
            <th><?= $order->getAttributeLabel('payment_type_id') ?></th>
            <td>
                <?=
                Html::encode(isset($order->paymentType) ? $order->paymentType->name : Yii::t('yii', '(not set)'))
                ?>
            </td>
        </tr>
        <?php foreach($order->abstractModel->attributes as $attribute => $value): ?>
            <tr>
                <th><?= $order->abstractModel->getAttributeLabel($attribute) ?></th>
                <td><?= empty($value) ? Yii::t('yii', '(not set)') : Html::encode($value) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<h2><?= Yii::t('shop', 'Order items') ?></h2>
<?= $this->render('@app/views/cart/order-items', ['order' => $order]) ?>