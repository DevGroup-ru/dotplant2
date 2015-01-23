<?php

namespace app\widgets\navigation;

use app\widgets\navigation\models\Navigation;
use yii\base\Widget;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\Menu;

class NavigationWidget extends Widget
{
    public $prependItems;
    public $appendItems;
    public $options;
    public $rootId = 1;
    public $useCache = true;
    public $viewFile = 'navigation';
    public $widget = '';

    public function init()
    {
        if (!trim($this->widget)) {
            $this->widget = Menu::className();
        }
        if (!is_array($this->options)) {
            $this->options = [];
        }
        Html::addCssClass($this->options, 'navigation-widget');
    }

    public function run()
    {
        $items = null;
        $cacheKey = implode(
            ':',
            [
                'Navigation',
                $this->rootId,
                $this->viewFile
            ]
        );
        if ($this->useCache) {
            if (false === $items = \Yii::$app->cache->get($cacheKey)) {
                $items = null;
            }
        }
        if (null === $items) {
            $root = Navigation::findOne($this->rootId);
            $items = [];
            foreach ($root->children as $child) {
                $items[] = self::getTree($child);
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

        return $this->render(
            $this->viewFile,
            [
                'widget' => $this->widget,
                'items' => ArrayHelper::merge((array) $this->prependItems, $items, (array) $this->appendItems),
                'options' => $this->options,
            ]
        );
    }

    /**
     * @param Navigation $model
     * @return array
     */
    private static function getTree($model)
    {
        if (trim($model->url)) {
            $url = trim($model->url);
        } else {
            $params = (trim($model->route_params)) ? Json::decode($model->route_params) : [];
            $url = ArrayHelper::merge([$model->route], $params);
        }
        $tree = [
            'label' => $model->name,
            'url' => \Yii::$app->request->baseUrl.$url,
            'options' => ['class' => $model->advanced_css_class],
            'items' => [],
        ];
        foreach ($model->children as $child) {
            $tree['items'][] = self::getTree($child);
        }
        return $tree;
    }
}
