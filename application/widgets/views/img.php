<?php

/**
 * @var $images \app\models\Image[]
 * @var $this \yii\web\View
 * @var $thumbnailOnDemand boolean
 * @var $thumbnailWidth integer
 * @var $thumbnailHeight integer
 */
use kartik\helpers\Html;
use app\components\Helper;

foreach ($images as $image) {
    $image_src = $image->image_src;
    if ($thumbnailOnDemand === true) {
        $image_src = Helper::thumbnailOnDemand($image_src, $thumbnailWidth, $thumbnailHeight);
    }
    echo Html::beginTag('div', ['itemscope' => '', 'itemtype' => 'http://schema.org/ImageObject']);
    echo Html::img($image_src, ['alt' => $image->image_description, 'itemprop' => "contentUrl"]);
    echo Html::endTag('div');
}
