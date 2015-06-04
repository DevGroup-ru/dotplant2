<?php

namespace app\modules\shop\models;

use Yii;
use yii\caching\TagDependency;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;

/**
 * This is the model class for table "special_price_list".
 *
 * @property integer $id
 * @property integer $object_id
 * @property string $class
 * @property integer $active
 * @property string $type
 * @property integer $sort_order
 * @property string $params
 * @property string $handler
 */
class SpecialPriceList extends \yii\db\ActiveRecord
{
    const TYPE_CORE = 'core';
    const TYPE_DISCOUNT = 'discount';
    const TYPE_DELIVERY = 'delivery';
    const TYPE_TAX = 'tax';
    const TYPE_PROJECT = 'project';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%special_price_list}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'class', 'type_id', 'handler'], 'required'],
            [['object_id', 'active', 'sort_order', 'type_id'], 'integer'],
            [['params'], 'string'],
            [['class'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'class' => Yii::t('app', 'Class'),
            'active' => Yii::t('app', 'Active'),
            'type_id' => Yii::t('app', 'Type'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'params' => Yii::t('app', 'Params'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @param string $type
     * @return SpecialPriceList[]|array
     */
    public static function getModelsByKey($type)
    {
        $cacheKey = static::className() .'_'. $type;

        if (false === $results = Yii::$app->cache->get($cacheKey)) {
            $results = self::find()
                ->where(
                    [
                        'type' =>$type
                    ]
                )->all();

            Yii::$app->cache->set(
                $cacheKey,
                $results,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(static::className())
                        ]
                    ]
                )
            );
        }
        return  $results;
    }
}
