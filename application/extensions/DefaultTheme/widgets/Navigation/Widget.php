<?php
/**
 * Created by PhpStorm.
 * User: bethrezen
 * Date: 15.06.15
 * Time: 14:55
 */

namespace app\extensions\DefaultTheme\widgets\Navigation;

use Yii;
use app\extensions\DefaultTheme\components\BaseWidget;

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
            'navigation',
            [
                'rootNavigationId' => $this->rootNavigationId,
            ]
        );
    }
}