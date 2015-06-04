<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\core\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;
use app\models\SpamChecker;
use app\components\Helper;
use kartik\widgets\SwitchInput;

?>

<div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'serverName') ?>
        <?= $form->field($model, 'composerHomeDirectory') ?>
        <?= $form->field($model, 'internalEncoding') ?>
        <?= $form->field($model, 'autoCompleteResultsCount') ?>
        <?= $form->field($model, 'fileUploadPath') ?>
        <?= $form->field($model, 'daysToStoreSubmissions') ?>
        <?php BackendWidget::end() ?>
    </div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Spam checker'), 'options' => ['class' => 'visible-header']]); ?>
        <?=
        $form
            ->field($model, 'spamCheckerApiKey')
            ->dropDownList(Helper::getModelMap(SpamChecker::className(), 'behavior', 'name'))
        ?>
        <?php BackendWidget::end() ?>

        <?php BackendWidget::begin(['title' => Yii::t('app', 'Error monitor'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'errorMonitorEnabled')->widget(SwitchInput::className()) ?>
        <?= $form->field($model, 'emailNotifyEnabled')->widget(SwitchInput::className()) ?>
        <?= $form->field($model, 'devmail') ?>
        <?= $form->field($model, 'notifyOnlyHttpCodes') ?>
        <?= $form->field($model, 'numberElementsToStore') ?>
        <?= $form->field($model, 'immediateNotice')->widget(SwitchInput::className()) ?>
        <?= $form->field($model, 'immediateNoticeLimitPerUrl') ?>
        <?= $form->field($model, 'httpCodesForImmediateNotify') ?>
        <?php BackendWidget::end() ?>
    </div>
</div>

