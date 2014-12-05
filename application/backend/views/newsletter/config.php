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
        <?php $form = ActiveForm::begin(
            [
                'id' => 'newsletter_config',
                'type' => ActiveForm::TYPE_VERTICAL,
                'method' => 'get'
            ]
        ) ?>

            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Newsletter config'), 'icon'=>'cogs', 'footer'=>$this->blocks['submit']]); ?>
                <?= $form->field($model, 'isActive')->textInput()->widget(\kartik\widgets\SwitchInput::className()); ?>
            <?php BackendWidget::end(); ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>



