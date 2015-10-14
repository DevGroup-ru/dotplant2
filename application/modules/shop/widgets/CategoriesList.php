<?php

namespace app\modules\shop\widgets;

use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\base\Widget;
use yii\caching\TagDependency;
use yii\db\Query;
use yii\helpers\Url;

class CategoriesList extends Widget
{
    /**
     * @property string $viewFile
     * @property int $rootCategory
     * @property int|null $depth
     * @property boolean $includeRoot
     * @property boolean $fetchModels
     * @property boolean $onlyNonEmpty
     * @property array $excludedCategories
     * @property array $additional
     */
    public $viewFile = 'categories-list/tree';
    public $rootCategory = null;
    public $depth = null;
    public $includeRoot = false;
    public $fetchModels = false;
    public $onlyNonEmpty = false;
    public $excludedCategories = [];
    public $additional = [];
    public $activeClass = '';
    public $activateParents = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (false === is_array($this->excludedCategories)) {
            $this->excludedCategories = [];
        }
        array_walk($this->excludedCategories, function(&$value, $key) {
            $value = intval($value);
        });
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

        $cacheKey = $this->className() . ':' . implode('_', [
            $this->viewFile,
            $this->rootCategory,
            null === $this->depth ? 'null' : intval($this->depth),
            intval($this->includeRoot),
            intval($this->fetchModels),
            intval($this->onlyNonEmpty),
            implode(',', $this->excludedCategories),
            \Yii::$app->request->url
        ]) . ':' . json_encode($this->additional);

        if (false !== $cache = \Yii::$app->cache->get($cacheKey)) {
            return $cache;
        }

        /** @var array|Category[] $tree */
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

        if (true === $this->onlyNonEmpty) {
            $_sq1 = (new Query())->select('main_category_id')
                ->distinct()
                ->from(Product::tableName());
            $_sq2 = (new Query())->select('category_id')
                ->distinct()
                ->from('{{%product_category}}');
            $_query = (new Query())->select('id')
                ->from(Category::tableName())
                ->andWhere(['not in', 'id', $_sq1])
                ->andWhere(['not in', 'id', $_sq2])
                ->all();

            $this->excludedCategories = array_merge(
                $this->excludedCategories,
                array_column($_query, 'id')
            );
        }
        $tree = $this->filterTree($tree);

        $cache = $this->render($this->viewFile, [
            'tree' => $tree,
            'fetchModels' => $this->fetchModels,
            'additional' => $this->additional,
            'activeClass' => $this->activeClass,
            'activateParents' => $this->activateParents,
        ]);

        \Yii::$app->cache->set(
            $cacheKey,
            $cache,
            0,
            new TagDependency([
                'tags' => [
                    ActiveRecordHelper::getCommonTag(Category::className()),
                    ActiveRecordHelper::getCommonTag(Product::className()),
                ],
            ])
        );

        return $cache;
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
                if ($item['url'] === \Yii::$app->request->url) {
                    $item['active'] = true;
                }
                $item['items'] = empty($item['items']) ? $item['items'] : $this->filterTree($item['items']);
                $result[] = $item;
                return $result;
            },
        []);
    }
}