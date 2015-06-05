<?php

namespace app\properties;

use app\models\Object;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
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
    /**
     * @var string|null Table name
     */
    static private $tableName = null;
    static private $objectClassMap = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%'.static::$tableName.'}}';
    }

    /**
     * @param string $tableName
     */
    public static function setTableName($tableName)
    {
        static::$tableName = $tableName;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @param integer|null $model_id
     * @param string|null $key
     * @param integer|null $property_group
     * @return \yii\db\ActiveRecord[]|array
     */
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

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $this->invalidateObjectCache();
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $this->invalidateObjectCache();
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Invalidate cache for object
     */
    private function invalidateObjectCache()
    {
        $className = null;
        if (!isset(static::$objectClassMap[static::$tableName])) {
            /** @var Object $object */
            if (null !== $object = Object::findOne(['eav_table_name' => static::$tableName])) {
                static::$objectClassMap[static::$tableName] = $object->object_class;
                $className = static::$objectClassMap[static::$tableName];
            }
        } else {
            $className = static::$objectClassMap[static::$tableName];
        }

        if (null !== $className) {
            \yii\caching\TagDependency::invalidate(
                \Yii::$app->cache,
                [
                    ActiveRecordHelper::getObjectTag($className, $this->object_model_id)
                ]
            );
        }
    }
}
