<?php

namespace app\modules\shop\models;

use app\modules\shop\components\DiscountInterface;
use Yii;

/**
 * This is the model class for table "{{%discount}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $appliance
 * @property double $value
 * @property integer $value_in_percent
 * @property double $apply_order_price_lg
 */
class Discount extends \yii\db\ActiveRecord implements DiscountInterface
{

    public $user;
    public $order;

    protected  $filters = [

    ];


    public function getFilters()
    {
        $filters =  $this->filters;
        $result = [];


        return $result;
    }


    public function getOrder()
    {
        return $this->order;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getOrderDiscount()
    {
        $result = 0;

        return $result;
    }

    public function getProductDiscount($id_product)
    {
        $result = 0;

        return $result;
    }





    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%discount}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'appliance', 'value'], 'required'],
            [['appliance'], 'string'],
            [['value', 'apply_order_price_lg'], 'number'],
            [['value_in_percent'], 'integer'],
            [['name'], 'string', 'max' => 255]
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
            'appliance' => Yii::t('app', 'Appliance'),
            'value' => Yii::t('app', 'Value'),
            'value_in_percent' => Yii::t('app', 'Value In Percent'),
            'apply_order_price_lg' => Yii::t('app', 'Apply Order Price Lg'),
        ];
    }
}
