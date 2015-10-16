<?php
namespace app\modules\shop\events;

use app\modules\shop\models\Order;
use yii\base\Event;

class CartActionEvent extends Event
{
    /**
     * @property array $eventData
     * @property array $products
     * @property Order $order
     */
    protected $eventData = [];
    private $products = [];
    private $order = null;

    /**
     * @inheritdoc
     */
    public function __construct(Order $order, $products, $config = [])
    {
        $this->order = $order;
        $this->products = is_array($products) ? $products : [];

        parent::__construct($config);
    }

    /**
     * @return array
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return array
     */
    public function getEventData()
    {
        return $this->eventData;
    }

    /**
     * @param array $eventData
     */
    public function setEventData($eventData)
    {
        $this->eventData = $eventData;
    }
}
