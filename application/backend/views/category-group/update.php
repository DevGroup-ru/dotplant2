<?php

/* @var $this yii\web\View */
/* @var $model app\models\CategoryGroup */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('shop', 'Categories groups'), 'url' => ['index']],
    $this->params['breadcrumbs'][] = $this->title,
];

?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
    <?php
    BackendWidget::begin(
        [
            'icon' => 'tag',
            'title'=> Yii::t('shop', 'Categories groups'),
            'footer' => Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save'),
                ['class' => 'btn btn-primary']
            ),
        ]
    );
    ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    <?php BackendWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>
