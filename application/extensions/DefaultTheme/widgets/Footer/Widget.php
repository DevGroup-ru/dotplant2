<?php

namespace app\extensions\DefaultTheme\widgets\Footer;

use Yii;
use app\extensions\DefaultTheme\assets\BootstrapHoverDropdown;
use app\extensions\DefaultTheme\components\BaseWidget;
use app\modules\shop\models\Order;
use yii\helpers\ArrayHelper;

class Widget extends BaseWidget
{
    public $rootNavigationId = 1;
    /**
     * Actual run function for all widget classes extending BaseWidget
     *
     * @return mixed
     */
    public function widgetRun()
    {
        return $this->render(
            'footer',
            [
                'rootNavigationId' => $this->rootNavigationId,
            ]
        );
    }
}