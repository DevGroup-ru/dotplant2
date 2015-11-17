<?php
namespace app\modules\shop\events\yml;

use yii\base\Event;

class YmlOffersEvent extends Event
{
    private $offers = [];

    /**
     * @return array
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * @param array $offers
     * @return YmlOffersEvent
     */
    public function setOffers($offers)
    {
        if (true === is_array($offers)) {
            $this->offers = $offers;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function clearHandled()
    {
        $this->handled = false;
        return $this;
    }
}
