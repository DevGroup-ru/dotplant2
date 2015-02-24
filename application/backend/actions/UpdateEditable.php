<?php

namespace app\backend\actions;

use app;
use yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
/**
 * Universal action for editable updates
 *
 * How to use:
 *
 * Add to your controller's action:
 *
 * ```
 *
 * 'update-editable' => [
 *      'class' => UpdateEditable::className(),
 *      'modelName' => Product::className(),
 *      'allowedAttributes' => [
 *          'currency_id' => function(Product $model, $attribute) {
 *              if ($model === null || $model->currency === null || $model->currency_id ===0) {
 *                  return null;
 *              }
 *              return \yii\helpers\Html::tag('div', $model->currency->name, ['class' => $model->currency->name]);
 *          },
 *          'price',
 *          'old_price',
 *      ],
 *  ],
 *
 *
 * ```
 *
 * Allowed attributes is the array of attribute name as key and callable as value.
 * Callable is the function that returns the result of editable change.
 *
 * @package app\backend\actions
 */
class UpdateEditable extends Action
{
    /**
     * @var string Model name, ie. `Product::className()`
     */
    public $modelName = null;

    public $allowedAttributes = [];

    public function init()
    {
        if (!isset($this->modelName)) {
            throw new InvalidConfigException("Model name should be set in controller actions");
        }
        if (!class_exists($this->modelName)) {
            throw new InvalidConfigException("Model class does not exists");
        }

        $newAllowedAttributes = [];
        foreach ($this->allowedAttributes as $key => $value) {
            if (is_callable($value) === true) {
                $newAllowedAttributes[$key] = $value;

            } else {
                $newAllowedAttributes[$value] =
                    function(yii\db\ActiveRecord $model, $attribute) {
                        return $model->getAttribute($attribute);
                    };
            }
        }
        $this->allowedAttributes = $newAllowedAttributes;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        /** @var \yii\db\ActiveRecord $modelName fake type for PHPStorm (: */
        $modelName = $this->modelName;

        if (Yii::$app->request->post('hasEditable')) {
            $modelId = Yii::$app->request->post('editableKey');
            $model = $modelName::findOne($modelId);

            if ($model === null) {
                throw new yii\web\NotFoundHttpException;
            }

            $formName = $model->formName();

            $out = Json::encode(['output'=>'', 'message'=>'']);

            $post = [];
            $posted = current($_POST[$formName]);
            $post[$formName] = $posted;

            // load model like any single model validation
            if ($model->load($post)) {
                // can save model or do something before saving model
                $model->save();
                if ($model->hasMethod('invalidateTags')) {
                    $model->invalidateTags();
                }

                $output = '';


                foreach ($this->allowedAttributes as $attribute=>$callable) {
                    if (isset($posted[$attribute])) {
                        $output = call_user_func($callable, $model, $attribute);

                        break;
                    }
                }

                $out = Json::encode(['output'=>$output, 'message'=>'']);

            }
            echo $out;

        }
        return;
    }
}
