<?php

namespace app\modules\shop\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "{{%category_discount}}".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $discount_id
 * @property Category $category
 */
class CategoryDiscount extends AbstractDiscountType
{
    /**
     * @inheritdoc
     */
    public function getFullName()
    {
        $category = $this->category;
        return null === $category ? '(none)' : $category->name;
    }

    /**
     * @return Category|\yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(),['id'=>'category_id']);
    }

    /**
     * @inheritdoc
     */
    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null)
    {
        if (null === $product) {
            return false;
        }

        $q = (new Query())
            ->from(self::tableName() . ' cd')
            ->leftJoin('{{%product_category%}} pc', 'pc.category_id = cd.category_id')
            ->where(['cd.discount_id' => $discount->id, 'pc.object_model_id' => $product->id])
            ->count();
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
        return '{{%category_discount}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'discount_id'], 'required'],
            [['category_id', 'discount_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_id' => Yii::t('app', 'Category ID'),
            'discount_id' => Yii::t('app', 'Discount ID'),
        ];
    }
}
