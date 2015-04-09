<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Order $order
 */

$this->title = Yii::t('app', 'Your order has been formed');
?>

    <p><?= Yii::t('app', 'Your order has been formed') ?>.</p>
<?php if (is_null($order)): ?>
    <p>
        <?= Yii::t('app', 'You can see your orders') ?>
        <?= \yii\helpers\Html::a(
            Yii::t('app', 'here'),
            ['/cabinet/orders'],
            ['class' => 'btn btn-info'])
        ?>
    </p>
<?php else: ?>
    <p>
        <?= Yii::t('app', 'You can see your order status') ?>
        <?= \yii\helpers\Html::a(
            Yii::t('app', 'here'),
            [
                '/cabinet/order',
                'id' => $order->hash
            ],
            ['class' => 'btn btn-info'])
        ?>.
    </p>
<?php endif; ?>
