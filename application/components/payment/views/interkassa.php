<?php
/**
 * @var string $checkoutId
 * @var string $currency
 * @var string $locale
 * @var \app\modules\shop\models\Order $order
 * @var \app\modules\shop\models\OrderTransaction $transaction
 */
?>
<form method="post" action="https://sci.interkassa.com/" accept-charset="UTF-8" id="interkassa-form">
    <input type="hidden" name="ik_co_id" value="<?= $checkoutId ?>" />
    <input type="hidden" name="ik_pm_no" value="<?= $transaction->id ?>" />
    <input type="hidden" name="ik_desc" value="Order payment #<?= $order->id ?>" />
    <input type="hidden" name="ik_am" value="<?= $transaction->total_sum ?>" />
    <input type="hidden" name="ik_cur" value="<?= $currency ?>" />
    <input type="hidden" name="ik_am_t" value="payway" />
    <input type="hidden" name="ik_loc" value="<?= $locale ?>" />
    <input type="hidden" name="ik_enc" value="UTF-8" />
    <input type="hidden" name="ik_suc_u" value="<?= $ik_suc_u; ?>" />
    <input type="hidden" name="ik_suc_m" value="get" />
    <input type="hidden" name="ik_fal_u" value="<?= $ik_fal_u; ?>" />
    <input type="hidden" name="ik_fal_m" value="get" />
    <input type="submit" value="<?= Yii::t('app', 'Pay') ?>" class="btn btn-primary" />
</form>
