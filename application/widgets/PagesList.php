<?php

namespace app\widgets;

use app\modules\core\models\Page;
use Yii;
use yii\base\Widget;

class PagesList extends Widget
{
    public $limit = 5;
    public $model = null;
    public $more_pages_label = 'All news';
    public $parent_id = null;
    public $title = 'Catalogue';
    public $viewFile = 'pagesList';

    public function run()
    {
        if ($this->model === null) {
            $this->model = Page::findById($this->parent_id);
        }
        if ($this->model === null) {
            return "<!-- can't render - model is null -->";
        }
        $children = Page::find()
            ->where(['parent_id' => $this->model->id])
            ->orderBy('date_added DESC')
            ->limit($this->limit)
            ->all();
        return $this->render(
            $this->viewFile,
            [
                'model' => $this->model,
                'children' => $children,
                'more_pages_label' => $this->more_pages_label,
            ]
        );
    }
}
