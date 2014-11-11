<?php

/* @var $this yii\web\View */
/* @var $model app\models\Config */

use kartik\helpers\Html;
use kartik\icons\Icon;
use app\backend\widgets\BackendWidget;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', $model->isNewRecord ? 'Create' : 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Configs'), 'url' => ['index']],
    $this->params['breadcrumbs'][] = $this->title,
];

?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'gear',
                    'title'=> Yii::t('shop', 'Config'),
                    'footer' => Html::submitButton(
                        Icon::show('save') . Yii::t('app', 'Save'),
                        ['class' => 'btn btn-primary']
                    ),
                ]
            );
        ?>
            <?= $form->field($model, 'name')->textInput(['maxlength' => 50]) ?>
            <?= $form->field($model, 'key')->textInput(['maxlength' => 50]) ?>
            <?= $form->field($model, 'value')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'preload')->textInput()->widget(\kartik\widgets\SwitchInput::className()) ?>
        <?php BackendWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>
