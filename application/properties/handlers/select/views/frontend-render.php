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

use app\models\Property;
use kartik\helpers\Html;

?>
<dl>
    <?php
    if (count($values->values) == 0) {
        return;
    }
    $property = Property::findById($property_id);
    $result = "";
    foreach ($values->values as $val) {
        if (isset($val['value'])) {
            $result .= Html::tag('dd', $val['value']);
        }
    }
    if (!empty($result)) {
        echo Html::tag('dt', $property->name) . $result;
    }
    ?>
</dl>