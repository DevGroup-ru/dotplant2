<?php

/** @var \app\modules\config\models\Configurable $configurable */
use yii\bootstrap\Tabs;

/** @var \app\backend\components\ActiveForm $form */
/** @var \app\modules\image\models\ConfigConfigurableModel $model */

?>

<div class="row">
    <div class="col-md-6 col-sm-12">
        <?=$form->field($model, 'useWatermark')->widget(\kartik\widgets\SwitchInput::className())?>
        <?=$form->field($model, 'defaultThumbnailSize')?>
        <?=$form->field($model, 'noImageSrc')?>
        <?=$form->field($model, 'thumbnailsDirectory')?>
        <?=$form->field($model, 'watermarkDirectory')?>
        <?php
        $configTabs = [];
        foreach ($model['components'] as $componentName => $componentConf) {
            $item = [];
            $item['label'] = $componentName;

            $necessaryContent = '';
            foreach ($componentConf['necessary'] as $necessaryConfName => $necessaryConfVal) {
                $content = $form->field($model, "components[{$componentName}][necessary][{$necessaryConfName}]")->label(
                    $necessaryConfName
                );
                if (is_bool($necessaryConfVal) === true) {
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
            $item['content'] = Tabs::widget(
                [
                    'items' => [
                        ['label' => Yii::t('app', 'necessary'), 'content' => $necessaryContent],
                        ['label' => Yii::t('app', 'unnecessary'), 'content' => $unnecessaryContent]
                    ]
                ]
            );
            $configTabs[] = $item;
        }
        ?>
        <?=Tabs::widget(['items' => $configTabs]);?>
    </div>
</div>

