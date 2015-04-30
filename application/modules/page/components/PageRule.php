<?php

namespace app\modules\page\components;

use app\models\Config;
use app\modules\page\models\Page;
use Yii;
use yii\web\UrlRuleInterface;

class PageRule implements UrlRuleInterface
{
    public function createUrl($manager, $route, $params)
    {
        if (isset($params['model'])) {
            $model = $params['model'];
            unset($params['model']);
        } else if (isset($params['id'])) {
            $model = Page::findById($params['id']);
            unset($params['id']);
        }
        if (($route == 'page/show' || $route == 'page/list') && null !== $model) {
            $url = ($model->slug_compiled === ':mainpage:') ? '' : $model->slug_compiled;
            $_query = http_build_query($params);
            $url = (!empty($_query)) ? $url.'?'.$_query : $url;
            return $url;
        }
        return false;
    }

    public function parseRequest($manager, $request)
    {
        if($request->serverName == Config::getValue('core.serverName', $request->serverName)){
            $_path = $request->getPathInfo();
        }else{
            $_path = $request->absoluteUrl;
        }
        $_path = !empty($_path) ? $_path : ':mainpage:';
        if (null !== $model = Page::getByUrlPath($_path)) {
            return [
                '/page/page/' . $model['show_type'],
                ['id' => $model['id']]
            ];
        }
        return false;
    }
}
