<?php

namespace app\widgets;

use app\modules\page\models\Page;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Widget;
use yii\caching\TagDependency;

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
     * @return string
     */
    public function run()
    {
        if ($this->model === null) {
            $this->model = Page::findById($this->parent_id);
        }
        if ($this->model === null) {
            return "<!-- can't render - model is null -->";
        }

        $cacheKey = 'PagesListWidget:'.$this->model->id.':limit:'.$this->limit;

        $children = Yii::$app->cache->get($cacheKey);
        if ($children === false) {
            $children = Page::find()
                ->where(['parent_id' => $this->model->id, 'published' => 1])
                ->orderBy(['date_added' => SORT_DESC]);
            if (null !== $this->limit) {
                $children->limit($this->limit);
            }
            $children = $children->all();
            Yii::$app->cache->set($cacheKey, $children, 86400, new TagDependency([
                'tags' => [
                    ActiveRecordHelper::getCommonTag(Page::className())
                ]
            ]));
        }

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