<?php

namespace app\components;

use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use Imagine\Image\ManipulatorInterface;

/**
 * Universal helper for common use cases
 * @package app\components
 */
class Helper
{
    private static $modelMaps = [];
    /**
     * Get all model records as array key => value.
     * @param string $className
     * @param string $keyAttribute
     * @param string $valueAttribute
     * @param bool $useCache
     * @return array
     */
    public static function getModelMap($className, $keyAttribute, $valueAttribute, $useCache = true)
    {
        /** @var ActiveRecord $className */
        $cacheKey = 'Map: ' . $className::tableName() . ':' . $keyAttribute . ':' . $valueAttribute;
        if (isset(Helper::$modelMaps[$cacheKey]) === false) {
            Helper::$modelMaps[$cacheKey] = $useCache ? Yii::$app->cache->get($cacheKey) : false;
            if (Helper::$modelMaps[$cacheKey] === false) {
                Helper::$modelMaps[$cacheKey] = ArrayHelper::map($className::find()->asArray()->all(), $keyAttribute, $valueAttribute);
                if ($useCache === true) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        Helper::$modelMaps[$cacheKey],
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag($className),
                                ],
                            ]
                        )
                    );
                }
            }
        }
        return Helper::$modelMaps[$cacheKey];
    }

    public static function createSlug($source)
    {
        $source = mb_strtolower($source);
        $translateArray = [
            "ый" => "y", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "c", "ч" => "ch" ,"ш" => "sh", "щ" => "sch", "ъ" => "",
            "ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
            " " => "-", "." => "", "/" => "-"
        ];
        $source = preg_replace('#[^a-z0-9\-]#is', '', strtr($source, $translateArray));
        return trim(preg_replace('#-{2,}#is', '-', $source));
    }

    /**
     * TrimPlain returns cleaned from tags part of text with given length
     * @param string $text input text
     * @param int $length length of text part
     * @param bool $dots adding dots to end of part
     * @return string
     */
    public static function trimPlain($text, $length = 150, $dots = '...')
    {
        if (!is_string($text) && empty($text)) {
            return "";
        }
        $length = intval($length);
        $text = trim(strip_tags($text));
        $pos = mb_strrpos(mb_substr($text, 0, $length), ' ');
        $string = mb_substr($text, 0, $pos);
        $string .= $dots;
        if (!empty($string)) {
            return $string;
        } else {
            return "";
        }
    }

    public static function thumbnailOnDemand($filename, $width, $height, $relativePart = '.', $inset = true)
    {
        $pos = mb_strrpos($filename, '/', null);
        if ($pos > 0) {
            $dir = mb_substr($filename, 0, $pos);
            $file = mb_substr($filename, $pos + 1, null);
        } else {
            $dir = '';
            $file = $filename;
        }
        $thumbFilename = $dir . '/thumb-' . $width . 'x' . $height . '-' . $file;
        if (file_exists($relativePart . $thumbFilename) === false) {
            try {
                $image = \yii\imagine\Image::thumbnail(
                    $relativePart . $filename,
                    $width,
                    $height,
                    $inset ? ManipulatorInterface::THUMBNAIL_INSET : ManipulatorInterface::THUMBNAIL_OUTBOUND
                );
                $image->save($relativePart . $thumbFilename, ['quality' => 90]);
            } catch (\Imagine\Exception\InvalidArgumentException $e) {
                // it seems that file not found in most cases - return original filename instead
                return $filename;
            }
        }
        return $thumbFilename;
    }
}
