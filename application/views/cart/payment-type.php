<?php
/**
 * @var $this yii\web\View
 * @var $order \app\models\Order
 * @var $paymentTypes \app\models\PaymentType[]
 */
$this->title = Yii::t('shop', 'Select payment type');
?>
<h1><?= $this->title ?></h1>
<?php
    $form = \kartik\widgets\ActiveForm::begin(
        [
            'id' => 'payment-type-form',
            'action' => ['/cart/payment-type', 'id' => $order->id],
            'enableClientValidation' => false,
        ]
    );
?>

<?= $form->field($order, 'payment_type_id')->radioList(\yii\helpers\ArrayHelper::map($paymentTypes, 'id', 'name')); ?>
<?= $this->render('order-items', ['order' => $order]); ?>
<?= \kartik\helpers\Html::submitButton(Yii::t('shop', 'Payment'), ['class' => 'btn btn-primary']); ?>
<?=
    \kartik\helpers\Html::a(
        Yii::t('shop', 'Print'),
        '#',
        [
            'class' => 'btn btn-default pull-right',
            'id' => 'print-page',
        ]
    )
?>
<?php \kartik\widgets\ActiveForm::end();