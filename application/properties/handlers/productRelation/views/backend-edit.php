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
 * @var $values \app\properties\PropertyValue
 */

use app\modules\shop\models\Product;
use yii\helpers\ArrayHelper;

$productIds = ArrayHelper::getColumn($values->values, 'value');
$data = [];
foreach ($values->values as $value) {
    $product = Product::findById($value['value']);
    if (is_object($product)) {
        $data [$product->id] = $product->name;
    }
}

?>

<?=
\app\backend\widgets\Select2Ajax::widget([
    'initialData' => $data,
    'form' => $form,
    'model' => $model,
    'modelAttribute' => $property_key,
    'multiple' => $multiple === 1,
    'searchUrl' => '/shop/backend-product/ajax-related-product',
    'additional' => [
        'placeholder' => Yii::t('app', 'Search'),
    ],
]);
$id = \yii\helpers\Html::getInputId($model, $property_key);
$this->registerJs(<<<js
    $('#$id').select2Sortable();
js
);