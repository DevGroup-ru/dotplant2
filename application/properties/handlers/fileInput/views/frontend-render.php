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
    echo Html::tag('dt', $property->name);
    foreach ($values->values as $val) {
        if (isset($val['value'])) {
            if(\Yii::$app->request->get('download') == $val['key']){
                \Yii::$app->response->sendFile(Yii::getAlias("@webroot") . $val['value']);
            }
            echo Html::tag('dd', Html::a($val['value'],\yii\helpers\Url::to(['form/view-submission', 'download' => $val['key'], 'id' => $values->object_model_id])));
        }
    }
    ?>
</dl>