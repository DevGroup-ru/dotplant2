<?php
/**
 * Use existent form
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\DeliveryInformation $deliveryInformation
 * @var \app\modules\shop\models\OrderDeliveryInformation $orderDeliveryInformation
 * @var boolean $immutable
 * @var string $action
 * @var \yii\bootstrap\ActiveForm $form
 */
?>

    <h3><?= Yii::t('app', 'Delivery information') ?></h3>
    <?= $form->field($deliveryInformation, 'country_id')
        ->dropDownList(
            \app\components\Helper::getModelMap(\app\models\Country::className(), 'id', 'name'),
            ['readonly' => $immutable]
    ); ?>
    <?= $form->field($deliveryInformation, 'city_id')
        ->dropDownList(
            \app\components\Helper::getModelMap(\app\models\City::className(), 'id', 'name'),
            ['readonly' => $immutable]
    ); ?>
    <?= $form->field($deliveryInformation, 'zip_code')->textInput(['readonly' => $immutable]); ?>
    <?= $form->field($deliveryInformation, 'address')->textarea(['readonly' => $immutable]); ?>

    <?= $form->field($orderDeliveryInformation, 'shipping_option_id')
        ->dropDownList(
            \app\components\Helper::getModelMap(\app\modules\shop\models\ShippingOption::className(), 'id', 'name'),
            ['readonly' => $immutable]
    ); ?>

    <?php
        /** @var \app\properties\AbstractModel $abstractModel */
        $abstractModel = $orderDeliveryInformation->getAbstractModel();
        $abstractModel->setArrayMode(false);
        foreach ($abstractModel->attributes() as $attr) {
            echo $form->field($abstractModel, $attr)->textInput(['readonly' => $immutable]);
        }
    ?>

