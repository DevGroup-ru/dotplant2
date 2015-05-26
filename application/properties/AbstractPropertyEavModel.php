<?php

namespace app\properties;

use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property integer $object_model_id
 * @property integer $property_group_id
 * @property string $key
 * @property string $value
 */
class AbstractPropertyEavModel extends ActiveRecord
{
    static private $tableName = null;

    public static function tableName()
    {
        return '{{%'.static::$tableName.'}}';
    }

    public static function setTableName($tableName)
    {
        static::$tableName = $tableName;
    }

    public function behaviors()
    {
        return [
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => 'sort_order',
                ],
                'value' => 0,
            ],
        ];
    }

    public function rules()
    {
        return [
            [['object_model_id', 'property_group_id', 'key'], 'required'],
            [['object_model_id', 'property_group_id'], 'integer'],
            [['key'], 'string', 'max' => 255],
            [['value'], 'string'],
            [['value'], 'default', 'value' => ''],
        ];
    }

    public static function findByModelId($model_id = null, $key = null, $property_group = null)
    {
        if (null === $model_id) {
            return [];
        }

        $query = static::find()->where(['object_model_id' => $model_id]);
        if (null !== $key) {
            $query->andWhere(['key' => $key]);
        }
        if (null !== $property_group) {
            $query->andWhere(['property_group_id' => $property_group]);
        }

        return $query->all();
    }
}
?>