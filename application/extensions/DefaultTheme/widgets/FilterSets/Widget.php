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
                if (array_key_exists($set->property->depends_on_property_id, $this->toUnset)) {
                    $this->toUnset[$set->property->depends_on_property_id][] = $set->property->id;
                } else {
                    $this->toUnset[$set->property->depends_on_property_id] = [$set->property->id];
                }
                if (false === array_key_exists($set->property->id, $depends)) {
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
            if (true === array_key_exists($prop_id, $urlParams['properties'])) {
                if (false === array_key_exists($key, $urlParams['properties'])) {
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
            ];
            $urlParams = $this->mergeUrlProperties($urlParams, Yii::$app->request->post('properties', []));
            $urlParams = $this->removeLostDependencies($filterSets, $urlParams);
            $properties = $urlParams['properties'];
            ksort($properties);
            $cacheKey = 'FilterSets:' . $categoryId . ':' . json_encode($properties);
            unset($properties);
            if (false === $filtersArray = Yii::$app->cache->get($cacheKey)) {
                $filtersArray = [];
                foreach ($filterSets as $filterSet) {
                    if ($filterSet->property->has_static_values === 0) {
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
                        $filterSet->multiple
                    );
                    if (count($selections) === 0) {
                        continue;
                    }
                    $item = [
                        'id' => $filterSet->property->id,
                        'name' => $filterSet->property->name,
                        'isRange' => $filterSet->is_range_slider,
                        'selections' => [],
                    ];
                    if ($filterSet->is_range_slider) {
                        $item['max'] = 0;
                        $item['min'] = PHP_INT_MAX;
                        $item['property'] = $filterSet->property;
                    }
                    foreach ($selections as $selection) {
                        if ($filterSet->is_range_slider) {
                            if ((int) $selection['value'] > $item['max']) {
                                $item['max'] = (int) $selection['value'];
                            }
                            if ((int) $selection['value'] < $item['min']) {
                                $item['min'] = (int) $selection['value'];
                            }
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
                                if (true === array_key_exists($filterSet->property_id, $this->toUnset)) {
                                    foreach ($this->toUnset[$filterSet->property_id] as $id) {
                                        if (array_key_exists($id, $routeParams['properties'])) {
                                            unset($routeParams['properties'][$id]);
                                        }
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
                                'url' => Url::toRoute($routeParams),
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
                        $item['step'] = $i > 3 ? (int) pow(10, $i - 3) : 1;
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
                $this->viewFile,
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
