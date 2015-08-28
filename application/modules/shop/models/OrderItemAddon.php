<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%order_item_addon}}".
 *
 * @property integer $id
 * @property integer $addon_id
 * @property integer $order_item_id
 * @property double $quantity
 * @property double $price_per_pcs
 * @property double $total_price
 */
class OrderItemAddon extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_item_addon}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['addon_id', 'order_item_id', 'price_per_pcs', 'total_price'], 'required'],
            [['addon_id', 'order_item_id'], 'integer'],
            [['quantity', 'price_per_pcs', 'total_price'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'addon_id' => Yii::t('app', 'Addon ID'),
            'order_item_id' => Yii::t('app', 'Order Item ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'price_per_pcs' => Yii::t('app', 'Price Per Pcs'),
            'total_price' => Yii::t('app', 'Total Price'),
        ];
    }
}
