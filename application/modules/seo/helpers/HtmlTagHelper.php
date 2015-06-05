<?php

namespace app\modules\seo\helpers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class HtmlTagHelper
{

    public static $htmlOptions = [];


    /**
     * @param string $tag
     * @param string $attributeName
     * @param string $attributeValue
     */
    public static function addTagOptions($tag, $attributeName, $attributeValue)
    {
        if (!isset(self::$htmlOptions[$tag])) {
            self::$htmlOptions[$tag] = [];
        }
        self::$htmlOptions[$tag] = ArrayHelper::merge(self::$htmlOptions[$tag], [$attributeName => $attributeValue]);
    }


    /**
     * @param string $tag
     * @return string
     */
    public static function registerTagOptions($tag)
    {
        $result = '';
        if (isset(self::$htmlOptions[$tag]))
        {
            $result .= ' ';
            $resultStringArray = [];
            foreach (self::$htmlOptions[$tag] as $name => $value) {
                $resultStringArray[] = $name.'="'.$value.'"';
            }
            $result .= implode(' ', $resultStringArray);
        }
        return $result;

    }

    /**
     * @param string $title
     * @param string $url
     * @param string $image
     * @param string $description
     * @param string $type
     */
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

        static::addTagOptions('html', 'prefix', 'og: http://ogp.me/ns#');
    }


}