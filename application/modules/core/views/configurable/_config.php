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
    <div class="col-md-5 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'serverName') ?>
        <?= $form->field($model, 'composerHomeDirectory') ?>
        <?= $form->field($model, 'internalEncoding') ?>
        <?= $form->field($model, 'autoCompleteResultsCount') ?>
        <?= $form->field($model, 'wysiwyg_id')->dropDownList(\app\modules\core\models\Wysiwyg::itemsForSelect()) ?>
        <?= $form->field($model, 'fileUploadPath') ?>
        <?= $form->field($model, 'removeUploadedFiles')->widget(SwitchInput::className()) ?>
        <?= $form->field($model, 'overwriteUploadedFiles')->widget(SwitchInput::className()) ?>
        <?= $form->field($model, 'daysToStoreSubmissions') ?>
        <?php BackendWidget::end() ?>
    </div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Email configuration'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'spamCheckerApiKey')
            ->dropDownList(Helper::getModelMap(SpamChecker::className(), 'behavior', 'name'));
        ?>
        <?= $form->field($model, 'emailConfig[transport]')
            ->dropDownList([
                'Swift_MailTransport' => 'Mail',
                'Swift_SmtpTransport' => 'SMTP',
                'Swift_SendmailTransport' => 'Sendmail',
            ])
            ->label('Mail transport'); ?>
        <?= $form->field($model, 'emailConfig[host]')->label('Mail server'); ?>
        <?= $form->field($model, 'emailConfig[username]')->label('Mail username'); ?>
        <?= $form->field($model, 'emailConfig[password]')->label('Mail password'); ?>
        <?= $form->field($model, 'emailConfig[port]')->label('Mail server port'); ?>
        <?= $form->field($model, 'emailConfig[encryption]')
            ->dropDownList([
                '' => '',
                'ssl' => 'Use SSL',
                'tls' => 'Use TLS'
            ])
            ->label('Mail encryption'); ?>
        <?= $form->field($model, 'emailConfig[mailFrom]')->label('Mail from'); ?>
        <?= $form->field($model, 'emailConfig[sendMail]')->label('Path to sendmail'); ?>
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

