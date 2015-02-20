<?php

namespace app\backend\actions;

use app;
use yii;
use yii\base\Action;
use yii\base\InvalidConfigException;

/**
 * Action for deletion one model from backend grid
 * @package app\backend\actions
 */
class DeleteOne extends Action
{
    /**
     * @var string Model name, ie. `Product::className()`
     */
    public $modelName = null;

    /**
     * @var bool Mark as deleted instead of calling `delete()`
     */
    public $markAsDeleted = false;

    /**
     * @var string Attribute that stores deleted state
     */
    public $deletedMarkAttribute = 'is_active';

    /**
     * @var mixed Deleted state value(ie. 1 for is_deleted or 0 for is_active)
     */
    public $deletedMarkValue = 0;

    /**
     * @var array Route to redirect after deletion
     */
    public $redirect = ['index'];

    public function init()
    {
        if (!isset($this->modelName)) {
            throw new InvalidConfigException("Model name should be set in controller actions");
        }
        if (!class_exists($this->modelName)) {
            throw new InvalidConfigException("Model class does not exists");
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $modelName = $this->modelName;
        $id = Yii::$app->request->get('id', null);
        if (!empty($id)) {
            /** @var \yii\db\ActiveRecord $modelName fake type for PHPStorm (: */
            $item = $modelName::findOne($id);
            if ($item === null) {
                throw new yii\web\NotFoundHttpException;
            }

            if ($this->markAsDeleted === true) {
                $item->setAttribute($this->deletedMarkAttribute, $this->deletedMarkValue);
                $item->save();
            } else {
                $item->delete();
            }

        }

        Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        return $this->controller->redirect($this->redirect);
    }
}
