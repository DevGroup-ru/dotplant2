<?php

namespace app\modules\seo\helpers;

use app\modules\seo\models\OpenGraphObject;
use Yii;

class HtmlHelper
{

    public static function registerOpenGraph($title = '', $url = '', $image = '', $description = '', $type = 'website')
    {

        if (!empty($title)) {
            Yii::$app->view->registerMetaTag(
                [
                    'property' => 'og:title',
                    'content' => $title
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
                    'property' => 'og:url',
                    'content' => $description
                ]
            );
        }

    }

    public static function registerOpenGraphByModel($model)
    {
        OpenGraphObject::getDataByModel($model);
    }


}