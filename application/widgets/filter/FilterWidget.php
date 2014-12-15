<?php

namespace app\widgets\filter;

use app\models\Object;
use app\models\ObjectStaticValues;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use Yii;
use yii\base\Widget;
use yii\caching\TagDependency;
use yii\db\Query;

class FilterWidget extends Widget
{
    private $possibleSelections = null;
    public $categoryGroupId = 0;
    public $currentSelections = [];
    public $goBackAlignment = 'left';
    public $objectId = null;
    public $onlyAvailableFilters = true;
    public $route = '/product/list';
    public $title = 'Filter';
    public $viewFile = 'filterWidget';

    public function run()
    {
        Yii::beginProfile("FilterWidget");

        $view = $this->getView();
        FilterWidgetAsset::register($view);
        $view->registerJs(
            "jQuery('#{$this->id}').getFilters();"
        );

        Yii::beginProfile("GetPossibleSelections");
        $this->getPossibleSelections();
        Yii::endProfile("GetPossibleSelections");

        $result = $this->render(
            $this->viewFile,
            [
                'id' => $this->id,
                'current_selections' => $this->currentSelections,
                'possible_selections' => $this->possibleSelections,
                'object_id' => $this->objectId,
                'title' => $this->title,
                'go_back_alignment' => $this->goBackAlignment,
                'route' => $this->route,
                'category_group_id' => $this->categoryGroupId,
            ]
        );

        Yii::endProfile("FilterWidget");

        return $result;
    }

    public function getPossibleSelections()
    {
        $data = [
            'propertyIds' => [],
            'propertyStaticValueIds' => [],
        ];
        if ($this->onlyAvailableFilters) {
            $object = Object::findById($this->objectId);
            if (!is_null($object) && isset($this->currentSelections['last_category_id'])) {
                $propertyStaticValues = [];
                if (isset($this->currentSelections['properties'])) {
                    foreach ($this->currentSelections['properties'] as $values) {
                        foreach ($values as $value) {
                            $propertyStaticValues[] = $value;
                        }
                    }
                    sort($propertyStaticValues);
                }
                $cacheKey = 'FilterWidget: ' . $object->id . ':' . $this->currentSelections['last_category_id'] . ':'
                    . implode('-', $propertyStaticValues);
                $data = Yii::$app->cache->get($cacheKey);
                if ($data === false) {
                    $query = new Query();
                    $query = $query->select($object->categories_table_name . '.object_model_id')
                        ->distinct()
                        ->from($object->categories_table_name)
                        ->where(['category_id' => $this->currentSelections['last_category_id']]);
                    foreach ($propertyStaticValues as $value) {
                        $query->join(
                            'JOIN',
                            ObjectStaticValues::tableName() . ' value' . $value,
                            'value' . $value . '.object_id = :objectId AND '
                            . 'value' . $value . '.object_model_id = ' . $object->categories_table_name . '.object_model_id AND '
                            . 'value' . $value . '.property_static_value_id=:staticValueId' . $value,
                            [
                                ':objectId' => $object->id,
                                ':staticValueId' . $value => $value,
                            ]
                        );
                    }
                    $ids = $query->column();
                    $query = null;
                    $data['propertyStaticValueIds'] = ObjectStaticValues::find()
                        ->select('property_static_value_id')
                        ->distinct()
                        ->where(['object_id' => $object->id, 'object_model_id' => $ids])
                        ->column();
                    $ids = null;
                    $data['propertyIds'] = PropertyStaticValues::find()
                        ->select('property_id')
                        ->distinct()
                        ->where(['id' => $data['propertyStaticValueIds']])
                        ->column();
                    Yii::$app->cache->set(
                        $cacheKey,
                        $data,
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag($object->object_class)
                                ],
                            ]
                        )
                    );
                    $object = null;
                }
            }
        }

        $this->possibleSelections = [];

        $groups = PropertyGroup::getForObjectId($this->objectId);

        foreach ($groups as $group) {
            if ($group->is_internal) {
                continue;
            }
            $this->possibleSelections[$group->id] = [
                'group' => $group,
                'selections' => [],
                'static_selections' => [],
                'dynamic_selections' => [],
            ];
            $props = Property::getForGroupId($group->id);
            foreach ($props as $p) {
                if ($this->onlyAvailableFilters && !in_array($p->id, $data['propertyIds'])) {
                    continue;
                }
                if ($p->dont_filter) {
                    continue;
                }
                if ($p->has_static_values) {
                    $propertyStaticValues = PropertyStaticValues::getValuesForPropertyId($p->id);
                    if ($this->onlyAvailableFilters) {
                        foreach ($propertyStaticValues as $key => $propertyStaticValue) {
                            if (!in_array($propertyStaticValue['id'], $data['propertyStaticValueIds'])) {
                                unset($propertyStaticValues[$key]);
                            }
                        }
                    }
                    $this->possibleSelections[$group->id]['static_selections'][$p->id] = $propertyStaticValues;
                } elseif ($p->is_column_type_stored && $p->value_type == 'NUMBER') {
                    $this->possibleSelections[$group->id]['dynamic_selections'][] = $p->id;
                }
            }
            if (count($this->possibleSelections[$group->id]) === 0) {
                unset($this->possibleSelections[$group->id]);
            }
        }
    }
}
