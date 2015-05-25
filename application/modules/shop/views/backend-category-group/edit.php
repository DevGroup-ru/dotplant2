<?php

/* @var $this yii\web\View */
/* @var $model app\modules\shop\models\CategoryGroup */

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = $model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Categories groups'), 'url' => ['index']],
    $this->params['breadcrumbs'][] = $this->title,
];

?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
    <?php
    BackendWidget::begin(
        [
            'icon' => 'tag',
            'title'=> Yii::t('app', 'Categories groups'),
            'footer' => Html::a(
                    Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
                    Yii::$app->request->get('returnUrl', ['index', 'id' => $model->id]),
                    ['class' => 'btn btn-danger']
                ).' '.($model->isNewRecord ? (Html::submitButton(
                    Icon::show('save') . Yii::t('app', 'Save & Go next'),
                    [
                        'class' => 'btn btn-success',
                        'name' => 'action',
                        'value' => 'next',
                    ])):'').' '.(Html::submitButton(
                    Icon::show('save') . Yii::t('app', 'Save & Go back'),
                    [
                        'class' => 'btn btn-warning',
                        'name' => 'action',
                        'value' => 'back',
                    ]
                )).' '.(Html::submitButton(
                    Icon::show('save') . Yii::t('app', 'Save'),
                    [
                        'class' => 'btn btn-primary',
                        'name' => 'action',
                        'value' => 'save',
                    ]
                )),
        ]
    );
    ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>
    <?php BackendWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>
