<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%category_discount}}".
 *
 * @property integer $id
 * @property integer $category_id
 * @property integer $discount_id
 */
class CategoryDiscount extends \yii\db\ActiveRecord
{
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
