<?php

namespace app\traits;

use app\models\Object;
use app\models\DynamicContent;
use app\modules\core\helpers\ContentBlockHelper;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\helpers\Json;

trait DynamicContentTrait
{
    public function loadDynamicContent($object_id, $route, $selections)
    {
        /** @var \app\components\Response $response */
        $response = Yii::$app->response;
        if ($response->is_prefiltered_page === true) {
            // DynamicContent should not work on prefiltered pages - all needed content is set in corresponding model
            return;
        }
        /**
         * @var $this \yii\web\Controller
         */

        $dynamicCacheKey = 'dynamicCacheKey'.json_encode([$object_id, $route, $selections]);

        if (!$dynamicResult = Yii::$app->cache->get($dynamicCacheKey)) {
            $dynamicResult = [];
            $models = DynamicContent::find()
                ->where([
                    'object_id' => $object_id,
                    'route' => $route,
                ])
                ->all();

            if (isset($selections['properties']) === false) {
                $selections['properties'] = [];
            }
            /**
             * @var $model DynamicContent
             */
            foreach ($models as $model) {
                if (is_integer($model->apply_if_last_category_id) === true && $model->apply_if_last_category_id !== 0) {
                    if (!isset($selections['last_category_id'])) {
                        continue;
                    } elseif ($selections['last_category_id'] != $model->apply_if_last_category_id) {
                        continue;
                    }
                }
                $model_selections = Json::decode($model->apply_if_params);
                $matches = false;
                if (is_array($model_selections) === true) {
                    $matches = true;

                    foreach ($model_selections as $property_id => $value) {
                        if (isset($selections['properties']) === true) {
                            if (isset($selections['properties'][$property_id]) === true) {
                                if (isset($selections['properties'][$property_id][0]) === true &&
                                    $selections['properties'][$property_id][0] == $value) {
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
                        $dynamicResult['model'] = $model;
                        if ($model->title) {
                            $dynamicResult['title'] = ContentBlockHelper::compileContentString(
                                $model->title,
                                self::class . "{$model->id}:title",
                                new TagDependency(
                                    [
                                        "tags" => [
                                            ActiveRecordHelper::getObjectTag($model, $model->id)
                                        ]
                                    ]
                                )
                            );
                        }
                        if ($model->meta_description) {
                            $dynamicResult['meta_description'] = ContentBlockHelper::compileContentString(
                                $model->meta_description,
                                self::class . ":{$model->id}:meta_description",
                                new TagDependency(
                                    [
                                        "tags" => [
                                            ActiveRecordHelper::getObjectTag($model, $model->id)
                                        ]
                                    ]
                                )
                            );
                        }
                        if ($model->h1) {
                            $dynamicResult['blocks']['h1'] = $model->h1;
                        }
                        $dynamicResult['blocks']['announce'] = $model->announce;
                        $dynamicResult['blocks'][$model->content_block_name] = $model->content;
                    }
                } else {
                    $matches = true;
                }
            }

            Yii::$app->cache->set(
                $dynamicCacheKey,
                $dynamicResult,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(DynamicContent::className()),
                            ActiveRecordHelper::getObjectTag(Object::className(), $object_id),
                            $route,
                        ]
                    ]
                )
            );
        }
        if (is_array($dynamicResult) === true && $dynamicResult !== []) {
            $response->dynamic_content_trait = true;
            $response->matched_dynamic_content_trait_model = $dynamicResult['model'];
            if (isset($dynamicResult['title']) && $dynamicResult['title']) {
                $response->title = $dynamicResult['title'];
                $response->dynamic_content_title_rewrited = true;
            }
            if (isset($dynamicResult['meta_description']) && $dynamicResult['meta_description']) {
                $response->meta_description = $dynamicResult['meta_description'];
                $response->dynamic_content_meta_description_rewrited = true;
            }
            if (isset($dynamicResult['blocks']) && is_array($dynamicResult['blocks'])) {
                foreach ($dynamicResult['blocks'] as $nameBlock => $contentBlock) {
                    $response->blocks[$nameBlock] = $contentBlock;
                    $response->dynamic_content_blocks_rewrited[$nameBlock] = true;
                }
            }
        }
    }
}
