<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%order_delivery_information}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $shipping_option_id
 * @property double $shipping_price
 * @property double $shipping_price_total
 * @property string $planned_delivery_date
 * @property string $planned_delivery_time
 * @property string $planned_delivery_time_range
 * Relations:
 * @property Order $order
 * @property ShippingOption $shippingOption
 */
class OrderDeliveryInformation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_delivery_information}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'shipping_option_id'], 'required'],
            [['order_id', 'shipping_option_id'], 'integer'],
            [['planned_delivery_date', 'planned_delivery_time'], 'safe'],
            [['planned_delivery_time_range'], 'string', 'max' => 255],
            [['shipping_price', 'shipping_price_total'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'shipping_option_id' => Yii::t('app', 'Shipment Option ID'),
            'shipping_price' => Yii::t('app', 'Shipping price'),
            'shipping_price_total' => Yii::t('app', 'Shipping price total'),
            'planned_delivery_date' => Yii::t('app', 'Planned Delivery Date'),
            'planned_delivery_time' => Yii::t('app', 'Planned Delivery Time'),
            'planned_delivery_time_range' => Yii::t('app', 'Planned Delivery Time Range'),
        ];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getShippingOption()
    {
        return $this->hasOne(ShippingOption::className(), ['id' => 'shipping_option_id']);
    }

    public static function getByOrderId($id = null)
    {
        return static::findOne(['order_id' => $id]);
    }
}
?>