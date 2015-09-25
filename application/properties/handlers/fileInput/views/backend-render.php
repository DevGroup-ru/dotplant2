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
use yii\helpers\Url;

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
            if ($model->getOwnerModel()->className() === \app\modules\shop\models\Order::className())
            {
                echo Html::tag('dd', Html::a($val['value'], 'http://'. Yii::$app->getModule('core')->serverName . Url::to([
                    '/shop/backend-order/download-file',
                    'key' => $val['key'],
                    'orderId' => $values->object_model_id
                ])));
            } else {
                echo Html::tag('dd', Html::a($val['value'], 'http://'. Yii::$app->getModule('core')->serverName . Url::to([
                    '/backend/form/download',
                    'key' => $val['key'],
                    'submissionId' => $values->object_model_id
                ])));
            }
        }

    }

    ?>
</dl>