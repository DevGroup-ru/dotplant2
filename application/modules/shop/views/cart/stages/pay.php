<?php
/**
 * @var \yii\web\View $this
 * @var \yii\bootstrap\ActiveForm $form
 * @var \app\modules\shop\models\PaymentType $paymentType
 * @var \app\modules\shop\models\Order $order
 * @var \app\modules\shop\models\OrderTransaction $orderTransaction
 */

?>
<div class="col-md-6 col-md-offset-3">
    <div class="row">
        <?php
            if (!empty($paymentType)) {
                echo $paymentType->getPayment($order, $orderTransaction)->content();
            }
        ?>
    </div>
</div>