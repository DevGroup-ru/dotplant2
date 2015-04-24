<?php

namespace app\seo\models;

use yii\caching\TagDependency;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;

/**
 * This is the model class for table "seo_config".
 *
 * @property string $key
 * @property string $value
 */
class Config extends \yii\db\ActiveRecord
{
    static private $modelMap = [];

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

    public function beforeSave($insert)
    {

        TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag($this->className())
            ]
        );

        TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag($this->className()),
                'Config:'.$this->key
            ]
        );

        return parent::beforeSave($insert);
    }

    static public function getModelByKey($key = null)
    {
        if (empty($key)) {
            return null;
        }

        if (!isset(static::$modelMap[$key])) {
            $cacheKey = static::className() . ':' . $key;
            if (false === $cache = Yii::$app->cache->get($cacheKey)) {
                $cache = static::findOne(['key' => $key]);
                if (empty($cache)) {
                    return null;
                }

                Yii::$app->cache->set($cacheKey, $cache, 0,
                    new TagDependency(['tags' => [
                        \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                    ]])
                );
            }

            static::$modelMap[$key] = $cache;
        }

        return static::$modelMap[$key];
    }
}
