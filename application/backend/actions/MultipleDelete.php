<?php

namespace app\backend\actions;

use app;
use yii;
use yii\base\Action;
use yii\base\InvalidConfigException;

/**
 * Action for multiple deletion of models from backend grid
 * @package app\backend\actions
 */
class MultipleDelete extends Action
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
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            /** @var \yii\db\ActiveRecord $modelName fake type for PHPStorm (: */
            $items = $modelName::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                if ($this->markAsDeleted === true) {
                    $item->setAttribute($this->deletedMarkAttribute, $this->deletedMarkValue);
                    $item->save();
                } else {
                    $item->delete();
                }
            }
        }

        Yii::$app->session->setFlash('info', Yii::t('app', 'Objects deleted'));
        return $this->controller->redirect($this->redirect);
    }
}
