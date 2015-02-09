<?php

namespace app\traits;

use app\models\DynamicContent;
use Yii;
use yii\helpers\Json;

trait DynamicContentTrait
{
    public function loadDynamicContent($object_id, $route, $selections)
    {
        if (Yii::$app->response->is_prefiltered_page === true) {
            // DynamicContent should not work on prefiltered pages - all needed content is set in corresponding model
            return;
        }
        /**
         * @var $this \yii\web\Controller
         */
        $models = DynamicContent::find()
            ->where(
                [
                    'object_id' => $object_id,
                    'route' => $route,
                ]
            )->all();
        if (!isset($selections['properties'])) {
            $selections['properties'] = [];
        }
        /**
         * @var $model DynamicContent
         */
        foreach ($models as $model) {
            if ($model->apply_if_last_category_id) {
                if (!isset($selections['last_category_id'])) {
                    continue;
                } elseif ($selections['last_category_id'] != $model->apply_if_last_category_id) {
                    continue;
                }
            }
            $model_selections = Json::decode($model->apply_if_params);
            $matches = false;
            if (is_array($model_selections)) {
                $matches=true;
                
                foreach ($model_selections as $property_id => $value) {
                    if (isset($selections['properties'])) {
                        if (isset($selections['properties'][$property_id])) {
                            if ($selections['properties'][$property_id][0] == $value) {
                                // all ok
                            } else {
                                $matches = false;
                            }
                        } else {
                            $matches = false;
                            break;
                        }
                    } else {
                        $matches = false;
                        break;
                    }
                }
                if ($matches === false) {
                    continue;
                }
                if (count($selections['properties']) != count($model_selections)) {
                    $matches = false;
                }
                if ($matches === true) {
                    Yii::$app->response->dynamic_content_trait = true;
                    Yii::$app->response->matched_dynamic_content_trait_model = &$model;


                    Yii::$app->response->blocks[$model->content_block_name] = $model->content;

                    Yii::$app->response->title = $model->title;

                    Yii::$app->response->blocks['h1'] = $model->h1;

                    Yii::$app->response->meta_description = $model->meta_description;



                    break;
                }
            } else {
                $matches = true;
            }
        }

    }
}
