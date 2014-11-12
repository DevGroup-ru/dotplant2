<?php
use kartik\widgets\ActiveForm;

?>

<div class="row">
    <div class="col-sm-4">
        <?php $form = ActiveForm::begin(
            [
                'id' => 'subscribe',
                'action' => $action
            ]
        ); ?>
        <?= $form->field($model, 'name') ?>
        <?= $form->field($model, 'email') ?>
        <?= \kartik\helpers\Html::submitButton($submitButtonText) ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
