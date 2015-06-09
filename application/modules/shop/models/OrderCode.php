<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%order_code}}".
 *
 * @property integer $id
 * @property integer $order_id
 * @property integer $discount_code_id
 * @property integer $status
 * @property DiscountCode $discountCode
 * @property Order $order
 */
class OrderCode extends \yii\db\ActiveRecord
{
    public $code;


    public function getDiscountCode()
    {
        return $this->hasOne(DiscountCode::className(), ['id' => 'discount_code_id']);
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }


    public function validateCode($attribute, $config)
    {

        $discountCode = DiscountCode::find()->where(['code' => $this->code])->one();
        /** @var  DiscountCode $discountCode */

        if ($discountCode == null) {
            $this->addError($attribute, Yii::t('app', 'Code not found'));
        } else {
            $this->discount_code_id = $discountCode->id;

            $nowTime = new \DateTime();
            if ($discountCode->valid_from && new \DateTime($discountCode->valid_from) > $nowTime) {
                $this->addError($attribute, Yii::t('app', 'Code not valid on this time'));
            }

            if ($discountCode->valid_till && new \DateTime($discountCode->valid_till) < $nowTime) {
                $this->addError($attribute, Yii::t('app', 'Code not valid on this time'));
            }
            if ($discountCode->maximum_uses &&
                $discountCode->maximum_uses >= self::find()
                    ->where(
                        [
                            'discount_code_id' => $this->discount_code_id,
                            'status' => 1
                        ]
                    )
                    ->count()
            ) {
                $this->addError($attribute, Yii::t('app', 'limit discounts ended'));
            }

            if ($this->errors === []) {
                $this->status = 1;
            }
        }


    }

    public function afterSave($insert, $changedAttributes)
    {

        foreach ($this->order->items as $item) {
            $item->save();
        }
        return parent::afterSave($insert, $changedAttributes);
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_code}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                ['code'],
                'validateCode'
            ],
            [['order_id', 'code'], 'required'],
            [['order_id', 'discount_code_id', 'status'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code' => Yii::t('app', 'Promo code'),
            'order_id' => Yii::t('app', 'Order ID'),
            'discount_code_id' => Yii::t('app', 'Discount Code ID'),
            'status' => Yii::t('app', 'Status'),
        ];
    }
}
