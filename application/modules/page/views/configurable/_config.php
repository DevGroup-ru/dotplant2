<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\shop\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;

?>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
        <?= $form->field($model, 'minPagesPerList') ?>

        <?= $form->field($model, 'maxPagesPerList') ?>

        <?= $form->field($model, 'pagesPerList') ?>

        <?= $form->field($model, 'searchResultsLimit') ?>

        <?php BackendWidget::end() ?>
    </div>
</div>