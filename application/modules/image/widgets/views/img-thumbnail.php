<?php

/**
 * @var $images \app\modules\image\models\Image[]
 * @var $this \yii\web\View
 */

use kartik\helpers\Html;

foreach ($images as $image) {
    echo Html::a(Html::img($image->thumbnail_src, ['alt' => $image->image_description]), $image->image_src, []);
}
