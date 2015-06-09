<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\OrderTransaction $transaction
 */
    $this->title = Yii::t('app', 'Payment error');
?>
    <p><?= Yii::t('app', 'Payment error') ?></p>
    <p><?= Yii::t('app', 'You can try to') ?>
        <?= \yii\helpers\Html::a(
            Yii::t('app', 'retry payment'),
            ['/cart/payment', 'id' => $transaction->order->id]
        ); ?>
        <?= Yii::t('app', 'or') ?>
        <?= \yii\helpers\Html::a(
            Yii::t('app', 'change payment type'),
            ['/cart/payment-type', 'id' => $transaction->order->id]
        ); ?>.
    </p>