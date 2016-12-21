<?php

namespace app\extensions\DefaultTheme\widgets\FilterSets;

use app\extensions\DefaultTheme\components\BaseWidget;
use app\models\Property;
use app\models\PropertyStaticValues;
use app\modules\shop\models\FilterSets;
use app\modules\shop\models\Product;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\Url;

class Widget extends BaseWidget
{
    protected $toUnset = [];
    public $viewFile = 'filter-sets';
    public $hideEmpty = true;
    public $usePjax = true;
    public $useNewFilter = false;

    /**
     * Remove lost dependencies from url params.
     * Also this method builds toUnset array.
     * @param FilterSets $filterSets
     * @param array $urlParams
     * @return array
     */
    protected function removeLostDependencies($filterSets, $urlParams)
    {
        $depends = [];
        $this->toUnset = [];
        foreach ($filterSets as $set) {
            if (false == empty($set->property->depends_on_property_id)) {
                if (isset($this->toUnset[$set->property->depends_on_property_id])) {
                    $this->toUnset[$set->property->depends_on_property_id][] = $set->property->id;
                } else {
                    $this->toUnset[$set->property->depends_on_property_id] = [$set->property->id];
                }
                if (false === isset($depends[$set->property->id])) {
                    $depends[$set->property->id] = [
                        $set->property->depends_on_property_id => $set->property->depended_property_values
                    ];
                } else {
                    $depends[$set->property->id][$set->property->depends_on_property_id] =
                        $set->property->depended_property_values;
                }
            }
        }
        foreach ($depends as $prop_id => $depend) {
            $key = key($depend);
            if (isset($urlParams['properties'][$prop_id])) {
                if (false === isset($urlParams['properties'][$key])) {
                    unset($urlParams['properties'][$prop_id]);
                } else {
                    if (false === in_array($depend[$key], $urlParams['properties'][$key])) {
                        unset($urlParams['properties'][$prop_id]);
                    }
                }
            }
        }
        return $urlParams;
    }

    public function getEavSliderFilter(Property $property, $urlParams)
    {
        $result = null;
        $key = $property->key;
        $eavCategoryData = Yii::$app->db->createCommand(
            'SELECT MAX(CAST({{%product_eav}}.value AS DECIMAL)) as max, MIN(CAST({{%product_eav}}.value AS DECIMAL)) as min
              FROM {{%product_eav}}
              LEFT JOIN {{%product_category}} ON ({{%product_category}}.object_model_id = {{%product_eav}}.object_model_id)
              WHERE {{%product_eav}}.key=:key AND {{%product_category}}.category_id=:category_id'
        )->bindParam(':key', $key)
            ->bindParam(':category_id', $urlParams['last_category_id'])
            ->queryOne();

        if ($eavCategoryData['max'] !== null &&
            $eavCategoryData['min'] !== null &&
            $eavCategoryData['max'] !== $eavCategoryData['min']
        ) {
            $result = [
                'id' => $property->id,
                'name' => $property->name,
                'isRange' => 1,
                'selections' => [],
                'multiple' => 0,
                'max' => $eavCategoryData['max'],
                'min' => $eavCategoryData['min'],
                'property' => $property,
                'step' => 1
            ];
        }
        return $result;
    }


    /**
     * Get selected property index
     * @param array $properties
     * @param integer $propertyId
     * @param integer $propertyValueId
     * @return string|false
     */
    protected function getSelectedPropertyIndex($properties, $propertyId, $propertyValueId)
    {
        if (!isset($properties[$propertyId])) {
            return false;
        }
        return array_search($propertyValueId, $properties[$propertyId]);
    }

    /**
     * Smart url params merging
     * @param array $a
     * @param array $b
     * @return array mixed
     */
    protected function mergeUrlProperties($a, $b)
    {
        if (isset($a['properties'], $b['properties'])) {
            foreach ($b['properties'] as $propertyId => $staticValues) {
                foreach ($staticValues as $staticValue) {
                    if (isset($a['properties'][$propertyId])) {
                        $a['properties'][$propertyId][] = $staticValue;
                    } else {
                        $a['properties'][$propertyId] = [$staticValue];
                    }
                }
                $a['properties'][$propertyId] = array_unique($a['properties'][$propertyId]);
            }
        }
        return $a;
    }

    /**
     * Actual run function for all widget classes extending BaseWidget
     *
     * @return mixed
     */
    public function widgetRun()
    {
        $categoryId = Yii::$app->request->get('last_category_id');
        if ($categoryId === null) {
            return '<!-- no category_id specified for FilterSet widget in request -->';
        }
        $filterSets = FilterSets::getForCategoryId($categoryId);
        if (count($filterSets) === 0) {
            return '<!-- no filter sets for current category -->';
        }
        if ($this->header === '') {
            $this->header = Yii::t('app', 'Filters');
        }

        // Begin of a new filter sets implementation
        if ($this->useNewFilter) {
            $urlParams = [
                '@category',
                'last_category_id' => $categoryId,
                'properties' => Yii::$app->request->get('properties', []),
                'category_group_id' => Yii::$app->request->get('category_group_id', 0),
            ];
            $priceMin = empty(Yii::$app->request->post('price_min')) ? Yii::$app->request->get('price_min') : Yii::$app->request->post('price_min');
            $priceMax = empty(Yii::$app->request->post('price_max')) ? Yii::$app->request->get('price_max') : Yii::$app->request->post('price_max');
            if (false === empty($priceMin)) {
                $urlParams['price_min'] = $priceMin;
            }
            if (false === empty($priceMax)) {
                $urlParams['price_max'] = $priceMax;
            }
            $urlParams = $this->mergeUrlProperties($urlParams, ['properties' => Yii::$app->request->post('properties', [])]);
            $urlParams = $this->removeLostDependencies($filterSets, $urlParams);
            $properties = $urlParams['properties'];
            $newGet = Yii::$app->request->get();
            $newGet['properties'] = $properties;
            Yii::$app->request->setQueryParams($newGet);
            ksort($properties);
            $cacheKey = 'FilterSets:' . $categoryId . ':' . json_encode($urlParams);
            unset($properties);
            if (false === $filtersArray = Yii::$app->cache->get($cacheKey)) {
                $filtersArray = [];
                foreach ($filterSets as $filterSet) {
                    /** Если eav и слайдер, то фильтр формируется по особым правилам */
                    if ($filterSet->is_range_slider && $filterSet->property->is_eav) {
                        $filter = $this->getEavSliderFilter($filterSet->property, $urlParams);
                        if($filter) {
                            $filtersArray[] = $filter;
                        }
                        continue;
                    } elseif ($filterSet->property->has_static_values === 0) {
                        continue;
                    }
                    if (!empty($filterSet->property->depends_on_property_id)
                        && !empty($filterSet->property->depended_property_values)
                    ) {
                        if (
                            $this->getSelectedPropertyIndex(
                                $urlParams['properties'],
                                $filterSet->property->depends_on_property_id,
                                $filterSet->property->depended_property_values
                            ) === false
                        ) {
                            continue;
                        }
                    }
                    $selections = PropertyStaticValues::getValuesForFilter(
                        $filterSet->property->id,
                        $urlParams['last_category_id'],
                        $urlParams['properties'],
                        $filterSet->multiple,
                        Yii::$app->getModule('shop')->productsFilteringMode
                    );
                    if (count($selections) === 0) {
                        continue;
                    }
                    $item = [
                        'id' => $filterSet->property->id,
                        'name' => $filterSet->property->name,
                        'sort_order' => $filterSet->property->sort_order,
                        'isRange' => $filterSet->is_range_slider,
                        'selections' => [],
                        'multiple' => $filterSet->multiple,
                    ];
                    if ($filterSet->is_range_slider) {
                        $item['max'] = PHP_INT_MIN;
                        $item['min'] = PHP_INT_MAX;
                        $item['property'] = $filterSet->property;
                    }
                    foreach ($selections as $selection) {
                        if ($filterSet->is_range_slider) {
                            if ((int)$selection['value'] > $item['max']) {
                                $item['max'] = (int)$selection['value'];
                            }
                            if ((int)$selection['value'] < $item['min']) {
                                $item['min'] = (int)$selection['value'];
                            }
                        } elseif($item['multiple']) {
                            $selectedPropertyIndex = $this->getSelectedPropertyIndex(
                                $urlParams['properties'],
                                $filterSet->property_id,
                                $selection['id']
                            );
                            if ($selectedPropertyIndex !== false) {
                                if (count($urlParams['properties'][$filterSet->property_id]) > 1) {
                                    $routeParams = $urlParams;
                                    unset($routeParams['properties'][$filterSet->property_id][$selectedPropertyIndex]);
                                } else {
                                    $routeParams = $urlParams;
                                    unset($routeParams['properties'][$filterSet->property_id]);
                                }
                                if (isset($this->toUnset[$filterSet->property_id])) {
                                    foreach ($this->toUnset[$filterSet->property_id] as $id) {
                                        unset($routeParams['properties'][$id]);
                                    }
                                }
                            } else {
                                $routeParams = $this->mergeUrlProperties(
                                    $urlParams,
                                    ['properties' => [$filterSet->property_id => [$selection['id']]]]
                                );
                            }
                            $item['selections'][] = [
                                'id' => $selection['id'],
                                'checked' => $selectedPropertyIndex !== false,
                                'label' => $selection['name'],
                                'url' =>  Url::toRoute($routeParams),
                                'active' => $selection['active'],
                            ];
                        } else {
                            $selectedPropertyIndex = $this->getSelectedPropertyIndex(
                                $urlParams['properties'],
                                $filterSet->property_id,
                                $selection['id']
                            );
                            if ($selectedPropertyIndex !== false) {
                                if (count($urlParams['properties'][$filterSet->property_id]) > 1) {
                                    $routeParams = $urlParams;
                                    unset($routeParams['properties'][$filterSet->property_id][$selectedPropertyIndex]);
                                } else {
                                    $routeParams = $urlParams;
                                    unset($routeParams['properties'][$filterSet->property_id]);
                                }
                                if (isset($this->toUnset[$filterSet->property_id])) {
                                    foreach ($this->toUnset[$filterSet->property_id] as $id) {
                                        unset($routeParams['properties'][$id]);
                                    }
                                }
                            } else {
                                $routeParams = $this->mergeUrlProperties(
                                    $urlParams,
                                    ['properties' => [$filterSet->property_id => [$selection['id']]]]
                                );
                            }
                            $item['selections'][] = [
                                'id' => $selection['id'],
                                'checked' => $selectedPropertyIndex !== false,
                                'label' => $selection['name'],
                                'url' => $selection['active'] === true || $selectedPropertyIndex !== false
                                    ? Url::toRoute($routeParams)
                                    : null,
                                'active' => $selection['active'],
                            ];
                        }
                    }
                    if ($filterSet->is_range_slider) {
                        if ($item['min'] === $item['max']) {
                            continue;
                        }
                        $i = 1;
                        $n = $item['max'] - $item['min'];
                        while ($n >= 10) {
                            $n = $n / 10;
                            $i++;
                        }
                        $item['step'] = $i > 3 ? (int)pow(10, $i - 3) : 1;
                        unset($i, $n);
                    }
                    $filtersArray[] = $item;
                    unset($item);
                }
                Yii::$app->cache->set(
                    $cacheKey,
                    $filtersArray,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(FilterSets::className()),
                                ActiveRecordHelper::getCommonTag(Product::className()),
                                ActiveRecordHelper::getCommonTag(Property::className()),
                            ],
                        ]
                    )
                );
            }
            return $this->render(
                "filter-sets-new",
                [
                    'filtersArray' => $filtersArray,
                    'id' => 'filter-set-' . $this->id,
                    'urlParams' => $urlParams,
                    'usePjax' => $this->usePjax,
                ]
            );
        }
        // End of a new filter sets implementation

        return $this->render(
            $this->viewFile,
            [
                'filterSets' => $filterSets,
                'id' => 'filter-set-' . $this->id,
                'hideEmpty' => $this->hideEmpty,
            ]
        );
    }
}
