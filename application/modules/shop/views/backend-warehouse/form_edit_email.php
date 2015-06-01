<?php
use app\backend\widgets\BackendWidget;
use app\backend\components\ActiveForm;
use yii\helpers\Html;

?>
<?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_INLINE]); ?>
<?php /** @var $form ActiveForm */ ?>
<?php BackendWidget::begin([
    'title' => Yii::t('app', 'Warehouse Email'),
    'icon' => 'cog',
    'footer' => Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success'])
]); ?>


    <div class="clearfix"></div>
<?= $form->errorSummary($warehouseEmail); ?>
    <div class="pull-right">
        <?= $form->field($warehouseEmail, 'name') ?>
        <?= $form->field($warehouseEmail, 'email') ?>
        <?= $form->field($warehouseEmail, 'sort_order') ?>

    </div>
    <div class="clearfix"></div>
<?php BackendWidget::end(); ?>
<?php ActiveForm::end(); ?>