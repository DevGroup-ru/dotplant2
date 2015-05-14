<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\seo\models\ConfigConfigurationModel $model */
/** @var \yii\web\View $this */

use app\backend\widgets\BackendWidget;

if (is_array($model->include)) {
    $model->include = implode(',', $model->include);
}

?>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?= $form->field($model, 'mainPage') ?>
        <?= $form->field($model, 'include') ?>
    </div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['id' => 'meta-form', 'title' => Yii::t('app', 'Meta cache')]); ?>
        <?= $form->field($model, 'cacheConfig[metaCache][name]')->label(Yii::t('app', 'Name')) ?>
        <?= $form->field($model, 'cacheConfig[metaCache][expire]')->label(Yii::t('app', 'Duration')) ?>
        <?php BackendWidget::end(); ?>
        <?php BackendWidget::begin(['id' => 'counter-form', 'title' => Yii::t('app', 'Counters cache')]); ?>
        <?= $form->field($model, 'cacheConfig[counterCache][name]')->label(Yii::t('app', 'Name')) ?>
        <?= $form->field($model, 'cacheConfig[counterCache][expire]')->label(Yii::t('app', 'Duration')) ?>
        <?php BackendWidget::end(); ?>
        <?php BackendWidget::begin(['id' => 'robots-form', 'title' => Yii::t('app', 'Robots cache')]); ?>
        <?= $form->field($model, 'cacheConfig[robotsCache][name]')->label(Yii::t('app', 'Name')) ?>
        <?= $form->field($model, 'cacheConfig[robotsCache][expire]')->label(Yii::t('app', 'Duration')) ?>
        <?php BackendWidget::end(); ?>
    </div>
</div>
