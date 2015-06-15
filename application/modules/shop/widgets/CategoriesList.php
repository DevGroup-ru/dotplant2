<?php

namespace app\modules\shop\widgets;

use app\modules\shop\models\Category;
use yii\base\Widget;
use yii\helpers\Url;

class CategoriesList extends Widget
{
    public $viewFile = 'categories-list/tree';
    public $rootCategory = null;
    public $depth = null;
    public $includeRoot = false;
    public $fetchModels = false;
    public $excludedCategories = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (false === is_array($this->excludedCategories)) {
            $this->excludedCategories = [];
        }
        array_unique($this->excludedCategories);
    }

    /**
     * @return string
     */
    public function run()
    {
        parent::run();

        if (null === $this->rootCategory) {
            return '';
        }

        /** @var array $tree */
        $tree = Category::getMenuItems(intval($this->rootCategory), $this->depth, boolval($this->fetchModels));

        if (true === $this->includeRoot) {
            if (null !== $_root = Category::findById(intval($this->rootCategory))) {
                $tree = [[
                    'label' => $_root->name,
                    'url' => Url::toRoute(
                        [
                            '@category',
                            'category_group_id' => $_root->category_group_id,
                            'last_category_id' => $_root->id,
                        ]
                    ),
                    'id' => $_root->id,
                    'model' => $this->fetchModels ? $_root : null,
                    'items' => $tree,
                ]];
            }
        }

        $tree = $this->filterTree($tree);

        return $this->render($this->viewFile, [
            'tree' => $tree,
            'fetchModels' => $this->fetchModels,
        ]);
    }

    /**
     * @param array $input
     * @return array
     */
    private function filterTree($input = [])
    {
        return array_reduce($input,
            function ($result, $item)
            {
                if (in_array($item['id'], $this->excludedCategories)) {
                    return $result;
                }
                $item['items'] = empty($item['items']) ? $item['items'] : $this->filterTree($item['items']);
                $result[] = $item;
                return $result;
            },
        []);
    }
}