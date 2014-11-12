<?php

namespace app\properties\url;

use app\models\Property;
use app\models\PropertyStaticValues;
use Yii;

class PropertyPart extends UrlPart
{
    public $property_id = null;

    /**
     * Значения свойства, которые надо всегда включать в урл
     */
    public $include_if_value = [];

    /**
     * Обычно такие части урла опциональны
     * @todo Выставить это на обсуждение, ибо может вызвать путанницу
     */
    public $optional = true;

    public function getNextPart(
        $full_url,
        $next_part,
        &$previous_parts
    ) {

        $property = Property::findById($this->property_id);
        
        if ($property->has_static_values && $property->has_slugs_in_values) {
            $static_values = PropertyStaticValues::getValuesForPropertyId($this->property_id);

            // помни, друг, что здесь массивы, а не модели!
            foreach ($static_values as $value) {
                if (mb_strpos($next_part, $value['slug']) === 0) {
                    // мы нашли наш слаг
                    
                    // В параметры пишем массив с одним PropertyStaticValues.id
                    // Value писать малоперспективно
                    // массив, ибо в фильтре может быть несколько значений
                    // а дальше мы должны по идее мержить массивы

                    $this->parameters = [
                        'properties' => [
                            $this->property_id => [ $value['id'] ],
                        ],
                    ];
                    if (!empty($value['title_append'])) {
                        $this->parameters['title_append'] = [$value['title_append']];
                    }

                    $part = new self([
                        'gathered_part' => $value['slug'],
                        'rest_part' => mb_substr($next_part, mb_strlen($value['slug'])),
                        'parameters' => $this->parameters,
                    ]);

                    return $part;
                }
            }

            // мы ничего не нашли
            return false;

        } else {
            return false;
        }


    }

    public function appendPart($route, $parameters = [], &$used_params = [])
    {
        if (isset($parameters['properties'])) {
            $used_params[] = 'properties';
            if (isset($parameters['properties'][$this->property_id]) &&
                is_array($parameters['properties'][$this->property_id])
            ) {
                $psv = PropertyStaticValues::findById($parameters['properties'][$this->property_id][0]);
                if (count($this->include_if_value)>0) {
                    if (!in_array($psv['value'], $this->include_if_value)) {
                        return false;
                    }
                }
                if (is_array($psv)) {
                    return $psv['slug'];
                } else {
                    return false;
                }
            } else {
                return $this->checkIncludeIfValue();
            }
        } else {
            return $this->checkIncludeIfValue();

        }
    }

    private function checkIncludeIfValue()
    {
        if (is_object($this->model)) {
            $object_property_values = $this->model->getPropertyValuesByPropertyId($this->property_id);
            if (!is_object($object_property_values)) {
                return false;
            }
            foreach ($object_property_values->values as $val) {
                if (in_array($val['value'], $this->include_if_value)) {
                    return $val['slug'];
                    
                }
            }
        }
        return false;
    }
}
