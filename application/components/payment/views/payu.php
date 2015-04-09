<?php
/**
 * @var string $url
 * @var \app\models\Order $order
 * @var \app\models\OrderTransaction $transaction
 * @var array $data
 */
?>
<form action="<?= $url ?>" method="post">
    <?php foreach ($data as $key => $value): ?>
        <?php if (is_array($value)): ?>
            <?php foreach ($value as $productKey => $productValue): ?>
                <input type="hidden" name="<?= $productKey; ?>" value="<?= $productValue; ?>" />
            <?php endforeach; ?>
        <?php else: ?>
            <input type="hidden" name="<?= $key; ?>" value="<?= $value; ?>" />
        <?php endif; ?>
    <?php endforeach; ?>
    <input type="submit" class="btn btn-primary" value="<?= Yii::t('app', 'Pay') ?>">
</form>