<?php
/**
 * Use existent form
 * @var \yii\web\View $this
 * @var \app\modules\shop\models\Contragent $model
 * @var boolean $immutable
 * @var string $action
 * @var \yii\bootstrap\ActiveForm $form
 */
?>

    <?= $form->field($model, 'type')
        ->dropDownList(
            ['Individual' => Yii::t('app', 'Individual'), 'Self-employed' => Yii::t('app', 'Self-employed'), 'Legal entity' => Yii::t('app', 'Legal entity')],
            ['readonly' => $immutable]
        ); ?>
    <?php
        /** @var \app\properties\AbstractModel $abstractModel */
        $abstractModel = $model->getAbstractModel();
        $abstractModel->setArrayMode(false);
        foreach ($abstractModel->attributes() as $attr) {
            echo $form->field($abstractModel, $attr)->textInput(['readonly' => $immutable]);
        }
    ?>

    <?php if (!empty($model->deliveryInformation)): ?>
    <h3><?= Yii::t('app', 'Delivery information') ?></h3>
    <?= $form->field($model->deliveryInformation, 'country_id')
        ->dropDownList(
            \app\components\Helper::getModelMap(\app\models\Country::className(), 'id', 'name'),
            ['readonly' => $immutable]
        ); ?>
    <?= $form->field($model->deliveryInformation, 'city_id')
        ->dropDownList(
            \app\components\Helper::getModelMap(\app\models\City::className(), 'id', 'name'),
            ['readonly' => $immutable]
        ); ?>
    <?= $form->field($model->deliveryInformation, 'zip_code')->textInput(['readonly' => $immutable]); ?>
    <?= $form->field($model->deliveryInformation, 'address')->textarea(['readonly' => $immutable]); ?>
    <?php endif; ?>