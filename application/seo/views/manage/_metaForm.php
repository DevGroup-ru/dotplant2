<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\seo\models\Meta $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="meta-form">

    <?php $form = ActiveForm::begin(['id' => 'meta-form']); ?>

    <?= $model->isNewRecord ? $form->field($model, 'key')->textInput() : $form->field($model, 'key')->textInput(['disabled' => 'disabled']); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'content')->textInput(['maxlength' => 255]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
