<?php

namespace app\modules\shop\models;

use app\behaviors\Tree;
use app\modules\shop\components\DiscountProductInterface;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%order_item}}".
 *
 * Model fields:
 * @property integer $id
 * @property integer $parent_id
 * @property integer $order_id
 * @property integer $product_id
 * @property double $quantity
 * @property double $price_per_pcs
 * @property double $total_price_without_discount
 * @property bool $lock_product_price
 * @property double $discount_amount
 * @property double $total_price
 * Relations:
 * @property OrderItem[] $children
 * @property Order $order
 * @property OrderItem $parent
 * @property Product $product
 */
class OrderItem extends ActiveRecord implements DiscountProductInterface
{

    public function behaviors()
    {
        return [
            [
                'class' => Tree::className(),
                'cascadeDeleting' => true,
            ],
        ];
    }

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
            [
                ['quantity', 'price_per_pcs', 'total_price_without_discount', 'discount_amount', 'total_price'],
                'number',
                'min' => 0,
            ],
            [['order_id', 'product_id', 'parent_id'], 'integer'],
            [['lock_product_price'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'order_id' => Yii::t('app', 'Order ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'price_per_pcs' => Yii::t('app', 'Price per pcs'),
            'total_price_without_discount' => Yii::t('app', 'Total price without discount'),
            'lock_product_price' => Yii::t('app', 'Lock product price'),
            'discount_amount' => Yii::t('app', 'Discount amount'),
            'total_price' => Yii::t('app', 'Total price'),
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
