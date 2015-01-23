<?php

/**
 * @var $images \app\models\Image[]
 * @var $this \yii\web\View
 */

use kartik\helpers\Html;

foreach ($images as $image) {
    echo Html::a(Html::img(Yii::$app->request->baseUrl.$image->thumbnail_src, ['alt' => $image->image_description]), Yii::$app->request->baseUrl.$image->image_src, []);
}
