<?php

namespace app\extensions\DefaultTheme\widgets\PagesList;

use app\modules\page\models\Page;
use app\extensions\DefaultTheme\components\BaseWidget;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\caching\TagDependency;

class Widget extends BaseWidget
{
    public $limit = 5;

    public $more_pages_label = '';
    public $parent_id = null;
    public $view_file = 'pages-list';
    public $order_by = 'date_added';
    public $order = SORT_DESC;
    public $display_date = false;
    public $date_format = 'd.m.Y';

    /**
     * Actual run function for all widget classes extending BaseWidget
     * @return mixed
     */
    public function widgetRun()
    {
        $pages = Page::getDb()->cache(
            function ($db) {
                return Page::find()->where(['parent_id' => $this->parent_id, 'published' => 1])->orderBy(
                        [$this->order_by => $this->order]
                    )->limit($this->limit)->all();
            },
            86400,
            new TagDependency(
                [
                    'tags' => [
                        ActiveRecordHelper::getObjectTag(Page::className(), $this->parent_id)
                    ]
                ]
            )
        );

        return $this->render(
            $this->view_file,
            [
                'pages' => $pages,
                'parent_id' => $this->parent_id,
                'more_pages_label' => $this->more_pages_label,
                'display_date' => $this->display_date,
                'date_format' => $this->date_format,
            ]
        );
    }
}