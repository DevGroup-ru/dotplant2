<?php

namespace app\modules\shop\models;

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
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Description'),
            'price_from' => Yii::t('app', 'Price From'),
            'price_to' => Yii::t('app', 'Price To'),
            'cost' => Yii::t('app', 'Cost'),
            'sort' => Yii::t('app', 'Sort'),
            'active' => Yii::t('app', 'Active'),
        ];
    }

    /**
     * @return ShippingOption|null
     */
    public static function findFirstActive()
    {
        return static::find()->where(['active' => 1])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->one();
    }
}
?>