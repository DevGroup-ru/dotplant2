<?php
/**
 * @var string $xmlEncode
 * @var \app\models\Order $order
 * @var string $signEncode
 * @var \app\models\OrderTransaction $transaction
 */
?>
<form action="https://merchant.pay2pay.com/?page=init" method="post" id="pay2pay-form">
    <input type="hidden" name="xml" value="<?= $xmlEncode ?>" />
    <input type="hidden" name="sign" value="<?= $signEncode ?>" />
    <input type="submit" class="btn btn-primary" value="<?= Yii::t('app', 'Pay') ?>" />
 </form>
<script>
    jQuery('#pay2pay-form').submit();
</script>