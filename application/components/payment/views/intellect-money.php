<?php
/**
 * @var string $currency
 * @var integer $eshopId
 * @var string $language
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Order $order
 * @var string $serviceName
 * @var \app\modules\shop\models\OrderTransaction $transaction
 */
?>
<form action="https://merchant.intellectmoney.ru/ru/" method="POST" id="intellect-money-form">
    <input type="hidden" name="eshopId" value="<?= $eshopId ?>" />
    <input type="hidden" name="orderId" value="<?= $order->id ?>" />
    <input type="hidden" name="serviceName" value="<?= $serviceName ?>" />
    <input type="hidden" name="recipientAmount" value="<?= $transaction->total_sum ?>" />
    <input type="hidden" name="recipientCurrency" value="<?= $currency ?>" />
    <input type="hidden" name="language" value="<?= $language ?>" />
    <input type="hidden" name="successUrl" value="<?= $successUrl; ?>" />
    <input type="hidden" name="failUrl" value="<?= $failUrl; ?>" />
    <input type="submit" name="button" value="<?= Yii::t('app', 'Pay') ?>" />
</form>