<?php

namespace app\modules\seo\models;

use yii\caching\TagDependency;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;

/**
 * This is the model class for table "{{%seo_config}}".
 *
 * @property string $key
 * @property string $value
 */
class Config extends \yii\db\ActiveRecord
{
    /**
     * @property array $modelMap
     */
    static protected $modelMap = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%seo_config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'value'], 'required'],
            [['value'], 'string'],
            [['key'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'key' => 'Key',
            'value' => 'Value',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @param string|null $key
     * @return Config|null
     */
    public static function getModelByKey($key = null, $cacheTime = 0)
    {
        if (empty($key)) {
            return null;
        }
        if (!isset(static::$modelMap[$key])) {
            $cacheKey = static::tableName() . ':' . $key;
            if (false === $cache = Yii::$app->cache->get($cacheKey)) {
                $cache = static::findOne(['key' => $key]);
                if (empty($cache)) {
                    return null;
                }
                Yii::$app->cache->set(
                    $cacheKey,
                    $cache,
                    intval($cacheTime),
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(static::className()),
                            ]
                        ]
                    )
                );
            }
            static::$modelMap[$key] = $cache;
        }
        return static::$modelMap[$key];
    }

    /**
     * @param $key
     * @return bool
     */
    public static function removeCacheByKey($key = null)
    {
        return empty($key) ? false : Yii::$app->cache->delete(static::tableName() . ':' . $key);
    }
}
