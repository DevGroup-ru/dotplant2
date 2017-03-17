<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%product_discount}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $discount_id
 * @property Product $product
 */
class ProductDiscount extends AbstractDiscountType
{
    /**
     * @inheritdoc
     */
    public function getFullName()
    {
        $product = $this->product;
        return null === $product ? '(none)' : $product->name;
    }

    /**
     * @return Product|\yii\db\ActiveQuery
     */
    public function getProduct()
    {
        $product = Yii::$container->get(Product::class);
        return $this->hasOne(get_class($product), ['id' => 'product_id']);
    }

    /**
     * @inheritdoc
     */
    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null)
    {
        if (null === $order || null === $product) {
            return false;
        }

        $q = self::find()->where(['discount_id' => $discount->id, 'product_id' => $product->id])->count();
        if (0 === intval($q)) {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_discount}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'discount_id'], 'required'],
            [['product_id', 'discount_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'product_id' => Yii::t('app', 'Product ID'),
            'discount_id' => Yii::t('app', 'Discount ID'),
        ];
    }
}
