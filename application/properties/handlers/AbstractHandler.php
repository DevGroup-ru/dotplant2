<?php

namespace app\properties\handlers;

abstract class AbstractHandler
{
    /** @var \app\models\PropertyHandler $propertyHandler */
    protected $propertyHandler;
    /** @var \yii\base\Widget $widgetClass */
    protected $widgetClass = null;
    protected $additionalRenderData = [];

    /**
     * @param \app\models\PropertyHandler $propertyHandler
     */
    function __construct(\app\models\PropertyHandler $propertyHandler)
    {
        $this->propertyHandler = $propertyHandler;

        $widgetClass = !empty($this->widgetClass) ? $this->widgetClass : get_called_class().'Widget';
        if (class_exists($widgetClass) && is_subclass_of($widgetClass, '\app\properties\handlers\AbstractHandlerWidget')) {
            $this->widgetClass = $widgetClass;
        } else {
            $this->widgetClass = null;
        }

        $this->init();
    }

    /**
     * Initialize instance
     */
    public function init()
    {
    }

    /**
     * @param \app\models\Property $property
     * @return bool
     */
    public function changePropertyType(\app\models\Property &$property)
    {
        return false;
    }

    /**
     * @param $property
     * @param $model
     * @param $values
     * @param $form
     * @param $renderType
     * @return string
     */
    public function render($property, $model, $values, $form, $renderType)
    {
        if (!empty($this->widgetClass)) {
            $widgetClass = $this->widgetClass;
            $renderType = isset($this->propertyHandler->$renderType) ? $this->propertyHandler->$renderType : $this->propertyHandler->frontend_render_view;
            return $widgetClass::widget([
                'values' => $values,
                'form' => $form,
                'model' => $model,
                'property_key' => $property->key,
                'property_id' => $property->id,
                'label' => $property->name,
                'multiple' => $property->multiple,
                'viewFile' => $renderType,
                'additional' => $this->additionalRenderData,
            ]);
        }

        return '';
    }

    /**
     * @param \app\models\Property $property
     * @param string $formProperties
     * @param array $values
     * @return array
     */
    public function processValues(\app\models\Property $property, $formProperties = '', $values = [])
    {
        return $values;
    }

    /**
     * @param string|null $action Method of handler
     * @param array $params
     * @return mixed|string
     */
    public function runAction($action = null, $params = [])
    {
        if (preg_match('#^[a-z0-9\\-_]+$#', $action) && strpos($action, '--') === false && trim($action, '-') === $action) {
            $methodName = 'action' . str_replace(' ', '', ucwords(implode(' ', explode('-', $action))));
            if (method_exists($this, $methodName)) {
                $method = new \ReflectionMethod($this, $methodName);
                if ($method->isPublic() && $method->getName() === $methodName) {
                    return call_user_func([$this, $methodName], $params);
                }
            }
        }

        return '';
    }
}
?>