<?php

namespace app\slider;

use app;
use Yii;
use yii\base\Model;
use yii\helpers\Json;

/**
 * BaseSliderEditModel is used to serializing, unserializing, validating slider implementation-specific params.
 * @package app\slider
 */
class BaseSliderEditModel extends Model
{
    /**
     * @return string serialized model attributes omitting null attributes
     */
    public function serialize()
    {
        $attributes = $this->getAttributes();
        foreach ($attributes as $key=>$value) {
            if ($value === null) {
                unset($attributes[$key]);
            }
        }
        return Json::encode($attributes);
    }

    /**
     * Unserializes params and returns model
     * @param string $params
     * @return BaseSliderEditModel
     */
    public function unserialize($params)
    {
        $this->setAttributes(Json::decode($params), false);
        return $this;
    }
} 