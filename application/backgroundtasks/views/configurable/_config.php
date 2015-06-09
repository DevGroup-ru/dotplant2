<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\backgroundtasks\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;
use kartik\widgets\SwitchInput;
use app\components\Helper;

?>

<div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['icon' => 'cogs', 'title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]) ?>
        <?= $form->field($model, 'daysToStoreNotify') ?>
        <?php BackendWidget::end() ?>
    </div>
</div>
