<?php

namespace app\properties\url;

use app\models\Property;
use app\models\PropertyStaticValues;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

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
     * Helper method to properly populate $this->params
     *
     * @param string $paramName
     * @param null $paramValue
     */
    private function addParam($paramName = "", $paramValue = null)
    {
        if (empty($paramName)) {
            throw new \InvalidArgumentException("Parameter name must be present");
        }

        if (isset($this->parameters[$paramName])) {
            $this->parameters[$paramName][] = $paramValue;
        } else {
            $this->parameters[$paramName] = [$paramValue];
        }
    }

    /**
     * @inheritdoc
     */
    public function getNextPart($full_url, $next_part, &$previous_parts)
    {
        $property = Property::findById($this->property_id);
        if (is_null($property)) {
            return false;
        }
        if ($property->has_static_values && $property->has_slugs_in_values) {
            $cacheTags = [];
            $static_values = PropertyStaticValues::getValuesForPropertyId($this->property_id);
            $slugs = explode('/', $next_part);
            $currentSlug = 0;
            $slugsCount = count($slugs);
            $appliedParts = [];
            foreach ($static_values as $value) {
                if ($slugs[$currentSlug] === $value['slug']) {
                    $appliedParts[] = $value['slug'];
                    if (isset($this->parameters['properties'][$this->property_id])) {
                        $this->parameters['properties'][$this->property_id][] = $value['id'];
                    } else {
                        $this->parameters = [
                            'properties' => [
                                $this->property_id => [$value['id']],
                            ],
                        ];
                    }
                    if (!empty($value['title_append'])) {
                        if ($value["title_prepend"] == 1) {
                            $this->addParam("title_prepend", $value["title_append"]);
                        } else {
                            $this->addParam("title_append", $value["title_append"]);
                        }
                    }
                    $cacheTags[] = ActiveRecordHelper::getObjectTag(PropertyStaticValues::className(), $value['id']);
                    $currentSlug++;
                    if ($currentSlug == $slugsCount) {
                        break;
                    }
                }
            }
            if ($currentSlug > 0) {
                $appliedPartsString = implode('/', $appliedParts);
                $part = new self(
                    [
                        'gathered_part' => $appliedPartsString,
                        'rest_part' => mb_substr($next_part, mb_strlen($appliedPartsString)),
                        'parameters' => $this->parameters,
                        'cacheTags' => $cacheTags,
                    ]
                );
                return $part;
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
            
            foreach ($parameters['properties'] as $id => $values) {
                if (count($values) > 1) {
                    return false;
                }
            }
            $used_params[] = 'properties';

            if (isset($parameters['properties'][$this->property_id]) &&
                is_array($parameters['properties'][$this->property_id])
            ) {
                $property_id = $this->property_id;
                $psvs = Yii::$app->db->cache(
                    function($db) use ($property_id, $parameters) {
                        $vals = array_values($parameters['properties'][$property_id]);
                        $vals = array_map(function($item){
                            return intval($item);
                        }, $vals);
                        return PropertyStaticValues::find()
                            ->where(['id' => $vals])
                            ->orderBy('sort_order ASC, name ASC')
                            ->asArray(true)
                            ->all();
                    },
                    86400, new TagDependency(['tags'=>[ActiveRecordHelper::getCommonTag(PropertyStaticValues::className())]])
                );

                foreach ($psvs as $psv) {
                    if (count($this->include_if_value) > 0) {
                        if (!in_array($psv['value'], $this->include_if_value)) {
                            return false;
                        }
                    }
                }
                if (!empty($psvs)) {
                    foreach ($psvs as $psv) {
                        $cacheTags[] = ActiveRecordHelper::getObjectTag(PropertyStaticValues::className(), $psv['id']);
                    }
                    return implode('/', ArrayHelper::getColumn($psvs, 'slug'));
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
