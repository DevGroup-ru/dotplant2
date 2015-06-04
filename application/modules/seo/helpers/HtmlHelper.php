<?php

namespace app\modules\seo\helpers;

use Yii;
use yii\helpers\Html;

class HtmlHelper
{

    public static $htmlOptions = [];

    public static function registerHtmlOptions()
    {
        return implode(' ', static::$htmlOptions);
    }

    public static function registerOpenGraph($title = '', $url = '', $image = '', $description = '', $type = 'website')
    {
        if (!empty($title)) {
            Yii::$app->view->registerMetaTag(
                [
                    'property' => 'og:title',
                    'content' => Html::encode($title)
                ]
            );
        }
        if (!empty($type)) {
            Yii::$app->view->registerMetaTag(
                [
                    'property' => 'og:type',
                    'content' => $type
                ]
            );
        }
        if (!empty($url)) {
            Yii::$app->view->registerMetaTag(
                [
                    'property' => 'og:url',
                    'content' => $url
                ]
            );
        }
        if (!empty($image)) {
            Yii::$app->view->registerMetaTag(
                [
                    'property' => 'og:image',
                    'content' => $image
                ]
            );
        }
        if (!empty($description)) {
            Yii::$app->view->registerMetaTag(
                [
                    'property' => 'og:description',
                    'content' => Html::encode($description)
                ]
            );
        }

        static::$htmlOptions[] = 'prefix="og: http://ogp.me/ns#"';
    }


}