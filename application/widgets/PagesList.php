<?php

namespace app\widgets;

use app\modules\page\models\Page;
use Yii;
use yii\base\Widget;

/**
 * Class PagesList renders pages list
 *
 * @deprecated Use PagesWidget from DefaultTheme
 * @package app\widgets
 */
class PagesList extends Widget
{
    public $limit = 5;
    public $model = null;
    public $more_pages_label = 'All news';
    public $parent_id = null;
    public $title = 'Catalogue';
    public $viewFile = 'pagesList';

    /**
     * @inheritdoc
     */
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
            ->orderBy('date_added DESC');
        if (null !== $this->limit) {
            $children->limit($this->limit);
        }
        $children = $children->all();

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
