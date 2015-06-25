<?php
/**
 * @var \yii\web\View $this
 * @var \yii\bootstrap\ActiveForm $form
 * @var \app\modules\shop\models\PaymentType[] $paymentTypes
 * @var integer $totalPayment
 */

    $currency = \app\modules\shop\models\Currency::getMainCurrency();
?>
<div class="col-md-6 col-md-offset-3">
    <div class="row">
        <div>К оплате: <?= $currency->format($totalPayment); ?></div>
        <?= \yii\bootstrap\Html::dropDownList('PaymentType', null, array_reduce($paymentTypes,
            function($result, $item)
            {
                /** @var \app\modules\shop\models\PaymentType $item */
                $result[$item->id] = $item->name;
                return $result;
            }, []),
            [
                'class' => 'form-control',
            ]
        ); ?>
    </div>
</div>