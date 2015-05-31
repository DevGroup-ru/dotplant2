<?php
/**
 * @var \app\modules\shop\models\OrderTransaction $transaction
 */
?>
<?php
    $form = \yii\bootstrap\ActiveForm::begin([
        'id' => 'order-details-form',
        'action' => \yii\helpers\Url::to([
            '/shop/payment/type',
            'id' => $transaction->id,
            'othash' => $transaction->generateHash(),
            'update' => '1'
        ]),
        'layout' => 'horizontal',
    ]);
?>
<?= $form->field($transaction, 'payment_type_id')->dropDownList(array_reduce(\app\modules\shop\models\PaymentType::getPaymentTypes(),
        function($result, $item)
        {
            /** @var \app\modules\shop\models\PaymentType $item */
            $result[$item->id] = $item->name;
            return $result;
        }, [])
); ?>
<?= \yii\helpers\Html::submitButton(Yii::t('app', 'Save')); ?>
<?php $form->end(); ?>