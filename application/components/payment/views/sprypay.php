<?php
/**
 * @var string $currency
 * @var string $language
 * @var \app\models\Order $order
 * @var string $shopId
 * @var \app\models\OrderTransaction $transaction
 */
?>
<form action="http://sprypay.ru/sppi/" method="POST" accept-charset="utf-8" target="_blank" id="spry-pay-payment">
    <input type="hidden" name="spShopId" value="<?= $shopId ?>" />
    <input type="hidden" name="spShopPaymentId" value="<?= $transaction->id ?>" />
    <input type="hidden" name="spCurrency" value="<?= $currency ?>" />
    <input type="hidden" name="spPurpose" value="Order #<?= $order->id ?>" />
    <input type="hidden" name="spAmount" value="<?= $transaction->total_sum ?>" />
    <input type="hidden" name="spIpnUrl" value="<?=
        \yii\helpers\Url::toRoute(['/cart/payment-result', 'id' => $transaction->payment_type_id], true)
    ?>" />
    <input type="hidden" name="spIpnMethod" value="1" />
    <input type="hidden" name="spSuccessUrl" value="<?=
        \yii\helpers\Url::toRoute(['/cart/payment-success', 'id' => $order->id], true)
    ?>" />
    <input type="hidden" name="spSuccessMethod" value="1" />
    <input type="hidden" name="spFailUrl" value="<?=
        \yii\helpers\Url::toRoute(['/cart/payment-error', 'id' => $order->id], true)
    ?>" />
    <input type="hidden" name="spFailMethod" value="1" />
    <input type="hidden" name="lang" value="<?= $language ?>" />
    <input type="submit" value="<?= Yii::t('app', 'Pay') ?>" class="btn btn-primary" />
</form>
<script>
jQuery('#spry-pay-payment').submit();
</script>