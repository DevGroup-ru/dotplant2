<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "discount_type".
 *
 * @property integer $id
 * @property string $name
 * @property string $class
 * @property integer $active
 * @property string $checking_class
 * @property integer $sort_order
 * @property string $add_view
 */
class DiscountType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%discount_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'class', 'checking_class'], 'required'],
            [['active', 'sort_order'], 'integer'],
            [['checking_class'], 'string'],
            [['name', 'class'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'class' => Yii::t('app', 'Class'),
            'active' => Yii::t('app', 'Active'),
            'checking_class' => Yii::t('app', 'Checking Class'),
            'sort_order' => Yii::t('app', 'Sort Order'),
        ];
    }
}
