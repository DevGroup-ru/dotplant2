<?php
use kartik\select2\Select2;

/**
 * @var $this yii\web\View
 * @var app\backend\components\ActiveForm $form
 * @var \app\modules\shop\models\ProductDiscount $object
 */

?>



<?= $form->field($object, 'product_id');
   /* ->widget(
    Select2::className(),
    [
        'options' => [
            'placeholder' => 'Поиск продуктов ...',
            'multiple' => true,
        ],
        'pluginOptions' => [
            'multiple' => false,
            'allowClear' => true,
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => '/backend/product/ajax-related-product',
                'dataType' => 'json',
                'data' => new \yii\web\JsExpression('function(term,page) { return {search:term}; }'),
                'results' => new \yii\web\JsExpression('function(data,page) { return {results:data.results}; }'),
            ],
        ],
    ]
); */?>
