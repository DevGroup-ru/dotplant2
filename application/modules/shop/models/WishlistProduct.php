<?php

namespace app\modules\shop\models;

use yii\db\ActiveRecord;
use Yii;
use app\modules\shop\models\Product;

/**
 * This is the model class for table "{{%wishlist_product}}".
 * Model fields:
 * @property integer $id
 * @property integer $wishlist_id
 * @property integer $product_id
 * Relations:
 * @property Product $product
 */
class WishlistProduct extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%wishlist_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'wishlist_id',
                    'product_id',
                ],
                'required'
            ],
            [
                [
                    'wishlist_id',
                    'product_id',
                ],
                'integer'
            ],
        ];
    }

    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
}
