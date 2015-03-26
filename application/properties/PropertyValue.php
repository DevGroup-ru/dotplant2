<?php

namespace app\properties;

use Yii;

class PropertyValue
{
    public $object_id;
    public $object_model_id;
    public $property_id;
    public $property_group_id;
    public $values = [];

    public function __construct($values, $property_id, $object_id, $object_model_id, $property_group_id = null)
    {
        $this->values = $values;
        $this->object_id = $object_id;
        $this->object_model_id = $object_model_id;
        $this->property_id = $property_id;
        $this->property_group_id = $property_group_id;
    }

    public function __toString()
    {
        $actual_values = [];
        foreach ($this->values as $val) {
            $actual_values[] = $val['value'];
        }
        return implode(", ", $actual_values);
    }

    public function toValue($arrayMode=false)
    {
        $actual_values = [];
        foreach ($this->values as $val) {
            if (isset($val['psv_id'])) {
                $actual_values[] = $val['psv_id'];
            } else {
                $actual_values[] = $val['value'];
            }
        }
        if ($arrayMode === true) {
            return $actual_values;
        } else {
            return implode(", ", $actual_values);
        }
    }
}
