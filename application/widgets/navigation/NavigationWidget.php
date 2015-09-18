<?php

namespace app\widgets\navigation;

use app\widgets\navigation\models\Navigation;
use Yii;
use yii\base\Widget;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use app\components\Menu;

class NavigationWidget extends Widget
{
    public $prependItems;
    public $appendItems;
    public $options;
    public $rootId = 1;
    public $depth = 99;
    public $useCache = true;
    public $viewFile = 'navigation';
    public $widget = '';
    public $linkTemplate = '<a href="{url}" title="{label}" itemprop="url"><span itemprop="name">{label}</span></a>';
    public $submenuTemplate = "\n<ul>\n{items}\n</ul>\n";

    public function init()
    {
        $schema = [
            'role'=> "navigation",
            'itemscope' => '',
            'itemtype' => "http://schema.org/SiteNavigationElement",

        ];
        if (!trim($this->widget)) {
            $this->widget = Menu::className();
        }
        if (!is_array($this->options)) {
            $this->options = $schema;
        } else {
            $this->options = ArrayHelper::merge($schema, $this->options);
        }
        Html::addCssClass($this->options, 'navigation-widget');
    }

    public function run()
    {
        Yii::beginProfile("NavigationWidget for ".$this->rootId);
        $items = null;
        $cacheKey = implode(
            ':',
            [
                'Navigation',
                $this->rootId,
                $this->depth,
                $this->viewFile
            ]
        );
        if ($this->useCache) {
            if (false === $items = \Yii::$app->cache->get($cacheKey)) {
                $items = null;
            }
        }
        if (null === $items) {
            $root = Navigation::find()
                ->where(['id' => $this->rootId])
                ->with('children')
                ->orderBy(['sort_order' => SORT_ASC])
                ->one();
            $items = [];
            if ($this->depth > 0) {
                foreach ($root->children as $child) {
                    $items[] = self::getTree($child, $this->depth - 1);
                }
            }
            if (count($items) > 0) {
                \Yii::$app->cache->set(
                    $cacheKey,
                    $items,
                    86400,
                    new TagDependency([
                        'tags' => [
                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(Navigation::className())
                        ]
                    ])
                );
            }
        }
        $items = ArrayHelper::merge((array) $this->prependItems, $items, (array) $this->appendItems);
        $currentUri = Yii::$app->request->url;
        array_walk($items, function(&$item) use ($currentUri) {
            if ($item['url'] === $currentUri) {
                $item['active'] = true;
            }
        });

        $result = $this->render(
            $this->viewFile,
            [
                'widget' => $this->widget,
                'items' => $items,
                'options' => $this->options,
                'linkTemplate' => $this->linkTemplate,
                'submenuTemplate' => $this->submenuTemplate,
            ]
        );
        Yii::endProfile("NavigationWidget for ".$this->rootId);
        return $result;
    }

    /**
     * @param Navigation $model
     * @param int $depth
     * @return array
     */
    private static function getTree($model, $depth)
    {
        if (trim($model->url)) {
            $url = trim($model->url);
        } else {
            $params = (trim($model->route_params)) ? Json::decode($model->route_params) : [];
            $url = ArrayHelper::merge([$model->route], $params);
        }
        $tree = [
            'model' => $model,
            'label' => $model->name,
            'url' => $url,
            'items' => [],
            'options' => [],
        ];
        if (!empty($model->advanced_css_class)) {
            $tree['options'] = ['class' => $model->advanced_css_class];
        }
        if ($depth > 0) {
            foreach ($model->children as $child) {
                $tree['items'][] = self::getTree($child, $depth - 1);
            }
        }
        return $tree;
    }
}
