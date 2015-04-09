<?php
/**
 * @var string $currency
 * @var integer $eshopId
 * @var string $language
 * @var \yii\web\View $this
 * @var \app\models\Order $order
 * @var string $serviceName
 * @var \app\models\OrderTransaction $transaction
 */
?>
<form action="https://rbkmoney.ru/acceptpurchase.aspx" name="pay" method="POST" id="rbk-money-form">
    <input type="hidden" name="eshopId" value="<?= $eshopId ?>" />
    <input type="hidden" name="orderId" value="<?= $order->id ?>" />
    <input type="hidden" name="serviceName" value="<?= $serviceName ?>" />
    <input type="hidden" name="recipientAmount" value="<?= $transaction->total_sum ?>" />
    <input type="hidden" name="recipientCurrency" value="<?= $currency ?>" />
    <input type="hidden" name="language" value="<?= $language ?>" />
    <input type="hidden" name="successUrl" value="<?=
        \yii\helpers\Url::toRoute(['/cart/payment-success', 'id' => $order->id], true)
    ?>" />
    <input type="hidden" name="failUrl" value="<?=
        \yii\helpers\Url::toRoute(['/cart/payment-error', 'id' => $order->id], true)
    ?>" />
    <input type="submit" name="button" value="<?= Yii::t('app', 'Pay') ?>" />
</form>
<script>
jQuery('#rbk-money-form').submit();
</script>