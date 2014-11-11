<?php

/**
 * @var $images \app\models\Image[]
 * @var $this \yii\web\View
 */

use kartik\helpers\Html;

foreach ($images as $image) {
    echo Html::img($image->image_src, ['alt' => $image->image_description, 'itemprop' => "image"]);
}
