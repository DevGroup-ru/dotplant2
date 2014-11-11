<?php

namespace app\components;

use Yii;
use app\models\Page;
use yii\web\UrlRuleInterface;

class PageRule implements UrlRuleInterface
{
    public function createUrl($manager, $route, $params)
    {
        if (($route == 'page/show' || $route == 'page/list') && isset($params['id'])) {
            if (null !== $model = Page::findById($params['id'])) {
                $url = ($model->slug_compiled === ':mainpage:') ? '' : $model->slug_compiled;
                unset($params['id']);
                $_query = http_build_query($params);
                $url = (!empty($_query)) ? $url.'?'.$_query : $url;
                return $url;
            }
        }
        return false;
    }

    public function parseRequest($manager, $request)
    {
        $_path = $request->getPathInfo();
        $_path = !empty($_path) ? $_path : ':mainpage:';
        if (null !== $model = Page::getByUrlPath($_path)) {
            return [
                'page/' . $model['show_type'],
                ['id' => $model['id']]
            ];
        }
        return false;
    }
}
