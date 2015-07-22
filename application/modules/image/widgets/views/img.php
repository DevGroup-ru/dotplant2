<?php

/**
 * @var $images \app\modules\image\models\Image[]
 * @var $this \yii\web\View
 * @var $thumbnailOnDemand boolean
 * @var $thumbnailWidth integer
 * @var $thumbnailHeight integer
 */

use app\modules\image\models\Image;
use kartik\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

foreach ($images as $image) {
    $image_src = $image->file;
    if ($thumbnailOnDemand === true) {
        $image_src = $image->getThumbnail("{$thumbnailWidth}x{$thumbnailHeight}", $useWatermark);
    }
    $title = $image->image_title;
    $alt = $image->image_alt;
    if (empty($image->image_alt) === true) {
        $alt = $title;
    }
    $imgHtml = Html::img($image_src, ['title' => $title, 'alt' => $alt, 'itemprop' => "contentUrl"]);
    echo Html::tag('div', $imgHtml, ['itemscope' => '', 'itemtype' => 'http://schema.org/ImageObject']);
}
