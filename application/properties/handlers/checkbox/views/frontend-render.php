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

use kartik\helpers\Html;

?>
<dl>
    <?= Html::tag('dt', $model->getAttributeLabel($property_key)) ?>
    <?= Html::tag('dd', Yii::t('app', $model->$property_key == 1 ? 'Yes' : 'No')) ?>
</dl>