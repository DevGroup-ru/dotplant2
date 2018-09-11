<?php
/**
 * @var \app\modules\config\models\Configurable $configurable
 * @var \app\backend\components\ActiveForm $form
 * @var \app\modules\seo\models\ConfigConfigurationModel $model
 * @var \yii\web\View $this
 */
use app\backend\widgets\BackendWidget;
use app\modules\seo\SeoModule;
use kartik\switchinput\SwitchInput;
use app\modules\shop\models\Currency;

    if (is_array($model->include)) {
        $model->include = implode(',', $model->include);
    }

    $currencies = [\app\modules\seo\handlers\AnalyticsHandler::CURRENCY_MAIN => Yii::t('app', 'Main currency'),
            \app\modules\seo\handlers\AnalyticsHandler::CURRENCY_USER => Yii::t('app', 'User currency'),]
        + \yii\helpers\ArrayHelper::map(Currency::find()->select(['iso_code','name'])->asArray()->all(), 'iso_code', 'name');
?>

<div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
            <?= $form->field($model, 'mainPage') ?>
            <?= $form->field($model, 'include') ?>
            <?= $form->field($model, 'redirectWWW')->dropDownList(SeoModule::getRedirectTypes()) ?>
            <?= $form->field($model, 'redirectTrailingSlash')->widget(SwitchInput::className()) ?>
        <?php BackendWidget::end(); ?>

        <?php BackendWidget::begin(['title' => Yii::t('app', 'Analytics'), 'options' => ['class' => 'visible-header']]); ?>
            <div class="col-md-6">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <?= Yii::t('app', 'Google ecommerce'); ?>
                    </div>
                    <div class="panel-body">
                        <?= $form->field($model, 'analytics[ecGoogle][active]')->widget(SwitchInput::className())->label(Yii::t('app', 'Active')); ?>
                        <?= $form->field($model, 'analytics[ecGoogle][currency]')->dropDownList($currencies)->label(Yii::t('app', 'Currency')); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <?= Yii::t('app', 'Yandex ecommerce'); ?>
                    </div>
                    <div class="panel-body">
                        <?= $form->field($model, 'analytics[ecYandex][active]')->widget(SwitchInput::className())->label(Yii::t('app', 'Active')); ?>
                        <?= $form->field($model, 'analytics[ecYandex][currency]')->dropDownList($currencies)->label(Yii::t('app', 'Currency')); ?>
                    </div>
                </div>
            </div>
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
