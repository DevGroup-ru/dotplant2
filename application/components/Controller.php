<?php

namespace app\components;

use app\models\ViewObject;

class Controller extends \yii\web\Controller
{
    /**
     * @param \yii\db\ActiveRecord $model
     * @param string $defaultView
     * @return string
     */
    public function computeViewFile($model, $defaultView = '')
    {
        if (is_null($model)) {
            return $defaultView;
        }
        do {
            $view = ViewObject::getViewByModel($model);
            if (!is_null($view)) {
                return $view == 'default' ? $defaultView : $view;
            }
            $model = $model->parent;
        } while (!is_null($model));
        return $defaultView;
    }
}
