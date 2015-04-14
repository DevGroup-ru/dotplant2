<?php

namespace app\properties;

use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\ObjectStaticValues;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyHandler;
use app\models\PropertyStaticValues;
use Yii;
use yii\base\Behavior;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class HasProperties extends Behavior
{
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
    public $props;
    
    /**
     * Get property group id by property id 
     * @param $id property id
     * @return int|string property group id
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
        return $property->property_group_id;
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

    public function getPropertyGroups($force = false, $getByObjectId = false)
    {
        if ($this->properties === null || $force === true) {
            $this->properties = [];
            if ($getByObjectId) {
                $groups = PropertyGroup::getForObjectId($this->getObject()->id);
            } else {
                $groups = PropertyGroup::getForModel($this->getObject()->id, $this->owner->id);
            }
            $values_for_abstract = [];
            $properties_models = [];
            $rules = [];
            /** @var PropertyGroup $group */
            foreach ($groups as $group) {
                $this->properties[$group->id] = [];
                $props = Property::getForGroupId($group->id);
                foreach ($props as $p) {
                    $values = $this->getPropertyValues($p);
                    $this->properties[$group->id][$p->key] = $values;
                    $values_for_abstract[$p->key] = $values;
                    $properties_models[$p->key] = $p;
                    $this->property_key_to_id[$p->key] = $p->id;
                    if (!isset($this->property_id_to_group_id[$group->id])) {
                        $this->property_id_to_group_id[$group->id] = [$p->key];
                    } else {
                        $this->property_id_to_group_id[$group->id][] = $p->key;
                    }
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
            $this->abstract_model = new AbstractModel([], $this->owner);
            $this->abstract_model->setPropertiesModels($properties_models);
            $this->abstract_model->setAttributes($values_for_abstract);
            $this->abstract_model->setFormName('Properties_' . $this->owner->formName()  .'_' . $this->owner->id);
            $this->abstract_model->addRules($rules);
        }
        return $this->properties;
    }

    public function saveProperties($data)
    {
        $form = $this->owner->formName();
        $formProperties = 'Properties_'.$form.'_'.$this->owner->id;

        $this->getPropertyGroups();
        if (isset($data['AddPropetryGroup']) && isset($data['AddPropetryGroup'][$form])) {
            $groups_to_add = is_array($data['AddPropetryGroup'][$form]) ? $data['AddPropetryGroup'][$form] : [$data['AddPropetryGroup'][$form]];
            foreach ($groups_to_add as $group_id) {
                $model = new ObjectPropertyGroup();
                $model->object_id = $this->getObject()->id;
                $model->object_model_id = $this->owner->id;
                $model->property_group_id = $group_id;
                $model->save();
            }
            $this->updatePropertyGroupsInformation();
        }
        if (isset($data[$formProperties])) {
            $my_data = $data[$formProperties];

            if (isset($data['AddProperty'])) {
                // admin clicked add property button for multiple properties
                if (!isset($my_data[$data['AddProperty']])) {
                    $my_data[$data['AddProperty']] = [];
                }
                $my_data[$data['AddProperty']][] = '';
            }

            $propertiesModels = $this->abstract_model->getPropertiesModels();

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

                    foreach ($values as $val) {
                        $vals[] = ['value' => $val, 'property_id' => $property_id];
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
        }
    }

    public function updatePropertyGroupsInformation()
    {
        $this->owner->invalidateTags();
        $this->table_inheritance_row = null;
        $this->eav_rows = null;
        $this->static_values = null;
        $this->abstract_model = null;
        $this->property_key_to_id = [];
        $this->getPropertyGroups(true);
    }

    public function getAbstractModel()
    {
        $this->getPropertyGroups();
        return $this->abstract_model;
    }

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
            $this->eav_rows = (new Query())
                ->select('`key`, value, sort_order, id as eav_id')
                ->from($this->getObject()->eav_table_name)
                ->where(['object_model_id' => $this->owner->id])
                ->orderBy('sort_order')
                ->all();
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
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($this->owner, $this->owner->id),
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
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($this->getObject(), $this->owner->id),
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
