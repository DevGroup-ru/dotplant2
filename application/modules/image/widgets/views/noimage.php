<?php
use yii\helpers\Html;

echo Html::tag(
    'div',
    Html::img(Yii::$app->getModule('image')->noImageSrc, ['itemprop' => "contentUrl", 'style'=>'max-width:100%']),
    ['itemscope' => '', 'itemtype' => 'http://schema.org/ImageObject']
);