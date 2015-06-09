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

    public function getFullName()
    {
        return $this->product->name;
    }

    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null)
    {
        $orderAttributes = $order->abstractModel->getAttributes();
        if (intval(self::find()->where(['discount_id' => $discount->id])->count()) === 0) {
            $result = true;
        } else {
            $query = self::find()->where(['discount_id' => $discount->id]);

            if (isset($orderAttributes['promocode']) && $orderAttributes['promocode']) {
                $query->andWhere(
                    [
                        'code' => $orderAttributes['promocode']
                    ]
                );
            }

            $result = $query->count() == 1;
        }
        return $result;
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
