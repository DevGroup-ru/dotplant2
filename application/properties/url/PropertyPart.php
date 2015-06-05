<?php

namespace app\properties\url;

use app\models\Property;
use app\models\PropertyStaticValues;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;

class PropertyPart extends UrlPart
{
    public $include_if_value = [];
    public $optional = true;
    public $property_id = null;

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

    /**
     * @inheritdoc
     */
    public function getNextPart(
        $full_url,
        $next_part,
        &$previous_parts
    ) {
        $property = Property::findById($this->property_id);
        if (is_null($property)) {
            return false;
        }
        if ($property->has_static_values && $property->has_slugs_in_values) {
            $cacheTags = [];
            $static_values = PropertyStaticValues::getValuesForPropertyId($this->property_id);
            $slugs = explode('/', $next_part);
            foreach ($static_values as $value) {
                if ($slugs[0] === $value['slug']) {
                    $this->parameters = [
                        'properties' => [
                            $this->property_id => [$value['id']],
                        ],
                    ];
                    if (!empty($value['title_append'])) {
                        $this->parameters['title_append'] = [$value['title_append']];
                    }
                    $cacheTags[] = ActiveRecordHelper::getObjectTag(PropertyStaticValues::className(), $value['id']);
                    $part = new self([
                        'gathered_part' => $value['slug'],
                        'rest_part' => mb_substr($next_part, mb_strlen($value['slug'])),
                        'parameters' => $this->parameters,
                        'cacheTags' => $cacheTags,
                    ]);

                    return $part;
                }
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function appendPart($route, $parameters = [], &$used_params = [], &$cacheTags = [])
    {
        if (isset($parameters['properties'])) {
            $used_params[] = 'properties';
            if (isset($parameters['properties'][$this->property_id]) &&
                is_array($parameters['properties'][$this->property_id])
            ) {
                $psv = PropertyStaticValues::findById($parameters['properties'][$this->property_id][0]);
                if (count($this->include_if_value) > 0) {
                    if (!in_array($psv['value'], $this->include_if_value)) {
                        return false;
                    }
                }
                if (is_array($psv)) {
                    $cacheTags[] = ActiveRecordHelper::getObjectTag(PropertyStaticValues::className(), $psv['id']);
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
}
