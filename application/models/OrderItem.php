<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "order_item".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $product_id
 * @property double $quantity
 * @property Order $order
 * @property Product $product
 * @property string $additional_options
 */
class OrderItem extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'product_id', 'quantity'], 'required'],
            [['quantity'], 'number', 'min' => 0],
            [['order_id', 'product_id'], 'integer'],
            [['additional_options'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('shop', 'ID'),
            'order_id' => Yii::t('shop', 'Order ID'),
            'product_id' => Yii::t('shop', 'Product ID'),
            'quantity' => Yii::t('shop', 'Quantity'),
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
}
