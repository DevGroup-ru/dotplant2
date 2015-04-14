<?php

namespace app\properties\handlers\dummy;

use app\properties\handlers\AbstractHandler;

class DummyHandler extends AbstractHandler
{
    /*
     *
     */
    function __construct(\app\models\PropertyHandler $propertyHandler)
    {
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
        return '';
    }
}
?>