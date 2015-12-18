<?php
/**
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Order $order
 * @var \app\modules\shop\models\OrderTransaction $transaction
 * @var string $approvalLink
 */
?>
<?php if(null !== $approvalLink): ?>
    <div>
        <a href="<?= $approvalLink; ?>">Перейти к оплате</a>
    </div>
<?php else: ?>
    <div>Произошла ошибка при формировании счета. Пожалуйста, повторите попытку.</div>
<?php endif; ?>
