<?php

namespace app\extensions\DefaultTheme\widgets\OneRowHeaderWithCart;

use app\extensions\DefaultTheme\assets\BootstrapHoverDropdown;
use app\extensions\DefaultTheme\components\BaseWidget;
use app\modules\shop\models\Order;

class Widget extends BaseWidget
{
    public $collapseOnSmallScreen = true;
    /**
     * Actual run function for all widget classes extending BaseWidget
     *
     * @return mixed
     */
    public function widgetRun()
    {
        // this header needs this plugin
        BootstrapHoverDropdown::register($this->view);

        $order = Order::getOrder(false);

        return $this->render(
            'header',
            [
                'order' => $order,
                'collapseOnSmallScreen' => $this->collapseOnSmallScreen,
            ]
        );
    }
}