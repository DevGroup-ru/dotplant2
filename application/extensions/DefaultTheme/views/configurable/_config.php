<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\shop\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;

?>

<div class="row">
    <div class="col-md-5 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Common settings'), 'options' => ['class' => 'visible-header']]); ?>


        <?php BackendWidget::end() ?>
    </div>
    <div class="col-md-5 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main page'), 'options' => ['class' => 'visible-header']]); ?>




        <?php BackendWidget::end() ?>
    </div>
</div>