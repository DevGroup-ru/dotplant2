<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Order $order
 */
$this->title = Yii::t('shop', 'Your order has been formed');
?>
    <p><?= Yii::t('shop', 'Your order has been formed') ?></p>
<?php if (is_null($order)): ?>
    <p>You can see your orders <?= \yii\helpers\Html::a('here', ['/cabinet/orders']) ?>.</p>
<?php else: ?>
    <p>You can see your order status <?= \yii\helpers\Html::a('here', ['/cabinet/order', 'id' => $order->hash]) ?>.</p>
<?php endif; ?>