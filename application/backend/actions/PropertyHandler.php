<?php

namespace app\backend\actions;

use app\models\Object;
use app\models\Property;
use app\properties\PropertyHandlers;
use yii;
use yii\base\Action;

class PropertyHandler extends Action
{
    public $modelName = null;
    public $objectId = null;

    /**
     * @throws yii\web\ServerErrorHttpException
     */
    public function init()
    {
        parent::init();
        if (null === $this->modelName) {
            throw new yii\web\ServerErrorHttpException('Model name should be set in controller actions');
        }

        if (!is_subclass_of($this->modelName, '\yii\db\ActiveRecord')) {
            throw new yii\web\ServerErrorHttpException('Model class does not exists');
        }

        $this->objectId = Object::getForClass($this->modelName);
        if (null === $this->objectId) {
            throw new yii\web\ServerErrorHttpException('Object does not exists for model.');
        }
    }

    /**
     * @param null $property_id
     * @param null $handler_action
     * @param null $model_id
     * @return mixed
     */
    public function run($property_id = null, $handler_action = null, $model_id = null)
    {
        if (null === $handler_action
            || null === $property_id
            || null === $model_id)
        {
            return '';
        }

        $property = Property::findById($property_id);
        if (null === $property) {
            return '';
        }

        $actionParams = [
            'model_name' => $this->modelName,
            'model_id' => $model_id,
            'object_id' => $this->objectId,
            'property' => $property,
        ];
        $propertyHandler = PropertyHandlers::createHandler($property->handler);
        return $propertyHandler->runAction($handler_action, $actionParams);
    }
}
