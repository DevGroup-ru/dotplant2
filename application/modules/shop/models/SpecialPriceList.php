<?php

namespace app\modules\shop\models;

use Yii;

/**
 * This is the model class for table "special_price_list".
 *
 * @property integer $id
 * @property integer $object_id
 * @property string $class
 * @property integer $active
 * @property string $type
 * @property integer $sort_order
 * @property string $params
 */
class SpecialPriceList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'special_price_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'class', 'type'], 'required'],
            [['object_id', 'active', 'sort_order'], 'integer'],
            [['type', 'params'], 'string'],
            [['class'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'class' => Yii::t('app', 'Class'),
            'active' => Yii::t('app', 'Active'),
            'type' => Yii::t('app', 'Type'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'params' => Yii::t('app', 'Params'),
        ];
    }
}
