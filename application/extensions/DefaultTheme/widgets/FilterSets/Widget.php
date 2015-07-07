<?php

namespace app\extensions\DefaultTheme\widgets\FilterSets;

use app\modules\shop\models\FilterSets;
use Yii;
use app\extensions\DefaultTheme\components\BaseWidget;
use app\models\PropertyStaticValues;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class Widget extends BaseWidget
{
    public $viewFile = 'filter-sets';
    public $hideEmpty = true;
    public $usePjax = true;

    protected function getSelectedPropertyIndex($properties, $propertyId, $propertyValueId)
    {
        if (!isset($properties[$propertyId])) {
            return false;
        }
        return array_search($propertyValueId, $properties[$propertyId]);
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
        $urlParams = array_merge(
            [
                '@category',
                'last_category_id' => 1,
                'properties' => []
            ],
            array_merge(
                Yii::$app->request->get(),
                Yii::$app->request->post()
            )
        );
        $filtersArray = [];
        foreach ($filterSets as $filterSet) {
            if ($filterSet->property->has_static_values === 0) {
                continue;
            }
            $selections = PropertyStaticValues::getValuesForFilter(
                $filterSet->property->id,
                $urlParams['last_category_id'],
                $urlParams['properties']
            );
            if (count($selections) === 0) {
                continue;
            }
            $item = [
                'id' => $filterSet->property->id,
                'name' => $filterSet->property->name,
                'isRange' => $filterSet->is_range_slider,
                'min' => 0,
                'max' => 0,
                'selections' => [],
            ];
            foreach ($selections as $selection) {
                if ($filterSet->is_range_slider) {
                    // @todo set min and max values for this properties
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
                        $routeParams = ArrayHelper::merge(
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
            $filtersArray[] = $item;
            unset($item);
        }
//        header('Content-Type: text/plain; charset=utf-8'); print_r($filtersArray); exit;
        // End of a new filter sets implementation

        return $this->render(
            $this->viewFile,
            [
                'filtersArray' => $filtersArray,
                'filterSets' => $filterSets,
                'id' => 'filter-set-' . $this->getId(),
                'hideEmpty' => $this->hideEmpty,
                'urlParams' => $urlParams,
            ]
        );
    }
}
