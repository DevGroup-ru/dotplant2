<?php

namespace app\widgets\options;

use app\models\Property;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Выводит список комплектаций
 * или многомерные комплектации
 */
class ProductOption extends Widget
{

    /**
     * @var \app\modules\shop\models\Product
     */
    public $model;
    public $type = 'list';
    public $listView = 'listView';
    public $formView = 'formView';

    /**
     * Renders the widget.
     * @return string
     */
    public function run()
    {
        if ($this->type == 'list') {
            $items = [];
            foreach ($this->model->options as $option) {
                $items[] = $option->name;
            }
            return $this->render(
                $this->listView,
                [
                    'items' => $items,
                    'model' => $this->model,
                ]
            );
        } else {
            $propertyGroup = Json::decode($this->model->option_generate)['group'];
            $items = [];
            $optionsJson = [];
            foreach ($this->model->options as $option) {
                /**
                 * @var $option \app\modules\shop\models\Product|\app\properties\HasProperties
                 */
                $optionProperties = $option->getPropertyGroups()[$propertyGroup];
                $itemsJson = [];
                foreach ($optionProperties as $key => $property) {
                    foreach ($property->values as $name => $propValue) {
                        if (ArrayHelper::keyExists('property_id', $propValue) === false) {
                            continue;
                        }
                        if (!isset($items[$key])) {
                            $items[$key] = [
                                'name' => Property::findById($propValue['property_id'])->name,
                            ];
                        }
                        if (!isset($firstOption)) {
                            $firstOption[$key] = $propValue['psv_id'];
                        }
                        $items[$key]['values'][$propValue['psv_id']] = $propValue['name'];
                        $itemsJson[$key] = $propValue['psv_id'];
                    }
                }
                $optionsJson[] = [
                    'id' => $option->id,
                    'values' => $itemsJson,
                    'price' => $option->convertedPrice(),
                    'old_price' => $option->convertedPrice(null, true),
                ];
            }
            return $this->render(
                $this->formView,
                [
                    'model' => $this->model,
                    'items' => $items,
                    // @todo null
                    'optionsJson' => $optionsJson,
                ]
            );
        }
    }
}
