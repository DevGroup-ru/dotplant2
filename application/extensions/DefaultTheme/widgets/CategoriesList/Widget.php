<?php

namespace app\extensions\DefaultTheme\widgets\CategoriesList;

use Yii;
use app\extensions\DefaultTheme\components\BaseWidget;

class Widget extends BaseWidget
{
    public $type = 'plain';
    public $root_category_id = 1;
    public $category_group_id = 1;

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
                'root_category_id' => $this->root_category_id,
                'category_group_id' => $this->category_group_id,
            ]
        );
    }
}