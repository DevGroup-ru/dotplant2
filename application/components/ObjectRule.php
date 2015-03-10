<?php

namespace app\components;

use app\models\Category;
use app\models\Object;
use app\models\PrefilteredPages;
use app\models\Route;
use app\properties\url\StaticPart;
use Yii;
use yii\caching\TagDependency;
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
        if (isset($params['model'])) {
            $handler_model = $params['model'];
            $handler_object = Object::getForClass(get_class($handler_model));
            unset($params['model']);
        }
        foreach (ObjectRule::getRoutes() as $model) {
            $used_params = ['categories'];

            $break_rule = false;
            if ($route == $model->route) {
                $url_parts = [];
                $handlers = [];
                foreach ($model->template as $t) {
                    $h = Yii::createObject($t);
                    $h->model = $handler_model;
                    $h->object = $handler_object;
                    $handlers[] = $h;
                }
                foreach ($handlers as $handler) {
                    $new_part = $handler->appendPart($route, $params, $used_params);
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
                    return $url.((!empty($additionalParams)) ? "?$additionalParams" : '');
                }
            }
        }
        return false;  // this rule does not apply
    }

    public function parseRequest($manager, $request)
    {
        Yii::beginProfile("ObjectRule::parseRequest");
        
        $url = $request->getPathInfo();
        if (empty($url)) {
            Yii::endProfile("ObjectRule::parseRequest");
            return false;
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

            if (!empty($prefilteredPage['title'])) {
                Yii::$app->response->title = $prefilteredPage['title'];
            }
            $blocks = [
                'content',
                'announce',
                'breadcrumbs_label',
                'h1',
            ];

            foreach ($blocks as $block_name) {

                if (!empty($prefilteredPage[$block_name])) {
                    Yii::$app->response->blocks[$block_name] = $prefilteredPage[$block_name];
                }
            }
            Yii::$app->response->is_prefiltered_page = true;

            if ($prefilteredPage['view_id']>0) {
                Yii::$app->response->view_id = $prefilteredPage['view_id'];
            }

            return [
                'product/list',
                $params
            ];
        }

        $routes = ObjectRule::getRoutes();
        foreach ($routes as $model) {
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
                    // удалим leading slash
                    $next_part = preg_replace("#^/#Us", "", $result->rest_part);
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

                return [$model->route, $parameters];
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
