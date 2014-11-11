<?php

namespace app\models;

use app\behaviors\TagDependency;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "shipping_option".
 *
 * @property integer $id
 * @property string $name
 * @property string $description
 * @property double $price_from
 * @property double $price_to
 * @property double $cost
 * @property integer $sort
 * @property integer $active
 */
class ShippingOption extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => TagDependency::className(),
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
            [['price_from', 'price_to', 'cost'], 'number'],
            [['sort', 'active'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255]
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['name', 'price_from', 'price_to', 'cost', 'sort', 'active', 'description'],
            'search' => ['id', 'name', 'price_from', 'price_to', 'cost', 'sort', 'active'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('shop', 'ID'),
            'name' => Yii::t('shop', 'Name'),
            'description' => Yii::t('shop', 'Description'),
            'price_from' => Yii::t('shop', 'Price From'),
            'price_to' => Yii::t('shop', 'Price To'),
            'cost' => Yii::t('shop', 'Cost'),
            'sort' => Yii::t('shop', 'Sort'),
            'active' => Yii::t('shop', 'Active'),
        ];
    }
}
