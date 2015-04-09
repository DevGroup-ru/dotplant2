<?php
/**
 * @var string[] $formData
 * @var \app\models\Order $order
 * @var \app\models\OrderTransaction $transaction
 */
?>
<form method="post" action="https://www.walletone.com/checkout/default.aspx" accept-charset="UTF-8" id="wallet-one-form">
    <?php foreach ($formData as $key => $value): ?>
        <?= \yii\helpers\Html::hiddenInput($key, $value) ?>
    <?php endforeach; ?>
    <input type="submit" value="<?= Yii::t('app', 'Pay') ?>" class="btn btn-primary" />
</form>
<script>
jQuery('#wallet-one-form').submit();
</script>