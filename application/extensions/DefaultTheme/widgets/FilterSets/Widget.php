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
    public $viewFile = 'filter-sets';
    public $hideEmpty = true;
    public $usePjax = true;
    public $useNewFilter = false;

    protected function getSelectedPropertyIndex($properties, $propertyId, $propertyValueId)
    {
        if (!isset($properties[$propertyId])) {
            return false;
        }
        return array_search($propertyValueId, $properties[$propertyId]);
    }

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
            // @todo Need to add caching
            $urlParams = array_merge(
                [
                    '@category',
                    'last_category_id' => $categoryId,
                    'properties' => []
                ],
                array_merge(
                    Yii::$app->request->get(),
                    Yii::$app->request->post()
                )
            );
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
                        if ($this->getSelectedPropertyIndex(
                            $urlParams['properties'],
                            $filterSet->property->depends_on_property_id,
                            $filterSet->property->depended_property_values
                        )
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
