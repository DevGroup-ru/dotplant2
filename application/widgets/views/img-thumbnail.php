<?php

/**
 * @var $images \app\models\Image[]
 * @var $this \yii\web\View
 */

use kartik\helpers\Html;
//@todo rewrite
foreach ($images as $image) {
    echo Html::a(Html::img($image->thumbnail_src, ['alt' => $image->image_description]), $image->image_src, []);
}
