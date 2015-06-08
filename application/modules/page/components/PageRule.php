<?php

namespace app\modules\page\components;

use app\modules\page\models\Page;
use Yii;
use yii\web\UrlRuleInterface;

class PageRule implements UrlRuleInterface
{
    /**
     * @inheritdoc
     */
    public function createUrl($manager, $route, $params)
    {
        /** @var Page $model */
        if ($route == 'page/page/show' || $route == 'page/page/list') {
            $model=null;
            if (isset($params['model'])) {
                $model = $params['model'];
                unset($params['model']);
            } else {
                if (isset($params['id'])) {
                    $model = Page::findById($params['id']);
                    unset($params['id']);
                }
            }
            if (null !== $model) {
                $url = ($model->slug_compiled === ':mainpage:') ? '' : $model->slug_compiled;
                $_query = http_build_query($params);
                $url = (!empty($_query)) ? $url . '?' . $_query : $url;
                return $url;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function parseRequest($manager, $request)
    {
        if ($request->serverName == Yii::$app->getModule('core')->serverName) {
            $_path = $request->getPathInfo();
        } else {
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
