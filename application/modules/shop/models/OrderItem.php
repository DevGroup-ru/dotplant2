<?php

namespace app\modules\shop\models;

use app\behaviors\Tree;
use app\modules\shop\helpers\PriceHelper;
use app\properties\HasProperties;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
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
 * @property Product|HasProperties $product
 */
class OrderItem extends ActiveRecord
{

    public function behaviors()
    {
        return [
            [
                'class' => Tree::className(),
                'cascadeDeleting' => true,
                'activeAttribute' => false,
            ],
            [
                'class' => ActiveRecordHelper::className(),
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

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        $this->total_price = PriceHelper::getProductPrice(
            $this->product,
            $this->order,
            $this->quantity
        );
        $this->discount_amount = ($this->quantity * $this->price_per_pcs) - $this->total_price;
        $this->discount_amount = $this->discount_amount < 0 ? 0 : round($this->discount_amount, 2);
        $this->total_price_without_discount = $this->total_price + $this->discount_amount;
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        SpecialPriceObject::deleteAllByObject($this);
        if (!static::find()->where(['order_id' => $this->order_id])->one()) {
            Order::deleteOrderElements($this->order);
        }
        $this->order->calculate(true, true);
        parent::afterDelete();
    }

    /**
     * @return Product|null
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return Order|null
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }
}
