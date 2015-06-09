<?php

namespace app\modules\shop\models;

use yii\data\ActiveDataProvider;

/**
 * Class AbstractDiscountType
 * @package app\modules\shop\models
 * @property DiscountType $type
 * @property string $fullName
 */
abstract class AbstractDiscountType extends \yii\db\ActiveRecord
{
    /**
     * @return string
     */
    abstract public function getFullName();

    /**
     * @param Discount $discount
     * @param Product $product
     * @param Order $order
     * @return boolean
     */
    abstract public function checkDiscount(Discount $discount, Product $product = null, Order $order = null);

    /**
     * @TODO: What this function for?
     * @return DiscountType|null
     */
    static public function getType()
    {
        return DiscountType::findOne(['class' => static::className()]);
    }

    /**
     * @param integer $discount_id
     * @return ActiveDataProvider
     */
    static public function searchDiscountFilter($discount_id)
    {
        return new ActiveDataProvider([
            'query' => static::find()->where(['discount_id' => $discount_id]),
        ]);
    }
}