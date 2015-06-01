<?php

namespace app\modules\seo\helpers;

use Yii;

class OpenGraphHelper
{

    public static function registerMeta($title = '', $url = '', $image = '', $description = '', $type = 'website')
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

    public static function registerMetaByObject($object)
    {
        $title = $object->title;
        $url = Yii::$app->request->getAbsoluteUrl();

        $imageObject = $object->getImages()->one();
        if ($imageObject !== null) {
            $image = $imageObject->getOriginalUrl();
        } else {
            $image = '';
        }
        $description = strip_tags($object->announce);
        self::registerMeta($title, $url, $image, $description);

    }


}