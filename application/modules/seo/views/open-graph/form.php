<?php
$form = \app\backend\components\ActiveForm::begin();


echo \app\backend\widgets\DataRelationsWidget::widget([
    'fields' => $openGraphFields,
    'object' => \app\models\Object::getForClass(\app\modules\shop\models\Product::className()),
    'data' => json_decode($model->relation_data),
    'relations' => $relationLinks
]);

echo \yii\helpers\Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

\app\backend\components\ActiveForm::end();