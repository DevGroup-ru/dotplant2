<?php
/**
 * @var $attribute_name string
 * @var $form \yii\widgets\ActiveForm
 * @var $label string
 * @var $model \app\properties\AbstractModel
 * @var $multiple boolean
 * @var $property_id integer
 * @var $property_key string
 * @var $this \app\properties\handlers\Handler
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
        <input class="kv-input kv-new form-control input-sm" value="{caption}" placeholder="Введите описание..." />
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
    $initialPreview[] =
        \yii\helpers\Html::img($uploadDir.$file, ['class' => 'file-preview-image', 'alt' => $file, 'title' => $file])
        . \yii\helpers\Html::hiddenInput($model->formName().'['.$property_key.'][]', $file);
    $initialPreviewConfig[] = [
        'caption' => $file,
        'url' => Url::to(['property-handler', 'handler_action' => 'delete', 'property_id' => $property_id, 'model_id' => $model->getOwnerModel()->id]),
        'key' => $property_key,
        'extra' => ['value' => $file],
    ];
}

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
                'uploadUrl' => Url::to(['property-handler', 'handler_action' => 'upload', 'property_id' => $property_id, 'model_id' => $model->getOwnerModel()->id]),
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
            ],
            'pluginEvents' => [
                'fileuploaded' => 'function(event, data, previewId, index) {
                    var $form = jQuery(event.target).parents("form").eq(0);
                    jQuery("<input />").attr("name", event.target.name).val(data.files[index]["name"]).appendTo($form);
                }',
                'filesuccessremove' => 'function(event, id) {
                    // console.log("---------------");
                    // jQuery("#" + id);
                    // console.log(event);
                    // console.log(id);
                    // jQuery("input[data-preview-id=" + key + "]").remove();
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