<?php
use yii\helpers\Html;

echo Html::tag(
    'div',
    Html::img(Yii::$app->getModule('image')->noImageSrc, ['itemprop' => "contentUrl"]),
    ['itemscope' => '', 'itemtype' => 'http://schema.org/ImageObject']
);