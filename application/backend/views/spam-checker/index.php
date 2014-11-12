<?php

use app\backend\widgets\BackendWidget;
use kartik\icons\Icon;
use kartik\widgets\ActiveForm;
use yii\helpers\Html;

$this->title = Yii::t('app', 'Spam Checker Settings');
$this->params['breadcrumbs'][] = $this->title;

$form = ActiveForm::begin(['id' => 'spamchecker-form', 'type' => ActiveForm::TYPE_VERTICAL]);

?>
<div class="config-index">
    <div class="row">
        <div class="col-md-4">
            <?php BackendWidget::begin(['title'=> Yii::t('app', 'Spam Checker Settings'), 'icon'=>'list']); ?>

            <?= $form->field($model, 'yandexApiKey') ?>

            <?= $form->field($model, 'akismetApiKey') ?>

            <?= $form->field($model, 'enabledApiKey')->dropDownList(\app\models\SpamChecker::getAvailableApis()); ?>

            <?= $form->field($model, 'configFieldsParentId')->dropDownList(\app\models\SpamChecker::getFieldTypesForForm()) ?>

            <?= Html::submitButton(
                Icon::show('save') . Yii::t('app', 'Save'),
                ['class' => 'btn btn-primary']
            ) ?>

            <?php BackendWidget::end(); ?>
        </div>
        <div class="col-md-8">

        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>