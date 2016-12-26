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
use yii\helpers\Html;

$property = Property::findById($property_id);
$result = "";
$valuesRendered = 0;
foreach ($values->values as $val) {
    if (isset($val['value'])) {
        if (!empty(trim($val['value']))) {
            if ($valuesRendered === 0) {
                $result .= '<meta itemprop="main" content="True"/>';
            }
            $valuesRendered++;
            $result .= Html::tag(
                'dd',
                Html::encode($val['value']),
                [
                    'itemprop' => 'value',
                ]
            );
        }
    }
}

$result = trim($result);

if (!empty($result)) {
    echo '<dl itemprop="itemListElement" itemscope itemtype="http://schema.org/NameValueStructure">'
        . Html::tag('dt', $property->name, ['itemprop'=>'name'])
        . $result . "</dl>\n\n";
}
