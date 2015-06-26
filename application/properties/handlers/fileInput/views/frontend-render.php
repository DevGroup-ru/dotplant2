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

use app\models\Property;
use yii\helpers\Html;
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
            echo Html::tag('dd', Html::a($val['value'], Url::to([
                'form/download',
                'key' => $val['key'],
                'id' => $values->object_model_id
            ])));
        }
    }
    ?>
</dl>