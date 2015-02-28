<?php

use app\backend\widgets\BackendWidget;
use kartik\helpers\Html;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', 'Config');
$this->params['breadcrumbs'][] = [
    'url' => ['/backend/error-monitor/index'],
    'label' => Yii::t('app', 'Error Monitor')
];
$this->params['breadcrumbs'][] = $this->title;

?>

<?= app\widgets\Alert::widget(
    [
        'id' => 'alert',
    ]
); ?>

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
    <div class="col-md-4">
        <?php
        $form = ActiveForm::begin(
            [
                'id' => 'error_monitor_common_config',
                'type' => ActiveForm::TYPE_VERTICAL,
                'method' => 'get'
            ]
        );
        ?>
            <?php BackendWidget::begin(
                [
                    'title'=> Yii::t('app', 'Common config'),
                    'icon' => 'cogs',
                    'footer' => $this->blocks['submit']
                ]
            ); ?>
                <?= $form->field($model, 'errorMonitorEnabled')->textInput()->widget(\kartik\widgets\SwitchInput::className()); ?>
                <?= $form->field($model, 'emailNotifyEnabled')->textInput()->widget(\kartik\widgets\SwitchInput::className()); ?>
                <?= $form->field($model, 'devmail')->textInput() ?>
                <?= $form->field($model, 'notifyOnlyHttpCodes')->textInput() ?>
                <?= $form->field($model, 'numberElementsToStore')->textInput() ?>
            <?php BackendWidget::end(); ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<div class="row">
    <div class="col-md-4">

        <?php
        $form = ActiveForm::begin(
            [
                'id' => 'error_monitor_immediate_notify_config',
                'type' => ActiveForm::TYPE_VERTICAL,
                'method' => 'get'
            ]
        );
        ?>
            <?php BackendWidget::begin(
                [
                    'title'=> Yii::t('app', 'immediate notify config'),
                    'icon' => 'cogs',
                    'footer' => $this->blocks['submit']
                ]
            ); ?>
                <?= $form->field($model, 'immediateNotice')->textInput()->widget(\kartik\widgets\SwitchInput::className()); ?>
                <?= $form->field($model, 'immediateNoticeLimitPerUrl')->textInput() ?>
                <?= $form->field($model, 'httpCodesForImmediateNotify')->textInput() ?>
            <?php BackendWidget::end(); ?>
        <?php ActiveForm::end(); ?>

    </div>
</div>