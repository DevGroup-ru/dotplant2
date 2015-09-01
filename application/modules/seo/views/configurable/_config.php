<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\seo\models\ConfigConfigurationModel $model */
/** @var \yii\web\View $this */

use app\backend\widgets\BackendWidget;
use app\modules\seo\SeoModule;
use kartik\widgets\SwitchInput;

if (is_array($model->include)) {
    $model->include = implode(',', $model->include);
}

?>

<div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
            <?= $form->field($model, 'mainPage') ?>
            <?= $form->field($model, 'include') ?>
            <?= $form->field($model, 'redirectWWW')->dropDownList(SeoModule::getRedirectTypes()) ?>
            <?= $form->field($model, 'redirectTrailingSlash')->widget(SwitchInput::className()) ?>
        <?php BackendWidget::end(); ?>
    </div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Meta cache'), 'options' => ['class' => 'visible-header']]); ?>
            <?= $form->field($model, 'cacheConfig[metaCache][name]')->label(Yii::t('app', 'Name')) ?>
            <?= $form->field($model, 'cacheConfig[metaCache][expire]')->label(Yii::t('app', 'Duration')) ?>
        <?php BackendWidget::end(); ?>
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Counters cache'), 'options' => ['class' => 'visible-header']]); ?>
            <?= $form->field($model, 'cacheConfig[counterCache][name]')->label(Yii::t('app', 'Name')) ?>
            <?= $form->field($model, 'cacheConfig[counterCache][expire]')->label(Yii::t('app', 'Duration')) ?>
        <?php BackendWidget::end(); ?>
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Robots cache'), 'options' => ['class' => 'visible-header']]); ?>
            <?= $form->field($model, 'cacheConfig[robotsCache][name]')->label(Yii::t('app', 'Name')) ?>
            <?= $form->field($model, 'cacheConfig[robotsCache][expire]')->label(Yii::t('app', 'Duration')) ?>
        <?php BackendWidget::end(); ?>
    </div>
</div>
