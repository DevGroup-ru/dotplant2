<?php
$form = \app\backend\components\ActiveForm::begin();

$product = \Yii::$container->get(\app\modules\shop\models\Product::class);
echo \app\backend\widgets\DataRelationsWidget::widget([
    'fields' => $openGraphFields,
    'object' => \app\models\BaseObject::getForClass(get_class($product)),
    'data' => json_decode($model->relation_data),
    'relations' => $relationLinks
]);

echo \yii\helpers\Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

\app\backend\components\ActiveForm::end();