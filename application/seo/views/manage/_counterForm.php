<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\seo\models\Counter $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="counter-form">

    <?php $form = ActiveForm::begin(['id' => 'counter-form']); ?>

    <?= $model->isNewRecord ? '' : $form->field($model, 'id')->textInput(['disabled' => 'disabled']); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'description')->textarea() ?>

    <?= $form->field($model, 'code')->textarea(['rows' => '30', 'data-editor' => 'html']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
