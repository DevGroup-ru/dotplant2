<?php

namespace app\modules\shop\models;

use app\modules\shop\components\AbstractShippingHandler;
use app\modules\shop\components\ShippingHandlerHelper;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "shipping_option".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property double $price_from
 * @property double $price_to
 * @property integer $sort
 * @property integer $active
 * @property string $handler_class
 * @property string $handler_params
 */
class ShippingOption extends ActiveRecord
{
    protected $handler;

    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shipping_option}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'required'],
            [['price_from', 'price_to'], 'number'],
            [['sort', 'active'], 'integer'],
            [['name', 'description', 'handler_class'], 'string', 'max' => 255],
            [['handler_params'], 'string', 'max' => 65535],
        ];
    }

    public function scenarios()
    {
        return [
            'default' => [
                'name',
                'price_from',
                'price_to',
                'sort',
                'active',
                'description',
                'handler_class',
                'handler_params'
            ],
            'search' => ['id', 'name', 'price_from', 'price_to', 'cost', 'sort', 'active'],
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
            'description' => Yii::t('app', 'Description'),
            'price_from' => Yii::t('app', 'Price From'),
            'price_to' => Yii::t('app', 'Price To'),
            'sort' => Yii::t('app', 'Sort'),
            'active' => Yii::t('app', 'Active'),
            'handler_class' => Yii::t('app', 'Handler class'),
            'handler_params' => Yii::t('app', 'Handler params'),
        ];
    }

    /**
     * @return ShippingOption|null
     */
    public static function findFirstActive()
    {
        return static::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->one();
    }

    /**
     * Get shipping option handler.
     * @return AbstractShippingHandler
     */
    public function getHandler()
    {
        if ($this->handler === null) {
            $this->handler = ShippingHandlerHelper::createHandlerByClass(
                $this->handler_class,
                Json::decode($this->handler_params)
            );
        }
        return $this->handler;
    }
}
