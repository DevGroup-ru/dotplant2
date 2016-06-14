<?php

namespace app\extensions\DefaultTheme\widgets\CategoriesList;

use Yii;
use app\extensions\DefaultTheme\components\BaseWidget;

class Widget extends BaseWidget
{
    public $type = 'plain';
    public $rootCategoryId = 1;
    public $activeClass = '';
    public $activateParents = false;

    /**
     * Actual run function for all widget classes extending BaseWidget
     *
     * @return mixed
     */
    public function widgetRun()
    {
        if ($this->header === '') {
            $this->header = Yii::t('app', 'Catalog');
        }

        return $this->render(
            'categories-list',
            [
                'type' => $this->type,
                'rootCategoryId' => $this->rootCategoryId,
                'activeClass' => $this->activeClass,
                'activateParents' => $this->activateParents,
            ]
        );
    }
}