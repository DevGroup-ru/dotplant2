<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "object_static_values".
 *
 * @property integer $id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property integer $property_static_value_id
 */
class ObjectStaticValues extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%object_static_values}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'object_model_id', 'property_static_value_id'], 'required'],
            [['object_id', 'object_model_id', 'property_static_value_id'], 'integer']
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
            'object_model_id' => Yii::t('app', 'Object Model ID'),
            'property_static_value_id' => Yii::t('app', 'Property Static Value ID'),
        ];
    }
}
