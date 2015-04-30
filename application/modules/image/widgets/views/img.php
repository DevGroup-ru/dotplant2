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

foreach ($images as $image) {
    $image_src = $image->src;
    if ($thumbnailOnDemand === true) {
        $image_src = $image->getThumbnail("{$thumbnailWidth}x{$thumbnailHeight}", $useWatermark);
    }
    echo Html::tag(
        'div',
        Html::img($image_src, ['alt' => $image->image_description, 'itemprop' => "contentUrl"]),
        ['itemscope' => '', 'itemtype' => 'http://schema.org/ImageObject']
    );
}
