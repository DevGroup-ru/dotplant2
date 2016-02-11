<?php

namespace app\widgets\filter;

use app\models\Object;
use app\models\ObjectStaticValues;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use app\modules\Shop\models\Product;
use Yii;
use yii\base\Widget;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class FilterWidget extends Widget
{
    protected $possibleSelections = null;
    public $categoryGroupId = 0;
    public $currentSelections = [];
    public $goBackAlignment = 'left';
    public $objectId = null;
    public $onlyAvailableFilters = true;
    public $disableInsteadOfHide = false;
    public $route = '/shop/product/list';
    public $title = 'Filter';
    public $viewFile = 'filterWidget';
    protected $disabled_ids = [];
    public $render_dynamic = true;

    /**
     * @var null|array Array of group ids to display in filter, null to display all available for Object
     */
    public $onlyGroupsIds = null;

    /**
     * @var array Additional params passed to filter view
     */
    public $additionalViewParams = [];

    /**
     * @inheritdoc
     */
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
            ArrayHelper::merge([
                'id' => $this->id,
                'current_selections' => $this->currentSelections,
                'possible_selections' => $this->possibleSelections,
                'object_id' => $this->objectId,
                'title' => $this->title,
                'go_back_alignment' => $this->goBackAlignment,
                'route' => $this->route,
                'category_group_id' => $this->categoryGroupId,
                'disabled_ids' => $this->disabled_ids,
                'render_dynamic' => $this->render_dynamic,
            ], $this->additionalViewParams)
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
            Yii::beginProfile("onlyAvailableFilters");
            $object = Object::findById($this->objectId);
            if (!is_null($object) && isset($this->currentSelections['last_category_id'])) {

                $cacheKey = 'FilterWidget: ' . $object->id . ':' . $this->currentSelections['last_category_id'] . ':'
                    . Json::encode($this->currentSelections['properties']);
                $data = Yii::$app->cache->get($cacheKey);
                if ($data === false) {
                    $data = [];
                    Yii::beginProfile("ObjectIds for this category");

                    $psv = [];
                    array_walk_recursive($this->currentSelections['properties'], function ($item) use (&$psv) {
                        if (is_array($item)) {
                            continue;
                        }
                        $psv[] = $item;
                    });

                    $query = (new Query)
                        ->select('pc.object_model_id')
                        ->from($object->categories_table_name . ' pc')
                        ->innerJoin(Product::tableName() . ' product', 'product.id = pc.object_model_id')
                        ->where([
                            'pc.category_id' => $this->currentSelections['last_category_id'],
                            'product.active' => 1,
                        ]);

                    if (count($this->currentSelections['properties'])) {
                        $query->innerJoin([
                            'osvm' => (new Query)
                                ->select('object_model_id')
                                ->distinct()
                                ->from(ObjectStaticValues::tableName())
                                ->where([
                                    'object_id' => $object->id,
                                    'property_static_value_id' => $psv
                                ])
                                ->groupBy('object_model_id')
                                ->having(['count(object_model_id)' => count($psv)])
                            ],
                            'osvm.object_model_id = pc.object_model_id'
                        );
                    }

                    Yii::endProfile("ObjectIds for this category");

                    $ids = array_map('intval', $query->column());
                    $query = null;

                    Yii::beginProfile("all PSV ids");
                    $data['propertyStaticValueIds'] = [];
                    if (count($ids) !== 0) {
                        $q4psv = (new Query())
                            ->select('property_static_value_id')
                            ->from(ObjectStaticValues::tableName())
                            ->distinct()
                            ->where(['object_id' => $object->id])
                            ->andWhere('object_model_id in (' . implode(',', $ids) . ')');


                        $data['propertyStaticValueIds'] = array_map('intval', $q4psv->column());
                    }
                    Yii::endProfile("all PSV ids");


                    $ids = null;

                    Yii::beginProfile("Property ids from PSV ids");
                    $data['propertyIds'] = [];
                    if (count($data['propertyStaticValueIds']) !== 0) {
                        $data['propertyIds'] = PropertyStaticValues::find()
                            ->select('property_id')
                            ->distinct()
                            ->where(['dont_filter' => 0])
                            ->andWhere('id IN (' . implode(',', $data['propertyStaticValueIds']) . ')')
                            ->asArray()
                            ->column();
                    }
                    Yii::endProfile("Property ids from PSV ids");

                    Yii::$app->cache->set(
                        $cacheKey,
                        $data,
                        86400,
                        new TagDependency([
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag($object->object_class)
                            ],
                        ])
                    );
                    $object = null;
                }
            }
            Yii::endProfile("onlyAvailableFilters");
        }

        $this->possibleSelections = [];

        $groups = PropertyGroup::getForObjectId($this->objectId);

        foreach ($groups as $group) {

            if ($this->onlyGroupsIds !== null) {
                if (in_array($group->id, $this->onlyGroupsIds) === false) {
                    // skip this group
                    continue;
                }
            }

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
                    if ($this->disableInsteadOfHide === false) {
                        continue;
                    }
                }
                if ($p->dont_filter) {
                    continue;
                }
                if ($p->has_static_values) {
                    $propertyStaticValues = PropertyStaticValues::getValuesForPropertyId($p->id);
                    foreach ($propertyStaticValues as $key => $propertyStaticValue) {

                        if ($propertyStaticValue['dont_filter']) {
                            unset($propertyStaticValues[$key]);
                        }
                    }
                    if ($this->onlyAvailableFilters) {
                        foreach ($propertyStaticValues as $key => $propertyStaticValue) {

                            if (!in_array($propertyStaticValue['id'], $data['propertyStaticValueIds'])) {
                                if ($this->disableInsteadOfHide === true) {
                                    $this->disabled_ids[]=$propertyStaticValue['id'];
                                } else {
                                    unset($propertyStaticValues[$key]);
                                }
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
