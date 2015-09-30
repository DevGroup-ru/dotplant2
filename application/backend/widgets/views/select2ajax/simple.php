<?php
/**
 * @var \yii\web\View $this
 * @var \yii\widgets\ActiveForm $form
 * @var \yii\db\ActiveRecord $model
 * @var string $modelAttribute
 * @var array $initialData
 * @var bool $multiple
 * @var string $searchUrl
 * @var array $pluginOptions
 * @var array $additional
 */

$defaultOptions = [
    'multiple' => $multiple,
    'allowClear' => true,
    'minimumInputLength' => 3,
    'ajax' => [
        'url' => $searchUrl,
        'dataType' => 'json',
        'data' => new \yii\web\JsExpression('function(term,page) { return {search:term}; }'),
        'results' => new \yii\web\JsExpression('function(data,page) { return {results:data.results}; }'),
        'cache' => false,
    ],
];

echo $form->field($model, $modelAttribute, !empty($additional['fieldOptions'])?$additional['fieldOptions']:[])
    ->widget(
        \kartik\widgets\Select2::className(),
        [
            'language' => Yii::$app->language,
            'data' => $initialData,
            'options' => [
                'placeholder' => isset($additional['placeholder']) ? $additional['placeholder'] : Yii::t('app', 'Type for search ...'),
                'multiple' => $multiple,
            ],
            'pluginOptions' => array_replace_recursive($defaultOptions, $pluginOptions),
        ]
    );
$id = \yii\helpers\Html::getInputId($model, $modelAttribute);
$this->registerJs(<<<js
    $('#$id').select2Sortable();
js
);