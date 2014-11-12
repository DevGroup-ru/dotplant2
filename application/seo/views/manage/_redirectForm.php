<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var app\seo\models\Redirect $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="redirect-form">

    <?php $form = ActiveForm::begin(['id' => 'redirect-form']); ?>

    <?= $model->isNewRecord ? '' : $form->field($model, 'id')->textInput(['disabled' => 'disabled']); ?>

    <?= $form->field($model, 'type')->dropDownList(\app\seo\models\Redirect::getTypes()) ?>

    <?= $form->field($model, 'from')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'to')->textInput(['maxlength' => 255]) ?>

    <?= $form->field($model, 'active')->checkbox([0 => 'false', 1 => 'true']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
