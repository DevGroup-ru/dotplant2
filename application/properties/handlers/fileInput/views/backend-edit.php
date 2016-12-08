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
use yii\helpers\Html;

    $uploadDir = !empty($additional['uploadDir']) ? $additional['uploadDir'] : '/';
    $uploadDir = str_replace(Yii::getAlias('@webroot'), '', Yii::getAlias($uploadDir));
    $uploadDir = Url::to(rtrim($uploadDir, '/').'/', true);

    $prop = $multiple ? $property_key.'[]' : $property_key;

    $urlDelete = Url::to(['property-handler', 'handler_action' => 'delete', 'property_id' => $property_id, 'model_id' => $model->getOwnerModel()->id]);
    $urlUpload = Url::to(['property-handler', 'handler_action' => 'upload', 'property_id' => $property_id, 'model_id' => $model->getOwnerModel()->id]);

    $tplFooter = <<< 'TPL'
    <div class="file-thumbnail-footer">
        <div style="margin:5px 0">
            <input class="kv-input kv-new form-control input-sm" type="hidden" value="{caption}" />
        </div>
        {actions}
    </div>
TPL;

    $initialPreview = [];
    $initialPreviewConfig = [];
    $layoutTemplates = [
        'footer' => $tplFooter
    ];
    foreach ($model->$property_key as $file) {
        if (file_exists(Yii::getAlias($additional['uploadDir']) . '/' . $file) === false) {
            continue;
        }
        $_preview = \yii\helpers\FileHelper::getMimeType(Yii::getAlias($additional['uploadDir']) . '/' . $file);
        $_preview =  false !== strpos(strval($_preview), 'image/')
            ? Html::img(
                [
                    'property-handler',
                    'handler_action' => 'show-file',
                    'fileName' => $file,
                    'property_id' => $property_id,
                    'model_id' => $model->ownerModel->id
                ],
                [
                    'class' => 'file-preview-image',
                    'alt' => $file,
                    'title' => $file
                ]
            )
            : \kartik\icons\Icon::show('file', ['style' => 'font-size: 42px']);
        $initialPreview[] = $_preview . Html::hiddenInput($model->formName().'['.$property_key.'][]', $file);
        $initialPreviewConfig[] = [
            'caption' => $file,
            'url' => $urlDelete,
            'key' => $property_key,
            'extra' => ['value' => $file],
        ];
    }

    $modelArrayMode = $model->setArrayMode(false);

// @TODO: maybe it's a good to replace fileuploaded with fileloaded (and this part of code for it)
//    $_js = <<< 'JSCODE'
//    function(event, file, previewId, index, reader) {
//        var name = file.name;
//        var hi = $('<input type="hidden" name="%s" />').val(name);
//        $('div.file-preview-frame[title="'+name+'"]').append(hi);
//    }
//JSCODE;

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
                'uploadUrl' => $urlUpload,
                'multiple' => $multiple,
                'initialPreview' => $initialPreview,
                'initialPreviewConfig' => $initialPreviewConfig,
                'initialPreviewShowDelete' => true,
                'maxFileCount' => $multiple ? 0 : 1,
                'showPreview' => true,
                'showCaption' => true,
                'showRemove' => true,
                'showUpload' => true,
                'overwriteInitial' => false,
                'uploadAsync' => true,
                'layoutTemplates' => $layoutTemplates,
                'allowedPreviewTypes' => ['image'],
            ],
            'pluginEvents' => [
                'fileuploaded' => 'function(event, data, previewId, index) {
                    var name = data.files[index]["name"];
                    try {
                        jQuery(".file-thumbnail-footer input[value=\"" + name + "\"]")
                            .last()
                            .attr("name", event.target.name)
                            .val(data.response[name]["fileName"]);
                    } catch ($e) {}
                }',
            ],
        ]
    );
?>
</div>
<?php $model->setArrayMode($modelArrayMode); ?>

<style>
    .file_input_preview span.file-input > div.file-preview {
        display: block;
    }
</style>