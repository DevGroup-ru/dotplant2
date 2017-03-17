<?php

namespace app\modules\shop\models;

use app\behaviors\Tree;
use app\modules\shop\components\AbstractEntity;
use app\modules\shop\components\AddonOrderItemEntityFactory;
use app\modules\shop\components\CustomOrderItemEntityFactory;
use app\modules\shop\components\EntityFactory;
use app\modules\shop\components\ProductOrderItemEntityFactory;
use app\modules\shop\helpers\PriceHelper;
use app\properties\HasProperties;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%order_item}}".
 * Model fields:
 * @property integer $id
 * @property integer $parent_id
 * @property integer $order_id
 * @property integer $product_id
 * @property string $custom_name
 * @property integer $addon_id
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
 * @property Addon $addon
 * @property AbstractEntity $entity
 */
class OrderItem extends ActiveRecord
{
    /**
     * @inheritdoc
     */
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
            [['order_id', 'product_id', 'parent_id', 'addon_id'], 'integer'],
            [['lock_product_price'], 'boolean'],
            [['custom_name'], 'string',],
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
            'addon_id' => Yii::t('app', 'Addon ID'),
            'quantity' => Yii::t('app', 'Quantity'),
            'price_per_pcs' => Yii::t('app', 'Price per pcs'),
            'total_price_without_discount' => Yii::t('app', 'Total price without discount'),
            'lock_product_price' => Yii::t('app', 'Lock product price'),
            'discount_amount' => Yii::t('app', 'Discount amount'),
            'total_price' => Yii::t('app', 'Total price'),
            'custom_name' => Yii::t('app', 'Custom name'),
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
        $product = Yii::$container->get(Product::class);
        return $this->hasOne(get_class($product), ['id' => 'product_id']);
    }

    /**
     * @return Addon|null
     */
    public function getAddon()
    {
        return $this->hasOne(Addon::className(), ['id' => 'addon_id']);
    }

    /**
     * @return Order|null
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    /**
     * Returns instance of OrderItem entity - product or addon
     * Used for calculating prices, etc.
     * @return Product|Addon
     */
    public function sellingItem()
    {
        if ($this->addon_id !== 0) {
            return Addon::findById($this->addon_id);
        } else {
            $product = Yii::$container->get(Product::class);
            return $product::findById($this->product_id);
        }
    }

    /**
     * @return \app\modules\shop\components\AbstractEntity
     */
    public function getEntity()
    {
        return EntityFactory::getEntityByModel($this);
    }
}
