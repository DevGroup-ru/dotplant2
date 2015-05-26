<?php

namespace app\modules\shop\models;


use app\modules\shop\models\Discount;
use app\modules\shop\models\Order;
use app\modules\shop\models\Product;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * Class AbstractDiscountType
 * @package app\modules\shop\models
 * @property DiscountType $type
 * @property string $fullName
 */
abstract class AbstractDiscountType extends \yii\db\ActiveRecord
{

    static public function getType()
    {
        return DiscountType::find()->where(['class'=>self::className()])->one();
    }

    static public function searchDiscountFilter($discount_id)
    {
        return   new ActiveDataProvider([
            'query' => self::find()->where(['discount_id'=>$discount_id]),
        ]);
    }


    abstract public function getFullName();


   abstract public function checkDiscount(Discount $discount, Product $product = null, Order $order = null);
}