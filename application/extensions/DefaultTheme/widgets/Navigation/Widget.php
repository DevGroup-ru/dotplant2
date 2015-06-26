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
use yii\helpers\Json;

class Widget extends BaseWidget
{
    public $rootNavigationId = 1;
    public $depth = 99;
    public $viewFile = 'navigation';
    public $options = '{}';
    public $linkTemplate = '<a href="{url}" title="{label}" itemprop="url"><span itemprop="name">{label}</span></a>';
    public $submenuTemplate = "\n<ul>\n{items}\n</ul>\n";

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
                'depth' => $this->depth,
                'options' => Json::decode($this->options),
                'linkTemplate' => $this->linkTemplate,
                'submenuTemplate' => $this->submenuTemplate,
                'viewFile' => $this->viewFile,
            ]
        );
    }
}