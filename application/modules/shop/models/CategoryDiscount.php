<?php

namespace app\modules\shop\models;

use Yii;

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

    public function getFullName()
    {
        return $this->category->name;
    }

    public function getCategory()
    {
        return $this->hasOne(Category::className(),['id'=>'category_id']);
    }


    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null)
    {
        $result = false;
        if (intval(self::find()->where(['discount_id'=>$discount->id])->count()) === 0) {
            $result = true;
        } else {
           if( self::find()
                ->leftJoin(
                    '{{%product_category%}}',
                    '{{%product_category%}}.category_id = '.self::tableName().'.category_id'
                )
                ->where(
                    [
                        self::tableName().'.discount_id'=>$discount->id,
                        '{{%product_category%}}.object_model_id' => $product->id
                    ]
                )
                ->count() > 0) {
               $result = true;
           }
        }
        return $result;
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
