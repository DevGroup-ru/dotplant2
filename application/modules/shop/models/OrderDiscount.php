<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%order_discount}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $discount_id
 * @property string $applied_date
 */
class OrderDiscount extends AbstractDiscountType
{
    /**
     * @inheritdoc
     */
    public function getFullName()
    {
        return $this->order_id;
    }

    /**
     * @inheritdoc
     */
    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null)
    {
        if (null === $order) {
            return false;
        }

        $q = self::find()->where(['discount_id' => $discount->id, 'order_id' => $order->id])->count();
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
        return '{{%order_discount}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'discount_id'], 'required'],
            [['order_id', 'discount_id'], 'integer'],
            [['applied_date'], 'safe']
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
            'discount_id' => Yii::t('app', 'Discount ID'),
            'applied_date' => Yii::t('app', 'Applied Date'),
        ];
    }
}
