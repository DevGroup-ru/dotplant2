<?php

namespace app\models;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "property_group".
 *
 * @property integer $id
 * @property integer $object_id
 * @property string $name
 * @property integer $sort_order
 * @property integer $is_internal
 * @property integer $hidden_group_title
 */
class PropertyGroup extends ActiveRecord
{
    private static $identity_map = [];
    private static $groups_by_object_id = [];

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'sort_order',
                ],
                'value' => 0,
            ],
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%property_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id', 'name'], 'required'],
            [['object_id', 'sort_order', 'is_internal', 'hidden_group_title'], 'integer'],
            [['name'], 'string']
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
            'name' => Yii::t('app', 'Name'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'is_internal' => Yii::t('app', 'Is Internal'),
            'hidden_group_title' => Yii::t('app', 'Hidden Group Title'),
        ];
    }

    /**
     * Relation to \app\models\Object
     * @return \yii\db\ActiveQuery
     */
    public function getObject()
    {
        return $this->hasOne(Object::className(), ['id' => 'object_id']);
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['object_id' => $this->object_id]);
        $query->andFilterWhere(['is_internal' => $this->is_internal]);
        $query->andFilterWhere(['hidden_group_title' => $this->hidden_group_title]);
        return $dataProvider;
    }

    /**
     * Возвращает модель по ID с использованием IdentityMap
     *
     * @param int $id
     * @return null|PropertyGroup
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            $cacheKey = "PropertyGroup:$id";
            if (false === $group = Yii::$app->cache->get($cacheKey)) {
                if (null !== $group = static::findOne($id)) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        $group,
                        0,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(static::className(), $id),
                                ],
                            ]
                        )
                    );
                }
            }
            static::$identity_map[$id] = $group;
        }
        return static::$identity_map[$id];
    }

    /**
     * Relation to properties
     * @return \yii\db\ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(Property::className(), ['property_group_id' => 'id'])->orderBy('sort_order');
    }

    public static function getForObjectId($object_id, $withProperties = false)
    {
        if (!isset(static::$groups_by_object_id[$object_id])) {

            $cacheKey = 'PropertyGroup:objectId:'.$object_id;
            static::$groups_by_object_id[$object_id] = Yii::$app->cache->get($cacheKey);
            if (!is_array(static::$groups_by_object_id[$object_id])) {
                $query = static::find()
                    ->where(['object_id'=>$object_id])
                    ->orderBy('sort_order');
                if ($withProperties === true) {
                    $query = $query->with('properties');
                }
                static::$groups_by_object_id[$object_id] = $query->all();
                if (null !== $object = Object::findById($object_id)) {
                    $tags = [
                        \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($object, $object_id)
                    ];
                    foreach (static::$groups_by_object_id[$object_id] as $propertyGroup){
                        $tags[] = \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($propertyGroup, $propertyGroup->id);
                        if ($withProperties === true) {
                            foreach ($propertyGroup->properties as $prop) {
                                if (isset(Property::$group_id_to_property_ids[$propertyGroup->id]) === false) {
                                    Property::$group_id_to_property_ids[$propertyGroup->id]=[];
                                }
                                Property::$group_id_to_property_ids[$propertyGroup->id][] = $prop->id;
                                Property::$identity_map[$prop->id] = $prop;
                            }
                        }
                    }


                    Yii::$app->cache->set(
                        $cacheKey,
                        static::$groups_by_object_id[$object_id],
                        0,
                        new TagDependency(
                            [
                                'tags' => $tags,
                            ]
                        )
                    );
                }
            }


        }
        return static::$groups_by_object_id[$object_id];
    }

    /**
     * @param int $object_id
     * @param int $object_model_id
     * @return null|\yii\db\ActiveRecord[]
     */
    public static function getForModel($object_id, $object_model_id)
    {
        $cacheKey = "PropertyGroupBy:$object_id:$object_model_id";
        if (false === $groups = Yii::$app->cache->get($cacheKey)) {

            $groups = Yii::$app->db->cache(
                function($db) use($object_id, $object_model_id) {
                    $group_ids = (new Query())->select('property_group_id')
                        ->from(ObjectPropertyGroup::tableName())
                        ->where([
                            'object_id' => $object_id,
                            'object_model_id' => $object_model_id,
                        ]);
                    return PropertyGroup::find()->where(['in', 'id', $group_ids])->all($db);
                },
                86400,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(PropertyGroup::className()),
                        ActiveRecordHelper::getCommonTag(ObjectPropertyGroup::className()),
                    ]
                ])
            );

            if (null === $groups) {
                return null;
            }
            if (null !== $object = Object::findById($object_id)) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $groups,
                    0,
                    new TagDependency(
                        [
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($object, $object_id),
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($object->object_class, $object_model_id),
                            ],
                        ]
                    )
                );
            }
        }
        return $groups;
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        $properties = Property::findAll(['property_group_id' => $this->id]);
        foreach ($properties as $prop) {
            $prop->delete();
        }
        return true;
    }
    public function afterDelete()
    {
        ObjectPropertyGroup::deleteAll(['property_group_id' => $this->id]);
        parent::afterDelete();
    }
}