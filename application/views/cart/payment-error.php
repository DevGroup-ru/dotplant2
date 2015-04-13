<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Order $order
 */
$this->title = Yii::t('app', 'Payment error');
?>
    <p><?= Yii::t('app', 'Payment error') ?></p>
<?php if (is_null($order)): ?>
    <p><?= Yii::t('app', 'Something went wrong') ?>.</p>
<?php else: ?>
    <p><?= Yii::t('app', 'You can try to') ?> <?= \yii\helpers\Html::a(Yii::t('app', 'retry payment'), ['/cart/payment', 'id' => $order->id]) ?> <?= Yii::t('app', 'or') ?> <?= \yii\helpers\Html::a(Yii::t('app', 'change payment type'), ['/cart/payment-type', 'id' => $order->id]) ?>.</p>
<?php endif; ?>