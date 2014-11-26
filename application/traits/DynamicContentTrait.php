<?php

namespace app\traits;

use app\models\DynamicContent;
use Yii;
use yii\helpers\Json;

trait DynamicContentTrait
{
    public function loadDynamicContent($object_id, $route, $selections)
    {
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
                    if (!empty($model->content_block_name)) {
                        $this->view->blocks[$model->content_block_name] = $model->content;
                    }
                    if (!empty($model->title)) {
                        $this->view->title = $model->title;
                    }
                    if (!empty($model->h1)) {
                        $this->view->blocks['h1'] = $model->h1;
                    }

                    if (!empty($model->meta_description)) {
                        $this->view->registerMetaTag(
                            [
                                'name' => 'description',
                                'content' => $model->meta_description,
                            ],
                            'meta_description'
                        );

                    }

                    break;
                }
            } else {
                $matches = true;
            }
        }
    }
}
