<?php
/**
 * @var \yii\web\View $this
 * @var \app\models\Order $order
 * @var \app\components\payment\AbstractPayment $payment
 * @var \app\models\OrderTransaction $transaction
 */
$this->title = Yii::t('app', 'Payment');
?>
<h1><?= $this->title ?></h1>
<?= $payment->content($order, $transaction) ?>
