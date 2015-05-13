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
use yii\helpers\Url;

foreach ($images as $image) {
    $image_src = Url::to(['/image/image/image', 'fileName' => $image->filename]);
    if ($thumbnailOnDemand === true) {
        $image_src = $image->getThumbnail("{$thumbnailWidth}x{$thumbnailHeight}", $useWatermark);
        if ($useWatermark === true) {
            $image_src = Url::to(['/image/image/thumbnail-watermark', 'fileName' => $image_src]);
        } else {
            $image_src = Url::to(['/image/image/thumbnail', 'fileName' => $image_src]);
        }
    }
    echo Html::tag(
        'div',
        Html::img($image_src, ['alt' => $image->image_description, 'itemprop' => "contentUrl"]),
        ['itemscope' => '', 'itemtype' => 'http://schema.org/ImageObject']
    );
}
