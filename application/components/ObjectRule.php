<?php

namespace app\components;

use app\modules\shop\models\Category;
use app\models\Object;
use app\models\PrefilteredPages;
use app\models\Route;
use app\properties\url\StaticPart;
use app\properties\url\UrlPart;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\UrlRuleInterface;

class ObjectRule implements UrlRuleInterface
{
    private static $routes = null;

    public static function canonical($params)
    {
        $params[0] = Yii::$app->controller->getRoute();
        return Url::to($params, true);
    }

    public function createUrl($manager, $route, $params)
    {

        $handler_model = null;
        $handler_object = null;

        $cacheKey = null;

        if (isset($params['model'])) {
            /** @var ActiveRecord $handler_model */
            $handler_model = $params['model'];
            unset($params['model']);
            $cacheKey = 'ObjectRule:'.$handler_model->tableName().':' . $handler_model->id . json_encode($params);
            $cached = Yii::$app->cache->get($cacheKey);
            if ($cached !== false) {
                return $cached;
            }

            $handler_object = Object::getForClass(get_class($handler_model));
        }

        $cacheTags = [];
        if (is_object($handler_model)) {
            $cacheTags[]=ActiveRecordHelper::getObjectTag($handler_model->className(), $handler_model->id);
        }
        foreach (ObjectRule::getRoutes() as $model) {
            $used_params = ['categories'];

            $break_rule = false;
            if ($route == $model->route) {
                $url_parts = [];
                /** @var UrlPart[] $handlers */
                $handlers = [];
                foreach ($model->template as $t) {
                    $h = Yii::createObject($t);
                    $h->model = $handler_model;
                    $h->object = $handler_object;
                    $handlers[] = $h;
                }
                foreach ($handlers as $handler) {
                    $new_part = $handler->appendPart($route, $params, $used_params, $cacheTags);
                    if ($handler instanceof StaticPart && $new_part === false) {
                        $break_rule = true;
                    }
                    if ($new_part !== false && !empty($new_part)) {
                        $url_parts[] = $new_part;
                    }
                }
                $url = implode("/", $url_parts);
                if (!empty($url) && $break_rule === false) {
                    $used_params = array_unique($used_params);
                    $allowed = array_filter(
                        array_keys($params),
                        function ($key) use ($used_params) {
                            return !in_array($key, $used_params);
                        }
                    );
                    $additionalParams = array_intersect_key($params, array_flip($allowed));
                    $additionalParams = (!empty($additionalParams)) ? http_build_query($additionalParams) : '';
                    $finalUrl = $url.((!empty($additionalParams)) ? "?$additionalParams" : '');

                    if (isset($cacheKey)) {
                        Yii::$app->cache->set(
                            $cacheKey,
                            $finalUrl,
                            86400,
                            new TagDependency([
                                'tags' => $cacheTags,
                            ])
                        );
                    }

                    return $finalUrl;
                }
                $cacheTags=[];
                if (is_object($handler_model)) {
                    $cacheTags[]=ActiveRecordHelper::getObjectTag($handler_model->className(), $handler_model->id);
                }
            }
        }
        return false;  // this rule does not apply
    }

    private function defineBlocksTitleAndView($data)
    {
        if (isset($data['blocks'])) {
            foreach ($data['blocks'] as $block_name=>$block_value) {
                Yii::$app->response->blocks[$block_name] = $block_value;
            }
        }
        if (isset($data['title'])) {
            Yii::$app->response->title = $data['title'];
        }
        if (isset($data['meta_description'])) {
            Yii::$app->response->meta_description = $data['meta_description'];
        }
        if (isset($data['viewId'])) {
            Yii::$app->response->view_id = $data['viewId'];
        }
        if (isset($data['is_prefiltered_page'])) {
            Yii::$app->response->is_prefiltered_page = true;
            Yii::$app->response->blocks['announce'] = '';
        }
    }

    public function parseRequest($manager, $request)
    {
        Yii::beginProfile("ObjectRule::parseRequest");

        $url = $request->getPathInfo();
        if (empty($url)) {
            Yii::endProfile("ObjectRule::parseRequest");
            return false;
        }

        $cacheKey = 'ObjectRule:'.$url.':'.Json::encode($request->getQueryParams());
        $result = Yii::$app->cache->get($cacheKey);
        if ($result !== false) {
            Yii::endProfile("ObjectRule::parseRequest");
            $this->defineBlocksTitleAndView($result);
            return $result['result'];
        }

        $prefilteredPage = PrefilteredPages::getActiveByUrl($url);

        if ($prefilteredPage !== null) {
            $params = [
                'properties' => Json::decode($prefilteredPage['params'])
            ];
            $category = Category::findById($prefilteredPage['last_category_id']);
            if ($category === null) {
                throw new NotFoundHttpException;
            }
            $params['category_group_id'] = $category->category_group_id;
            $params['last_category_id'] = $category->id;
            $data = ['blocks'=>[]];
            if (!empty($prefilteredPage['title'])) {
                $data['title'] = $prefilteredPage['title'];
            }
            if (!empty($prefilteredPage['meta_description'])) {
                $data['meta_description'] = $prefilteredPage['meta_description'];
            }
            $blocks = [
                'content',
                'announce',
                'breadcrumbs_label',
                'h1',
            ];

            foreach ($blocks as $block_name) {

                if (!empty($prefilteredPage[$block_name])) {
                    $data['blocks'][$block_name] = $prefilteredPage[$block_name];
                }
            }
            $data['is_prefiltered_page'] = true;

            if ($prefilteredPage['view_id']>0) {
                $data['viewId'] = $prefilteredPage['view_id'];
            }

            $data['result'] = [
                'shop/product/list',
                $params
            ];
            $this->defineBlocksTitleAndView($data);
            Yii::$app->cache->set(
                $cacheKey,
                $data,
                86400,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getObjectTag(PrefilteredPages::className(), $prefilteredPage['id']),
                        ActiveRecordHelper::getObjectTag(Category::className(), $category->id),
                    ]
                ])
            );
            return $data['result'];
        }

        $routes = ObjectRule::getRoutes();
        $cacheTags = [];
        foreach ($routes as $model) {
            /** @var UrlPart[] $handlers */
            $handlers = [];
            $object = Object::findById($model->object_id);
            foreach ($model->template as $t) {
                $handler = Yii::createObject($t);
                $handler->object = $object;
                $handlers[] = $handler;
            }
            $url_parts = [];
            $parameters = [];
            $next_part = $url;
            foreach ($handlers as $handler) {
                if (empty($next_part)) {
                    //break;
                }
                $result = $handler->getNextPart($url, $next_part, $url_parts);
                if ($result !== false && is_object($result) === true) {
                    $parameters = ArrayHelper::merge($parameters, $result->parameters);
                    $cacheTags = ArrayHelper::merge($cacheTags, $result->cacheTags);
                    // удалим leading slash
                    $next_part = ltrim($result->rest_part, '/');
                    $url_parts[] = $result;
                } elseif ($result === false && $handler->optional===false) {
                    continue;
                }
            }
            if (count($url_parts)==0) {
                continue;
            }

            // в конце удачного парсинга next_part должен остаться пустым
            if (empty($next_part)) {
                $resultForCache = ['result'=>[$model->route, $parameters]];
                if (isset($_POST['properties'], $parameters['properties'])) {

                    foreach ($_POST['properties'] as $key=>$value) {
                        if (isset($parameters['properties'][$key])) {
                            $parameters['properties'][$key] = array_unique(ArrayHelper::merge($parameters['properties'][$key], $value));
                        } else {
                            $parameters['properties'][$key] = array_unique($value);
                        }
                    }


                } elseif (isset($_POST['properties'])) {
                    $parameters['properties'] = $_POST['properties'];
                }
                Yii::endProfile("ObjectRule::parseRequest");
                if (isset($parameters['properties'])) {
                    foreach ($parameters['properties'] as $key => $values) {
                        foreach ($parameters['properties'][$key] as $index => $value) {
                            if ($value === '') {
                                unset($parameters['properties'][$key][$index]);
                            }
                        }
                        if (count($parameters['properties'][$key]) === 0) {
                            unset($parameters['properties'][$key]);
                        }
                    }
                }
                $result = [$model->route, $parameters];

                Yii::$app->cache->set(
                    $cacheKey,
                    $resultForCache,
                    86400,
                    new TagDependency([
                        'tags' => $cacheTags,
                    ])
                );

                return $result;
            }
        }
        Yii::endProfile("ObjectRule::parseRequest");
        return false;  // this rule does not apply
    }

    public static function getRoutes()
    {
        if (static::$routes === null) {
            $cacheKey = "Routes:all";
            static::$routes = Yii::$app->cache->get($cacheKey);
            if (!is_array(static::$routes)) {
                static::$routes = Route::find()->all();
                foreach (static::$routes as $key => $route) {
                    static::$routes[$key]['template'] = json_decode($route->url_template, true);
                }
                Yii::$app->cache->set(
                    $cacheKey,
                    static::$routes,
                    86400,
                    new TagDependency([
                        'tags' => [
                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(Route::className())
                        ]
                    ])
                );
            }
        }
        return static::$routes;
    }
}
