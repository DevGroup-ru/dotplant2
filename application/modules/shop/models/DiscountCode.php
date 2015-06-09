<?php

namespace app\modules\shop\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%discount_code}}".
 *
 * @property integer $id
 * @property string $code
 * @property integer $discount_id
 * @property string $valid_from
 * @property string $valid_till
 * @property integer $maximum_uses
 */
class DiscountCode extends AbstractDiscountType
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%discount_code}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'discount_id'], 'required'],
            [['code'], 'unique'],
            [['discount_id', 'maximum_uses'], 'integer'],
            [['valid_from', 'valid_till'], 'safe'],
            [['code'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'code' => Yii::t('app', 'Code'),
            'discount_id' => Yii::t('app', 'Discount ID'),
            'valid_from' => Yii::t('app', 'Valid From'),
            'valid_till' => Yii::t('app', 'Valid Till'),
            'maximum_uses' => Yii::t('app', 'Maximum Uses'),
        ];
    }

    public function getFullName()
    {
        return $this->code . ' from: ' . $this->valid_from . ' till ' . $this->valid_till . ' ' . $this->maximum_uses;
    }

    public function checkDiscount(Discount $discount, Product $product = null, Order $order = null)
    {
        $result = false;
        if (intval(self::find()->where(['discount_id' => $discount->id])->count()) === 0) {
            $result = true;
        } else {
            $discountsCode = self::find()->where(['discount_id' => $discount->id])->all();

            $result = OrderCode::find()->where(
                [
                    'order_id' => $order->id,
                    'discount_code_id' => ArrayHelper::map($discountsCode, 'id', 'id'),
                    'status' => 1
                ]
            )->count() == 1;

        }
        return $result;
    }

    public function beforeDelete()
    {
        OrderCode::updateAll(
            [
                'status' => 0
            ],
            [
                'discount_code_id' => $this->id
            ]
        );

        return parent::beforeDelete();
    }
}
