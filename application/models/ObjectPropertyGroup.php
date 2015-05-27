<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "object_property_group".
 *
 * @property integer $id
 * @property integer $object_id
 * @property integer $object_model_id
 * @property integer $property_group_id
 */
class ObjectPropertyGroup extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%object_property_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'object_model_id', 'property_group_id'], 'required'],
            [['object_id', 'object_model_id', 'property_group_id'], 'integer'],
            [['object_id', 'object_model_id', 'property_group_id'], 'unique', 'targetAttribute' => ['object_id', 'object_model_id', 'property_group_id'], 'message' => 'Property group is already binded'],
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
            'property_group_id' => Yii::t('app', 'Property Group ID'),
        ];
    }

    public static function getForModel($model)
    {
        /** @var Object $object */
        $object = Object::getForClass(get_class($model));
        return ObjectPropertyGroup::find()
            ->joinWith('group')
            ->where(
                [
                    static::tableName() . '.object_id' => $object->id,
                    static::tableName() . '.object_model_id' => $model->id,
                ]
            )->orderBy(PropertyGroup::tableName() . '.sort_order')
            ->all();
    }

    /**
     * @return PropertyGroup|null
     */
    public function getGroup()
    {
        return $this->hasOne(PropertyGroup::className(), ['id' => 'property_group_id']);
    }
}
?>