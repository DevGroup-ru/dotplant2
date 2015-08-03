<?php

namespace app\components;

use Yii;

class Menu extends \yii\widgets\Menu
{
    /**
     * @inheritdoc
     * @param array $item
     * @return bool
     */
    protected function isItemActive($item)
    {
        if (is_string($item['url'])) {
            return $item['url'] === Yii::$app->request->url;
        } else {
            return parent::isItemActive($item);
        }
    }
}