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

use app\models\Property;
use app\modules\shop\models\Product;
use yii\helpers\ArrayHelper;
use kartik\helpers\Html;

$productIds = ArrayHelper::getColumn($values->values, 'value');
/** @var Product[] $products */
$products = [];
foreach ($productIds as $id) {
    $product = Product::findById($id);
    if ($product !== null) {
        $products[] = $product;
    }
}

?>

<dl>
    <?php
    if (count($products) == 0) {
        return;
    }
    $property = Property::findById($property_id);
    echo Html::tag('dt', $property->name);
    foreach ($products as $product) {
        echo Html::tag('dd', Html::a($product->name, ['@product', 'model' => $product]));
    }
    ?>
</dl>
