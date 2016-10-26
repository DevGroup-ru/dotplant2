<?php

namespace app\components;

use Yii;
use yii\base\Exception;
use yii\base\Model;
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
     * @param bool $useIntl
     * @return array
     */
    public static function getModelMap($className, $keyAttribute, $valueAttribute, $useCache = true, $useIntl = false)
    {
        /** @var ActiveRecord $className */
        $cacheKey = 'Map: ' . $className::tableName() . ':' . implode(':', [
            $keyAttribute,
            $valueAttribute,
            intval($useIntl)
        ]) ;
        if (isset(Helper::$modelMaps[$cacheKey]) === false) {
            Helper::$modelMaps[$cacheKey] = $useCache ? Yii::$app->cache->get($cacheKey) : false;
            if (Helper::$modelMaps[$cacheKey] === false) {
                Helper::$modelMaps[$cacheKey] = ArrayHelper::map($className::find()->asArray()->all(), $keyAttribute, $valueAttribute);
                if (true === $useIntl) {
                    array_walk(Helper::$modelMaps[$cacheKey],
                        function (&$value, $key)
                        {
                            $value = Yii::t('app', $value);
                        });
                }
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
            " " => "-", "." => "", "/" => "-", "_" => "-"
        ];
        $source = preg_replace('#[^a-z0-9\-]#is', '', strtr($source, $translateArray));
        return trim(preg_replace('#-{2,}#is', '-', $source));
    }

    /**
     * TrimPlain returns cleaned from tags part of text with given length
     * @param string $text input text
     * @param int $length length of text part
     * @param string $dots adding dots to end of part
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
        if (!empty($string)) {
            return $string.$dots;
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

    /**
     * @param Model $model
     * @param string $glue
     * @return string
     */
    public static function formatModelErrors(Model $model, $glue = PHP_EOL)
    {
        return implode($glue,
            array_map(
                function($item) {
                    return is_array($item) ? array_pop($item) : $item;
                },
                $model->getErrors()
            )
        );
    }

    /**
     * used with backend/widgets/DataRelationsWidget
     * @param ActiveRecord $model
     * @param $relationData
     * @return array
     */
    public static function getRelationDataByModel(ActiveRecord $model, $relationData)
    {
        $result = [];

        foreach ($relationData as $name => $data) {
            if ($data['type'] == 'field') {
                if (isset($model->attributes[$data['key']])) {
                    $result[$name] = [
                        'value' => $model->{$data['key']},
                        'label' => $model->getAttributeLabel($data['key'])
                    ];
                }
            }elseif ($data['type'] == 'property') {
                try {
                    $result[$name] = [
                        'value' => $model->AbstractModel->{$data['key']},
                        'label' => $model->AbstractModel->getAttributeLabel($data['key'])
                    ];
                } catch (Exception $e) {
                    Yii::warning(
                        'relation data not found: class: '.
                        $model::className().'; relation Type'.
                        $data['type'].'; id '.$model->id
                    );
                }
            } elseif ($data['type'] == 'relation') {
                try {
                    $result[$name] = [
                        'value' => $model->{$data['relationName']}->{$data['key']},
                        'label' => $model->{$data['relationName']}->getAttributeLabel($data['key'])
                    ];
                } catch (Exception $e) {
                    Yii::warning(
                        'relation data not found: class: '.
                        $model::className().'; relation Type'.
                        $data['type'].'; id '.$model->id
                    );
                }
            }
        }

        return $result;
    }
}
