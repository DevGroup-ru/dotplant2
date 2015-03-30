<?php

namespace app\properties;

use app\properties\handlers\dummy\DummyHandler;

class PropertyHandlers
{
    static private $handlers = [];

    /**
     * @param \app\models\PropertyHandler $property_handler
     * @return handlers\AbstractHandler|DummyHandler
     */
    static public function createHandler(\app\models\PropertyHandler $property_handler)
    {
        if (!isset(static::$handlers[$property_handler->id])) {
            $handlerClass = $property_handler->handler_class_name;

            if (is_subclass_of($handlerClass, '\app\properties\handlers\AbstractHandler')) {
                /** @var $handler \app\properties\handlers\AbstractHandler */
                $handler = new $handlerClass($property_handler);
                static::$handlers[$property_handler->id] = $handler;

                return $handler;
            }
        } elseif (isset(static::$handlers[$property_handler->id])) {
            return static::$handlers[$property_handler->id];
        }

        return static::$handlers[$property_handler->id] = new DummyHandler($property_handler);
    }

    /**
     * @param null $id
     * @return null
     */
    static public function getHandlerById($id = null)
    {
        if (null === $id) {
            return null;
        }

        if (isset(static::$handlers[$id])) {
            return static::$handlers[$id];
        }

        return null;
    }
}
?>