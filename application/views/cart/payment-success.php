<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Order $order
 */
$this->title = Yii::t('shop', 'Your order has been formed');
?>
    <p><?= Yii::t('shop', 'Your order has been formed') ?>.</p>
<?php if (is_null($order)): ?>
    <p><?= Yii::t('shop', 'You can see your orders') ?> <?= \yii\helpers\Html::a(Yii::t('shop', 'here'), ['/cabinet/orders'], ['class' => 'btn btn-info']) ?></p>
<?php else: ?>
    <p><?= Yii::t('shop', 'You can see your order status') ?> <?= \yii\helpers\Html::a(Yii::t('shop', 'here'), ['/cabinet/order', 'id' => $order->hash]) ?>.</p>
<?php endif; ?>
