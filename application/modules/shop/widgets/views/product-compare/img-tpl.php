<?php
/**
 * @var $images \app\modules\image\models\Image[]
 * @var $this \yii\web\View
 * @var $thumbnailOnDemand boolean
 * @var $thumbnailWidth integer
 * @var $thumbnailHeight integer
 * @var $useWatermark boolean
 * @var $additional array
 */

    $image = empty($images) ? null : array_pop($images);
    $image_src = empty($image)
        ? $additional['blank']
        : (true === $thumbnailOnDemand
            ? $image->getThumbnail("{$thumbnailWidth}x{$thumbnailHeight}", $useWatermark)
            : $image->file
        );
?>
<div class="img"><img src="<?= $image_src; ?>" class="img-rounded"></div>
