<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "{{%discount_code}}".
 *
 * @property integer $id
 * @property string $code
 * @property integer $discount_id
 * @property string $valid_from
 * @property string $valid_till
 * @property integer $maximum_uses
 * @property integer $used
 */
class DiscountCode extends \yii\db\ActiveRecord
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
            [['discount_id', 'maximum_uses', 'used'], 'integer'],
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
            'used' => Yii::t('app', 'Used'),
        ];
    }
}
