<?php
/**
 * @var string[] $formData
 * @var \app\modules\shop\models\Order $order
 * @var \app\modules\shop\models\OrderTransaction $transaction
 */
?>
<form action="https://secure.futubank.com/pay" method="post" xmlns="http://www.w3.org/1999/html">
    <?php foreach ($formData as $key => $value): ?>
        <?= \kartik\helpers\Html::hiddenInput($key, $value) ?>
    <?php endforeach; ?>
    <input type="submit" value="<?= Yii::t('app', 'Pay') ?>" class="btn btn-primary" />
</form>