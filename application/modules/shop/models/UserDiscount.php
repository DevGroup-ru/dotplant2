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
    /**
     * @inheritdoc
     */
    public function getFullName()
    {
        $user = $this->user;
        return null === $user
            ? $this->user_id
            : "[{$this->user_id}] {$this->user->first_name} {$this->user->last_name}";
    }

    /**
     * @return User|\yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null)
    {
        if (null === $order) {
            return false;
        }

        $q = self::find()->where(['discount_id' => $discount->id, 'user_id' => $order->user_id])->count();
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
