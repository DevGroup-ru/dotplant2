<?php

use app\backend\widgets\BackendWidget;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Newsletter config');
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget([
    'id' => 'alert',
]); ?>

<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        ['class' => 'btn btn-success']
    ) ?>
</div>
<?php $this->endBlock('submit'); ?>

<div class="row">
    <div class="col-md-4" id="jstree-more">
        <?php if (null != $model) { ?>

            <?php $form = ActiveForm::begin(
                [
                    'id' => 'subscribe_email_update',
                    'type' => ActiveForm::TYPE_VERTICAL,
                    'method' => 'get'
                ]
            ); ?>

            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Edit subscribe'), 'icon'=>'edit', 'footer'=>$this->blocks['submit']]); ?>
                <?= $form->field($model, 'name')->textInput() ?>
                <?= $form->field($model, 'email')->textInput() ?>
                <?= $form->field($model, 'is_active')->textInput()->widget(\kartik\widgets\SwitchInput::className()); ?>
            <?php BackendWidget::end(); ?>

            <?php $form = ActiveForm::end(); ?>

        <?php } ?>
    </div>
</div>



