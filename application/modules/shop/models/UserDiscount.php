<?php

namespace app\modules\shop\models;

use app\modules\user\models\User;
use Yii;

/**
 * This is the model class for table "{{%user_discount}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $discount_id
 * @property User $user
 */
class UserDiscount extends AbstractDiscountType
{

    public function getFullName()
    {
        return $this->user_id .' '.$this->user->first_name .' '.$this->user->last_name;
    }


    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null)
    {
        $result = false;
        if (intval(self::find()->where(['discount_id'=>$discount->id])->count()) === 0)
        {
            $result = true;
        } elseif (
            $order !== null &&
            intval(self::find()->where(['discount_id'=>$discount->id, 'user_id'=>$order->user_id])->count()) === 1
        ) {
            $result = true;
        }
        return $result;
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
