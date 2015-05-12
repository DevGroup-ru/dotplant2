<?php

namespace app\modules\shop\models;

use app\modules\shop\components\DiscountProductInterface;
use app\modules\shop\components\FilterInterface;
use Yii;

/**
 * This is the model class for table "{{%user_discount}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $discount_id
 */
class UserDiscount extends \yii\db\ActiveRecord implements FilterInterface
{
    public function verifi(DiscountProductInterface $object)
    {

    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_discount}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'discount_id'], 'required'],
            [['user_id', 'discount_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'discount_id' => Yii::t('app', 'Discount ID'),
        ];
    }
}
