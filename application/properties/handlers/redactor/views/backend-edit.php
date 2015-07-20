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
    Yii::$app->getModule('core')->wysiwyg_class_name(),
    Yii::$app->getModule('core')->wysiwyg_params()
)?>
