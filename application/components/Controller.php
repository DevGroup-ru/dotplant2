<?php

namespace app\components;

use Yii;
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
        if (Yii::$app->response->view_id !== null) {
            $view = \app\models\View::getViewById(Yii::$app->response->view_id);
            if (!is_null($view)){
                return $view === 'default' ? $defaultView : $view;
            }
        }
        if (is_null($model)) {
            return $defaultView;
        }
        do {
            $view = ViewObject::getViewByModel($model);
            if (!is_null($view)) {
                return $view === 'default' ? $defaultView : $view;
            }
            $model = $model->parent;
        } while (!is_null($model));
        return $defaultView;
    }

    /**
     * @inheritdoc
     */
    public function render($view, $params = [])
    {
        if (!empty(Yii::$app->response->title)) {
            $this->view->title = Yii::$app->response->title;
        }


        foreach (Yii::$app->response->blocks as $block_name=>$value) {

            $this->view->blocks[$block_name] = $value;

        }

        if (!empty(Yii::$app->response->meta_description)) {
            $this->view->registerMetaTag(
                [
                    'name' => 'description',
                    'content' => Yii::$app->response->meta_description,
                ],
                'meta_description'
            );
        }

        return parent::render($view, $params);
    }
}
