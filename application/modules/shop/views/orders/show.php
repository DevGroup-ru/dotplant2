<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Order $order
 */
?>
<?= \yii\helpers\Html::tag('p', 'Order: '.$order->id); ?>
<?= \yii\helpers\Html::tag('p', 'User id: '.$order->user_id); ?>
