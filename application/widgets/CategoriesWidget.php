<?php

namespace app\widgets;

use app\models\Category;
use Yii;
use yii\base\Widget;
use yii\helpers\Url;

class CategoriesWidget extends Widget
{
    private $possible_selections = null;
    public $category_group_id = null;
    public $current_selections = [];
    public $omit_root = true;
    public $route = '/product/list';
    public $title = 'Catalogue';
    public $viewFile = 'categoriesWidget';

    public function run()
    {
        Yii::beginProfile("CategoriesWidget - get possible selections");
        $this->getPossibleSelections();
        Yii::endProfile("CategoriesWidget - get possible selections");
        return $this->render(
            $this->viewFile,
            [
                'current_selections' => $this->current_selections,
                'possible_selections' => $this->possible_selections,
                'title' => $this->title,
                'route' => $this->route,
                'category_group_id' => $this->category_group_id,
            ]
        );
    }

    public function getPossibleSelections()
    {
        $this->possible_selections = [];
        $models = Category::getByLevel($this->category_group_id);
        if (isset($models[0]) && $this->omit_root == true) {
            $models = Category::getByParentId($models[0]->id);
        }
        $this->possible_selections = [];
        foreach ($models as $model) {
            $this->possible_selections [] = $this->recursiveGetTree($model);
        }
        return $this->possible_selections;
    }

    private function recursiveGetTree($model)
    {
        $params = [$this->route];
        $params += $this->current_selections;
        $params['category_group_id'] = $this->category_group_id;
        $params['last_category_id'] = $model->id;
        if (!isset($params['categories'])) {
            $params['categories'] = [];
        }

        $result = [
            'label' => $model->name,
            'url' => Url::to($params),
            'items' => [],
            'active' => in_array($model->id, $params['categories']),
            //'items' => $this->recursiveGetTree($model),
        ];
        $children = Category::getByParentId($model->id);

        foreach ($children as $child) {
            $result['items'][] = $this->recursiveGetTree($child);
        }
        return $result;
    }
}
