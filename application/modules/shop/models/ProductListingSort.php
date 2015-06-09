<?php

namespace app\modules\shop\models;

use Yii;
use yii\caching\TagDependency;

/**
 * This is the model class for table "{{%product_listing_sort}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $sort_field
 * @property string $asc_desc
 * @property integer $enabled
 * @property integer $sort_order
 */
class ProductListingSort extends \yii\db\ActiveRecord
{
    public static $_cachedRows = null;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_listing_sort}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'sort_field'], 'required'],
            [['asc_desc'], 'string'],
            [['enabled', 'sort_order'], 'integer'],
            ['asc_desc', 'default', 'value'=>'asc',],
            [['name', 'sort_field'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'sort_field' => Yii::t('app', 'Sort Field'),
            'asc_desc' => Yii::t('app', 'Asc Desc'),
            'enabled' => Yii::t('app', 'Enabled'),
            'sort_order' => Yii::t('app', 'Sort Order'),
        ];
    }

    /**
     * Returns all enabled sorts as array of rows
     * Caches through cache and static variable
     * @return array
     */
    public static function enabledSorts()
    {
        if (static::$_cachedRows === null) {
            $cacheKey = "ProductListingSorts:all:arrayOfRows";
            static::$_cachedRows = Yii::$app->cache->get($cacheKey);
            if (!is_array(static::$_cachedRows)) {
                static::$_cachedRows = ProductListingSort::find()
                    ->where(['enabled'=>1])
                    ->orderBy('sort_order ASC')
                    ->indexBy('id')
                    ->asArray()
                    ->all();
                Yii::$app->cache->set(
                    $cacheKey,
                    static::$_cachedRows,
                    86400,
                    new TagDependency([
                        'tags' => [
                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                        ]
                    ])
                );
            }
        }
        return static::$_cachedRows;
    }
}
