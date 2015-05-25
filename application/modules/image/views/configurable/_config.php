<?php

/** @var \app\modules\config\models\Configurable $configurable */
/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\image\models\ConfigConfigurationModel $model */

use app\backend\widgets\BackendWidget;
use yii\bootstrap\Tabs;

?>

<div>
    <div class="col-md-6 col-sm-12">
        <?php BackendWidget::begin(['title' => Yii::t('app', 'Main settings'), 'options' => ['class' => 'visible-header']]); ?>
        <?=$form->field($model, 'useWatermark')->widget(\kartik\widgets\SwitchInput::className())?>
        <?=$form->field($model, 'defaultThumbnailSize')?>
        <?=$form->field($model, 'noImageSrc')?>
        <?=$form->field($model, 'thumbnailsDirectory')?>
        <?=$form->field($model, 'watermarkDirectory')?>
        <?php BackendWidget::end() ?>
    </div>
    <div class="col-md-6 col-sm-12" id="fs-config">
        <?php
        foreach ($model['components'] as $componentName => $componentConf) {
            BackendWidget::begin(['id' => $componentName, 'title' => Yii::t('app', $componentName)]);

            $necessaryContent = '';
            foreach ($componentConf['necessary'] as $necessaryConfName => $necessaryConfVal) {
                $content = $form->field($model, "components[{$componentName}][necessary][{$necessaryConfName}]")->label(
                    $necessaryConfName
                );
                if (is_bool($necessaryConfVal) === true || $necessaryConfName === 'active') {
                    $content = $content->widget(\kartik\widgets\SwitchInput::className());
                }
                $necessaryContent .= $content;
            }

            $unnecessaryContent = '';
            foreach ($componentConf['unnecessary'] as $unnecessaryConfName => $unnecessaryConfVal) {
                $unnecessaryContent .= $form->field(
                    $model,
                    "components[{$componentName}][unnecessary][{$unnecessaryConfName}]"
                )->label(
                    $unnecessaryConfName
                );
            }
            echo Tabs::widget(
                [
                    'items' => [
                        ['label' => Yii::t('app', 'necessary'), 'content' => $necessaryContent],
                        ['label' => Yii::t('app', 'unnecessary'), 'content' => $unnecessaryContent]
                    ]
                ]
            );
            BackendWidget::end();
        }
        ?>
    </div>
</div>

