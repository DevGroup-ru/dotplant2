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

if ($multiple) {
    echo "<p>Multiple isn't supported for json-property {$property_key}</p>";
} else {
    echo $form->field($model, "${property_key}[0]")->widget(\devgroup\jsoneditor\Jsoneditor::class);
}
