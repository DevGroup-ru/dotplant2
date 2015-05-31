<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\OrderTransaction $transaction
 */

    $this->title = Yii::t('app', 'Your order has been formed');
?>
    <p><?= Yii::t('app', 'Your order has been formed') ?>.</p>
    <p>
        <?= Yii::t('app', 'You can see your order status') ?>
        <?= \yii\helpers\Html::a(
            Yii::t('app', 'here'),
            [
                '/shop/orders/show',
                'hash' => $transaction->order->hash
            ],
            ['class' => 'btn btn-info'])
        ?>.
    </p>
