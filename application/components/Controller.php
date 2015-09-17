<?php

namespace app\components;

use Yii;
use app\models\ViewObject;
use app\modules\core\events\ViewEvent;
use yii\web\ServerErrorHttpException;

/**
 * Class Controller extends default \yii\web\Controller adding some additional functions
 * @package app\components
 */
class Controller extends \yii\web\Controller
{
    const EVENT_PRE_DECORATOR = 'pre-decorator';
    const EVENT_POST_DECORATOR = 'post-decorator';

    protected function renderDecorator($methodName, $view, $params = [])
    {
        if (!empty(Yii::$app->response->title)) {
            $this->view->title = Yii::$app->response->title;
        }
        foreach (Yii::$app->response->blocks as $block_name => $value) {
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
        $preDecoratorEvent = new ViewEvent();
        $preDecoratorEvent->viewFile = $view;
        $preDecoratorEvent->params = &$params;
        $preDecoratorEvent->blocks = &$this->view->blocks;
        $this->trigger(self::EVENT_PRE_DECORATOR, $preDecoratorEvent);
        if ($preDecoratorEvent->isValid === true) {
            $content = $this->getView()->{$methodName}($view, $preDecoratorEvent->params, $this);
            $postDecoratorEvent = new ViewEvent();
            $postDecoratorEvent->viewFile = $view;
            $postDecoratorEvent->params = &$preDecoratorEvent->params;
            $postDecoratorEvent->output = &$content;
            $postDecoratorEvent->blocks = &$this->view->blocks;
            $this->trigger(self::EVENT_POST_DECORATOR, $postDecoratorEvent);
            if ($postDecoratorEvent->isValid === true) {
                return $methodName === 'render'
                    ? $this->renderContent($postDecoratorEvent->output)
                    : $postDecoratorEvent->output;
            }
        }
        throw new ServerErrorHttpException("Error rendering output");
    }

    /**
     * @param \yii\db\ActiveRecord $model
     * @param string $defaultView
     * @return string
     */
    public function computeViewFile($model, $defaultView = '')
    {
        if (Yii::$app->response->view_id !== null) {
            $view = \app\models\View::getViewById(Yii::$app->response->view_id);
            if (!is_null($view)) {
                if ($view === 'default') {
                    $view =  ViewObject::getViewByModel($model->parent);
                }
                return $view === null ? $defaultView : $view;
            }
        }
        if (is_null($model)) {
            return $defaultView;
        }

        do {
            $view = ViewObject::getViewByModel($model);
            if (is_null($view) || $view == 'default') {
                $view = ViewObject::getViewByModel($model->parent, true);
            }
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
        return $this->renderDecorator('render', $view, $params);
    }

    /**
     * @inheritdoc
     */
    public function renderAjax($view, $params = [])
    {
        return $this->renderDecorator('renderAjax', $view, $params);
    }
}
