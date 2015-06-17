<?php
/**
 * @var $attribute_name string
 * @var $form \yii\widgets\ActiveForm
 * @var $label string
 * @var $model \app\properties\AbstractModel
 * @var $multiple boolean
 * @var $property_id integer
 * @var $property_key string
 * @var $this \yii\web\View
 * @var $values array
 * @var $additional array
 */

use \yii\helpers\Url;

    $uploadDir = !empty($additional['uploadDir']) ? $additional['uploadDir'] : '/';
    $uploadDir = str_replace(Yii::getAlias('@webroot'), '', Yii::getAlias($uploadDir));
    $uploadDir = Url::to(rtrim($uploadDir, '/').'/', true);

    $prop = $multiple ? $property_key.'[]' : $property_key;

    $tplFooter = <<< 'TPL'
    <div class="file-thumbnail-footer">
        <div style="margin:5px 0">
            {caption}
        </div>
        <button type="button" class="kv-file-remove btn btn-xs btn-default pull-right" title="Remove file"><i class="glyphicon glyphicon-trash text-danger"></i></button>
    </div>
TPL;

    $modelArrayMode = $model->setArrayMode(false);
    ?>
    <div class="file_input_preview">
        <?=
        $form->field($model, $prop)->widget(
            \kartik\widgets\FileInput::classname(),
            [
                'options' => [
                    'multiple' => $multiple,
                ],
                'pluginOptions' => [
                    'multiple' => $multiple,
                    'maxFileCount' => $multiple ? 0 : 1,
                    'showPreview' => true,
                    'showCaption' => true,
                    'showRemove' => true,
                    'showUpload' => false,
                    'overwriteInitial' => false,
                    'uploadAsync' => false,
                    'layoutTemplates' => ['footer' => $tplFooter,],
                    'allowedPreviewTypes' => ['image'],
                ],
            ]
        );
        ?>
    </div>
    <?php $model->setArrayMode($modelArrayMode); ?>

    <style>
        .file_input_preview span.file-input > div.file-preview {
            /*display: block;*/
        }
    </style>