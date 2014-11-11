<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Order $order
 */
$this->title = Yii::t('shop', 'Payment error');
?>
    <p><?= Yii::t('shop', 'Payment error') ?></p>
<?php if (is_null($order)): ?>
    <p>Something went wrong.</p>
<?php else: ?>
    <p>You can try to <?= \yii\helpers\Html::a('retry payment', ['/cart/payment', 'id' => $order->id]) ?> or <?= \yii\helpers\Html::a('change payment type', ['/cart/payment-type', 'id' => $order->id]) ?>.</p>
<?php endif; ?>