<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\shop\models\ConfigConfigurableModel $model */

?>



<div class="row">
    <div class="col-md-6 col-sm-12">
        <?= $form->field($model, 'exportDirPath') ?>
        <?= $form->field($model, 'importDirPath') ?>
        <?= $form->field($model, 'defaultType')->dropDownList(\app\modules\data\models\ImportModel::knownTypes()) ?>
    </div>
</div>