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
 */
use vova07\imperavi\Widget as ImperaviWidget;
use yii\helpers\Url;

?>
<?=$form->field($model, $property_key.'[0]')->widget(
    ImperaviWidget::className(),
    [
        'settings' => [
            'replaceDivs' => false,
            'minHeight' => 200,
            'paragraphize' => false,
            'pastePlainText' => true,
            'buttonSource' => true,
            'imageManagerJson' => Url::to(['/backend/dashboard/imperavi-images-get']),
            'plugins' => [
                'table',
                'fontsize',
                'fontfamily',
                'fontcolor',
                'video',
                'imagemanager',
            ],
            'replaceStyles' => [],
            'replaceTags' => [],
            'deniedTags' => [],
            'removeEmpty' => [],
            'imageUpload' => Url::to(['/backend/dashboard/imperavi-image-upload']),
        ],
    ]
)?>
