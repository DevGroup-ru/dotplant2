<?php

use app\backgroundtasks\models\Task;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\icons\Icon;

/**
 * @var yii\web\View $this
 * @var app\backgroundtasks\models\Task $model
 * @var yii\widgets\ActiveForm $form
 */
?>
<?php $this->beginBlock('submit'); ?>
<div class="form-group no-margin">
    <?=
    Html::a(
        Icon::show('arrow-circle-left') . Yii::t('app', 'Back'),
        Yii::$app->request->get('returnUrl', ['/background/manage/index']),
        ['class' => 'btn btn-danger']
    )
    ?>
    <?php if ($model->isNewRecord): ?>
        <?=
        Html::submitButton(
            Icon::show('save') . Yii::t('app', 'Save & Go next'),
            [
                'class' => 'btn btn-success',
                'name' => 'action',
                'value' => 'next',
            ]
        )
        ?>
    <?php endif; ?>
    <?= Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save & Go back'),
        [
            'class' => 'btn btn-warning',
            'name' => 'action',
            'value' => 'back',
        ]
    ); ?>
    <?=
    Html::submitButton(
        Icon::show('save') . Yii::t('app', 'Save'),
        [
            'class' => 'btn btn-primary',
            'name' => 'action',
            'value' => 'save',
        ]
    )
    ?>



</div>
<?php $this->endBlock('submit'); ?>
<div class="tasks-form">

	<?= \app\widgets\Alert::widget([
		'id' => 'alert',
	]); ?>

	<?php $form = ActiveForm::begin(['id' => 'tasks-form']); ?>

		<?php if(!$model->isNewRecord): ?>
		<?= $form->field($model, 'id')->textInput(['disabled' => 'disabled']); ?>
		<?php endif; ?>

		<?= $form->field($model, 'type', ['template' => '{input}'])->input('hidden'); ?>

		<?= $model->isNewRecord ? $form->field($model, 'initiator', ['template' => '{input}'])->input('hidden') : $form->field($model->initiatorUser, 'username')->textInput(['disabled' => 'disabled']); ?>

		<?= $form->field($model, 'name')->textInput(['maxlength' => 255]) ?>

		<?= $form->field($model, 'action')->hint($model->attributeHints()['action'])->textInput(['maxlength' => 255]) ?>

		<?= $form->field($model, 'params')->hint($model->attributeHints()['params'])->textArea() ?>

		<?= $form->field($model, 'description')->textArea() ?>

		<?= $form->field($model, 'cron_expression')->textArea() ?>

		<?php if(!$model->isNewRecord): ?>
		<?= $form->field($model, 'ts')->textInput(['disabled' => 'disabled']) ?>
		<?php endif; ?>

		<?= $form->field($model, 'status')->dropDownList(Task::getStatuses(Task::TYPE_REPEAT)); ?>

        <?= $this->blocks['submit'] ?>



	<?php ActiveForm::end(); ?>

</div>
