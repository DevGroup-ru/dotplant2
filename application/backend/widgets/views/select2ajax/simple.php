<?php
/**
 * @var \yii\web\View $this
 * @var \yii\widgets\ActiveForm $form
 * @var \yii\db\ActiveRecord $model
 * @var string $modelAttribute
 * @var array $initialData
 * @var bool $multiple
 * @var string $searchUrl
 * @var array $additional
 */

echo $form->field($model, $modelAttribute)
    ->widget(
        \kartik\widgets\Select2::className(),
        [
            'language' => Yii::$app->language,
            'data' => $initialData,
            'options' => [
                'placeholder' => isset($additional['placeholder']) ? $additional['placeholder'] : '',
                'multiple' => $multiple,
            ],
            'pluginOptions' => [
                'multiple' => $multiple,
                'allowClear' => isset($additional['allowClear']) ? boolval($additional['allowClear']) : true,
                'minimumInputLength' => 3,
                'ajax' => [
                    'url' => $searchUrl,
                    'dataType' => 'json',
                    'data' => new \yii\web\JsExpression('function(term,page) { return {search:term}; }'),
                    'results' => new \yii\web\JsExpression('function(data,page) { return {results:data.results}; }'),
                ],
            ],
        ]
    );