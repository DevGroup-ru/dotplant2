<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "shipping_discount".
 *
 * @property integer $id
 * @property integer $shipping_option_id
 * @property integer $discount_id
 * @property shippingOption $shippingOption
 */
class ShippingDiscount extends \app\modules\shop\models\AbstractDiscountType
{
    /**
     * getShippingOption
     * @return ShippingOption
     */
    public function getShippingOption()
    {
        return $this->hasOne(ShippingOption::className(), ['id' => 'shipping_option_id']);
    }

    /**
     * @inheritdoc
     */
    public function getFullName() {
        return $this->shippingOption->name;
    }

    /**
     * @inheritdoc
     */
    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null)
    {
        if (intval(self::find()->where(['discount_id' => $discount->id])->count()) === 0) {
            return true;
        }

        if (!is_null($order) && ($order->orderDeliveryInformation instanceof OrderDeliveryInformation)) {
            $model_count = self::find()->where(
                [
                    'discount_id' => $discount->id,
                    'shipping_option_id' => $order->orderDeliveryInformation->shipping_option_id
                ]
                )->count();

            if ($model_count > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shipping_discount';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shipping_option_id', 'discount_id'], 'required'],
            [['shipping_option_id', 'discount_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'shipping_option_id' => Yii::t('app', 'Shipping Option ID'),
            'discount_id' => Yii::t('app', 'Discount ID'),
        ];
    }
}
