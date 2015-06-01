<?php
use app\backend\widgets\BackendWidget;
use app\backend\components\ActiveForm;
use \yii\helpers\Html;

?>

<?php $form = ActiveForm::begin(['type' => ActiveForm::TYPE_INLINE]); ?>
<?php /** @var $form ActiveForm */ ?>
<?php BackendWidget::begin([
    'title' => Yii::t('app', 'Warehouse Phone'),
    'icon' => 'cog',
    'footer' => Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success'])
]); ?>


    <div class="clearfix"></div>
<?= $form->errorSummary($warehousePhone); ?>
    <div class="pull-right">
        <?= $form->field($warehousePhone, 'name') ?>
        <?= $form->field($warehousePhone, 'phone') ?>
        <?= $form->field($warehousePhone, 'sort_order') ?>

    </div>
    <div class="clearfix"></div>
<?php BackendWidget::end(); ?>
<?php ActiveForm::end(); ?>