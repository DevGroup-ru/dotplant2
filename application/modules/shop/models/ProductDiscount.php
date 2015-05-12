<?php

namespace app\modules\shop\models;

use app\modules\shop\components\DiscountInterface;
use Yii;

/**
 * This is the model class for table "{{%product_discount}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property integer $discount_id
 */
class ProductDiscount extends \yii\db\ActiveRecord
{

    public function checkProduct(DiscountInterface $object)
    {

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
