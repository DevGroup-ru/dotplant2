<?php

/**
 * @var $this yii\web\View
 * @var $model \app\modules\shop\models\ShippingOption
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
<?php $form = ActiveForm::begin(); ?>
    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'car',
                    'title'=> Yii::t('app', 'Shipping Option'),
                    'footer' => \app\backend\components\Helper::saveButtons($model),
                ]
            );
        ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'description')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'price_from')->textInput() ?>
            <?= $form->field($model, 'price_to')->textInput() ?>
            <?= $form->field($model, 'sort')->textInput() ?>
            <?= $form->field($model, 'active')->widget(\kartik\widgets\SwitchInput::className()) ?>
        <?php BackendWidget::end(); ?>
    </div>
    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
        <?php
        BackendWidget::begin(
            [
                'icon' => 'gears',
                'title'=> Yii::t('app', 'Shipping option handler config'),
                'footer' => \app\backend\components\Helper::saveButtons($model),
            ]
        );
        ?>
        <?= $form->field($model, 'handler_class')->textInput(['maxlength' => 255]) ?>
        <?= $form->field($model, 'handler_params')->widget(\devgroup\jsoneditor\Jsoneditor::className()) ?>
        <?php BackendWidget::end(); ?>
    </div>
<?php ActiveForm::end(); ?>
