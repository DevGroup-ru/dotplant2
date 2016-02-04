<?php

namespace app\properties;

use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\ObjectStaticValues;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use app\modules\core\helpers\ContentBlockHelper;
use app\modules\core\models\ContentBlock;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Behavior;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Class HasProperties
 * @property \yii\db\ActiveRecord $owner
 * @property $object Object
 * @package app\properties
 */
class HasProperties extends Behavior
{
    const FIELD_ADD_PROPERTY_GROUP = 'AddPropertyGroup';
    const FIELD_REMOVE_PROPERTY_GROUP = 'RemovePropertyGroup';
    const FIELD_ADD_PROPERTY = 'AddProperty';
    /**
     * @var AbstractModel
     */
    private $abstract_model = null;
    private $eav_rows = null;
    private $object = null;
    private $properties = null;
    private $property_key_to_id = [];
    private $property_id_to_group_id = [];
    private $static_values = null;
    private $table_inheritance_row = null;
    private $propertiesFormName = null;
    public $props;

    /**
     * Get property group id by property id
     * @param int $id property id
     * @return int property group id
     */
    private function getGroupIdBypropertyId($id)
    {
        foreach ($this->property_id_to_group_id as $groupId => $propertyIds) {
            if (in_array($id, $propertyIds)) {
                return $groupId;
            }
        }
        $property = Property::findById($id);
        if (!array_key_exists($property->property_group_id, $this->property_id_to_group_id)) {
            $this->property_id_to_group_id[$property->property_group_id] = [$id];
        } else {
            $this->property_id_to_group_id[$property->property_group_id][] = $id;
        }
        return intval($property->property_group_id);
    }

    public function getObject()
    {
        if ($this->object === null) {
            $this->object = Object::getForClass(get_class($this->owner));
            if ($this->object === null) {
                throw new \Exception("Can't find Object row for ".get_class($this->owner));
            }
        }
        return $this->object;
    }

    public function getPropertyGroups($force = false, $getByObjectId = false, $createAbstractModel = false)
    {
        $message = "getPropertyGroups ". $this->owner->className() . ':'.$this->owner->id.
            " force:".($force?'true':'false').
            " getByObjectId:".($getByObjectId?'true':'false') .
            " createAbstractModel:".($createAbstractModel?'true':'false');
        Yii::trace(
            $message,
            'properties'
        );
        $cacheKey = $message;


        if ($this->properties === null && $force === false) {
            // try to get from cache
            $data = Yii::$app->cache->get($message);
            if ($data !== false) {
                Yii::trace("Properties from cache", 'properties');
                $this->abstract_model = isset($data['abstract_model'])?$data['abstract_model']:[];
                $this->properties = $data['properties'];
                $this->property_key_to_id = $data['property_key_to_id'];
                $this->property_id_to_group_id = $data['property_id_to_group_id'];
            }
        }

        if ($createAbstractModel === true && is_null($this->abstract_model)) {
            // force creating abstract model if it is null and needed
            $force = true;
        }

        if ($this->properties === null || $force === true) {
            $tags = [ActiveRecordHelper::getObjectTag($this->owner->className(), $this->owner->id)];
            $this->properties = [];
            if ($getByObjectId === true) {
                $groups = PropertyGroup::getForObjectId($this->getObject()->id);
            } else {
                $groups = PropertyGroup::getForModel($this->getObject()->id, $this->owner->id);
            }
            $values_for_abstract = [];
            $properties_models = [];
            $rules = [];
            /** @var PropertyGroup $group */
            foreach ($groups as $group) {
                $tags[] = ActiveRecordHelper::getObjectTag(PropertyGroup::className(), $group->id);
                $this->properties[$group->id] = [];
                $props = Property::getForGroupId($group->id);
                foreach ($props as $p) {
                    $values = $this->getPropertyValues($p);
                    $this->properties[$group->id][$p->key] = $values;

                    if ($createAbstractModel === true) {
                        $values_for_abstract[$p->key] = $values;
                    }

                    $properties_models[$p->key] = $p;
                    $this->property_key_to_id[$p->key] = $p->id;
                    if (!isset($this->property_id_to_group_id[$group->id])) {
                        $this->property_id_to_group_id[$group->id] = [$p->key];
                    } else {
                        $this->property_id_to_group_id[$group->id][] = $p->key;
                    }

                    if ($createAbstractModel === true) {
                        $handlerAdditionalParams = Json::decode($p->handler_additional_params);
                        if (isset($handlerAdditionalParams['rules']) && is_array($handlerAdditionalParams['rules'])) {
                            foreach ($handlerAdditionalParams['rules'] as $rule) {
                                if (is_array($rule)) {
                                    $rules[] = ArrayHelper::merge([$p->key], $rule);
                                } else {
                                    $rules[] = [$p->key, $rule];
                                }
                            }
                        }
                    }
                }
            }

            if ($createAbstractModel === true) {
                $this->abstract_model = new AbstractModel([], $this->owner);
                $this->abstract_model->setPropertiesModels($properties_models);
                $this->abstract_model->setAttributes($values_for_abstract);
                $this->abstract_model->setFormName('Properties_' . $this->owner->formName() . '_' . $this->owner->id);
                $this->abstract_model->addRules($rules);
            }

            $res = Yii::$app->cache->set(
                $cacheKey,
                [
                    'properties' => $this->properties,
                    'abstract_model' => $this->abstract_model,
                    'property_id_to_group_id' => $this->property_id_to_group_id,
                    'property_key_to_id' => $this->property_key_to_id,
                ],
                86400,
                new TagDependency([
                    'tags' => $tags,
                ])
            );
            Yii::trace('putting props to cache: ' . ($res?'true':'false') . ' key:' . $cacheKey, 'properties');
        }
        return $this->properties;
    }

    /**
     * Adds property group to object instance
     *
     * @param int|string $property_group_id Id of Property Group
     * @param bool $refreshProperties
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function addPropertyGroup($property_group_id, $refreshProperties = true, $createAbstractModel = false)
    {
        $params = [];
        $where = [
            'object_id' => $this->getObject()->id,
            'object_model_id' => $this->owner->id,
            'property_group_id' => $property_group_id,
        ];
        if (null === ObjectPropertyGroup::findOne($where)) {
            $sql = Yii::$app->db->queryBuilder->insert(
                ObjectPropertyGroup::tableName(),
                $where,
                $params
            );

            Yii::$app->db->createCommand(
                $sql,
                $params
            )->execute();
        }
        if ($refreshProperties === true) {
            $this->updatePropertyGroupsInformation($createAbstractModel);
        }
    }

    public function removePropertyGroup($property_group_id)
    {
        ObjectPropertyGroup::deleteAll(
            [
                'object_id' => $this->getObject()->id,
                'object_model_id' => $this->owner->id,
                'property_group_id' => $property_group_id,
            ]
        );
    }

    public function setPropertiesFormName($name = null)
    {
        $this->propertiesFormName = $name;
    }

    public function saveProperties($data)
    {
        $form = $this->owner->formName();
        $formProperties = empty($this->propertiesFormName)
            ? 'Properties_'.$form.'_'.$this->owner->id
            : $this->propertiesFormName;

        $this->getPropertyGroups();
        if (isset($data[self::FIELD_ADD_PROPERTY_GROUP]) && isset($data[self::FIELD_ADD_PROPERTY_GROUP][$form])) {
            $groups_to_add = is_array($data[self::FIELD_ADD_PROPERTY_GROUP][$form])
                ? $data[self::FIELD_ADD_PROPERTY_GROUP][$form]
                : [$data[self::FIELD_ADD_PROPERTY_GROUP][$form]];
            foreach ($groups_to_add as $group_id) {
                $this->addPropertyGroup($group_id, false);
            }
            $this->updatePropertyGroupsInformation(true);
        }
        if (isset($data[self::FIELD_REMOVE_PROPERTY_GROUP]) && isset($data[self::FIELD_REMOVE_PROPERTY_GROUP][$form])) {
            $this->removePropertyGroup($data[self::FIELD_REMOVE_PROPERTY_GROUP][$form]);
        }
        if (isset($data[$formProperties])) {
            $my_data = $data[$formProperties];

            if (isset($data[self::FIELD_ADD_PROPERTY])) {
                // admin clicked add property button for multiple properties
                if (!isset($my_data[$data[self::FIELD_ADD_PROPERTY]])) {
                    $my_data[$data[self::FIELD_ADD_PROPERTY]] = [];
                }
                $my_data[$data[self::FIELD_ADD_PROPERTY]][] = '';
            }

            $propertiesModels = $this->getAbstractModel()->getPropertiesModels();

            $new_values_for_abstract = [];
            foreach ($my_data as $property_key => $values) {
                if (isset($this->property_key_to_id[$property_key])) {
                    $vals = [];
                    $property_id = $this->property_key_to_id[$property_key];
                    $values = is_array($values) ? $values : [$values];

                    if (isset($propertiesModels[$property_key])) {
                        $_property = $propertiesModels[$property_key];
                        $propertyHandler = PropertyHandlers::getHandlerById($_property->property_handler_id);
                        if (null === $propertyHandler) {
                            $propertyHandler = PropertyHandlers::createHandler($_property->handler);
                        }

                        $values = $propertyHandler->processValues($_property, $formProperties, $values);
                    }

                    foreach ($values as $index => $val) {
                        $vals[] = ['value' => $val, 'property_id' => $property_id, 'sort_order' => $index];
                    }

                    $val = new PropertyValue(
                        $vals,
                        $property_id,
                        $this->getObject()->id,
                        $this->owner->id,
                        $this->getGroupIdBypropertyId($property_id)
                    );
                    $new_values_for_abstract[$property_key] = $val;
                }
            }

            $this->abstract_model->updateValues($new_values_for_abstract, $this->getObject()->id, $this->owner->id);
            $this->owner->trigger(ActiveRecord::EVENT_AFTER_UPDATE, new AfterSaveEvent(['changedAttributes' => []]));
        }
    }

    public function updatePropertyGroupsInformation($createAbstractModel = false)
    {
        $this->owner->invalidateTags();
        $this->table_inheritance_row = null;
        $this->eav_rows = null;
        $this->static_values = null;
        $this->abstract_model = null;
        $this->property_key_to_id = [];
        $this->getPropertyGroups(true, false, $createAbstractModel);
    }

    public function getAbstractModel()
    {
        if (empty($this->abstract_model)) {
            $this->getPropertyGroups(!is_object($this->abstract_model), false, true);
        }
        return $this->abstract_model;
    }

    public function setAbstractModel(AbstractModel $model)
    {
        $this->abstract_model = $model;
    }

    /**
     * Get PropertyValue model by key. It consists object property values
     * @param string $key
     * @return PropertyValue
     */
    public function getPropertyValuesByKey($key)
    {
        $this->getPropertyGroups();
        foreach ($this->properties as $group_id => $group) {
            foreach ($group as $property_key => $value) {
                if ($property_key === $key) {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * Get property values by key.
     * @param $key
     * @param bool $asString
     * @param string $delimiter
     * @return array|string
     */
    public function property($key, $asString = true, $delimiter = ', ')
    {
        $values = [];
        $propertyValue = $this->getPropertyValuesByKey($key);
        if (is_null($propertyValue)) {
            return $asString ? '' : [];
        }
        foreach ($propertyValue->values as $value) {
            $values[] = $value['value'];
        }
        if ($asString) {
            return ContentBlockHelper::compileContentString(
                implode($delimiter, $values),
                "{$this->owner->className()}:{$this->owner->id}:property={$key}",
                new TagDependency(
                    [
                        'tags' => [
                            $this->owner->objectTag(),
                            ActiveRecordHelper::getCommonTag(ContentBlock::className())
                        ]
                    ]
                )
            );
        } else {
            return $values;
        }
    }

    public function getPropertyValuesByPropertyId($property_id)
    {
        $this->getPropertyGroups();
        foreach ($this->properties as $group_id => $group) {
            foreach ($group as $property_key => $value) {
                if ($value->property_id == $property_id) {
                    return $value;
                }
            }
        }
        return null;
    }

    private function getPropertyValues($property)
    {
        $values = [];
        if ($property->is_column_type_stored) {
            $row = $this->getTableInheritanceRow();
            if (isset($row[$property->key])) {
                $values[] = [
                    'value' => $row[$property->key],
                    'property_id' => $property->id,
                ];
            }
        } elseif ($property->has_static_values) {
            $static_values = $this->getStaticValues();
            if (isset($static_values[$property->id])) {
                $values = $static_values[$property->id];
            }
        } elseif ($property->is_eav) {
            foreach ($this->getEavRows() as $row) {
                if ($row['key'] === $property->key) {
                    $values[] = $row;
                }
            }

        }
        return new PropertyValue($values, $property->id, $this->getObject()->id, $this->owner->id);
    }

    private function getEavRows()
    {
        if ($this->eav_rows === null) {
            $cacheKey = md5("EAV_ROWS:".$this->getObject()->eav_table_name.":".$this->owner->id);

            $this->eav_rows = Yii::$app->cache->get($cacheKey);

            if ($this->eav_rows === false) {
                $this->eav_rows =
                    (new Query())
                        ->select('`key`, value, sort_order, id as eav_id')
                        ->from($this->getObject()->eav_table_name)
                        ->where(['object_model_id' => $this->owner->id])
                        ->orderBy('sort_order')
                        ->all();

                Yii::$app->cache->set(
                    $cacheKey,
                    $this->eav_rows,
                    86400,
                    new TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getObjectTag($this->owner->className(), $this->owner->id)
                        ]
                    ])
                );
            }
        }
        return $this->eav_rows;
    }

    private function getTableInheritanceRow()
    {
        if ($this->table_inheritance_row === null) {
            $cacheKey = "TIR:" . $this->getObject()->id . ':' . $this->owner->id;
            $this->table_inheritance_row = Yii::$app->cache->get($cacheKey);
            if (!is_array($this->table_inheritance_row)) {
                $this->table_inheritance_row = (new Query())
                    ->select('*')
                    ->from($this->getObject()->column_properties_table_name)
                    ->where(['object_model_id' => $this->owner->id])
                    ->one();
                if (!is_array($this->table_inheritance_row)) {
                    $this->table_inheritance_row = [];
                }
                Yii::$app->cache->set(
                    $cacheKey,
                    $this->table_inheritance_row,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getObjectTag($this->owner, $this->owner->id),
                            ],
                        ]
                    )
                );
            }
        }
        return $this->table_inheritance_row;
    }

    private function getStaticValues()
    {
        if ($this->static_values === null) {
            $cacheKey = "PSV:" . $this->getObject()->id . ":" . $this->owner->id;
            $array = Yii::$app->cache->get($cacheKey);
            if (!is_array($array)) {
                $array = (new Query())
                    ->select("PSV.property_id, PSV.value, PSV.slug, PSV.name, PSV.id as psv_id")
                    ->from(ObjectStaticValues::tableName() . " OSV")
                    ->innerJoin(PropertyStaticValues::tableName() . " PSV", "PSV.id = OSV.property_static_value_id")
                    ->where(
                        [
                            'OSV.object_id' => $this->getObject()->id,
                            'OSV.object_model_id' => $this->owner->id,
                        ]
                    )->orderBy('PSV.sort_order')
                    ->all();
                Yii::$app->cache->set(
                    $cacheKey,
                    $array,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getObjectTag($this->owner, $this->owner->id),
                            ],
                        ]
                    )
                );
            }
            $this->static_values = [];
            foreach ($array as $row) {
                $this->static_values[$row['property_id']][$row['value']] = $row;
            }
        }
        return $this->static_values;
    }
}
