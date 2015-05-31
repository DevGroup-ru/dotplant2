<?php
/**
 * @var \app\modules\shop\models\OrderTransaction $transaction
 */
    $paymentType = $transaction->paymentType;
?>
<?= $paymentType->getPayment($transaction->order, $transaction)->content(); ?>
