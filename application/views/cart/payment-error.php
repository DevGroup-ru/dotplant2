<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Order $order
 */
$this->title = Yii::t('shop', 'Payment error');
?>
    <p><?= Yii::t('shop', 'Payment error') ?></p>
<?php if (is_null($order)): ?>
    <p><?= Yii::t('shop', 'Something went wrong') ?>.</p>
<?php else: ?>
    <p><?= Yii::t('shop', 'You can try to') ?> <?= \yii\helpers\Html::a(Yii::t('shop', 'retry payment'), ['/cart/payment', 'id' => $order->id]) ?> <?= Yii::t('shop', 'or') ?> <?= \yii\helpers\Html::a(Yii::t('shop', 'change payment type'), ['/cart/payment-type', 'id' => $order->id]) ?>.</p>
<?php endif; ?>