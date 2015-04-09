<?php
/**
 * @var string $url
 * @var \app\models\Order $order
 * @var \app\models\OrderTransaction $transaction
 */
?>
<?= \yii\helpers\Html::a(Yii::t('app', 'Pay'), $url, ['class' => 'btn btn-primary']) ?>
<meta http-equiv='refresh'  content="0; URL=<?= $url ?>" />