<?php

/**
 * @var $this yii\web\View
 * @var $model app\models\ShippingOption
 */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Shipping Options'), 'url' => ['index']],
    $this->params['breadcrumbs'][] = $this->title,
];
?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'car',
                    'title'=> Yii::t('shop', 'Shipping Option'),
                    'footer' => Html::submitButton(
                        Icon::show('save') . Yii::t('app', 'Save'),
                        ['class' => 'btn btn-primary']
                    ),
                ]
            );
        ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'description')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'price_from')->textInput() ?>
            <?= $form->field($model, 'price_to')->textInput() ?>
            <?= $form->field($model, 'cost')->textInput() ?>
            <?= $form->field($model, 'sort')->textInput() ?>
            <?= $form->field($model, 'active')->widget(\kartik\widgets\SwitchInput::className()) ?>
        <?php BackendWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>
