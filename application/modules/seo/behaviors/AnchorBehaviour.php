<?php

namespace app\modules\seo\behaviors;

use app\modules\seo\models\LinkAnchor;
use app\modules\seo\models\LinkAnchorBinding;
use app\modules\seo\models\ModelAnchorIndex;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

/**
 * Class AnchorBehaviour
 * @package app\modules\seo\behaviors
 * @property ActiveRecord $owner
 */
class AnchorBehaviour extends Behavior
{
    private $cache_expire = 84600;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    public function getLastIndex()
    {
        $model_id = Json::encode($this->owner->primaryKey);
        return ModelAnchorIndex::find()->where(
            [
                'and',
                [
                    'model_name' => $this->owner->className(),
                    'model_id' => $model_id
                ]
            ]
        )->one();
    }

    public function anchor($view_file, $hash = '')
    {
        $model_id = Json::encode($this->owner->primaryKey);
        /* @var LinkAnchorBinding $anchoreModel */
        $returnAnchor =
            \Yii::$app->getCache()->get("Anchor::{$this->owner->className()}::{$model_id}::{$view_file}::{$hash}");
        if (!$returnAnchor) {
            $anchorModel =
                LinkAnchorBinding::find()->with(['index'])->where(
                    [
                        'and',
                        [
                            LinkAnchorBinding::tableName().'.model_name' => $this->owner->className(),
                            LinkAnchorBinding::tableName().'.model_id' => $model_id,
                            LinkAnchorBinding::tableName().'.view_file' => $view_file,
                            LinkAnchorBinding::tableName().'.params_hash' => $hash
                        ]
                    ]
                )->one();
            if ($anchorModel === null) {
                /* @var ModelAnchorIndex $indexModel */
                $indexModel = $this->lastIndex;
                if ($indexModel === null) {
                    $indexModel = new ModelAnchorIndex(
                        [
                            'model_name' => $this->owner->className(),
                            'model_id' => $model_id,
                            'next_index' => 0,
                        ]
                    );
                }
                $anchors = LinkAnchor::find()->where(
                    [
                        'and',
                        [
                            'model_name' => $this->owner->className(),
                            'model_id' => $model_id
                        ]
                    ]
                )->orderBy(
                    [
                        LinkAnchor::tableName().'.sort_order' => SORT_ASC
                    ]
                )->limit((int)$indexModel->next_index + 1)->all();
                if (!empty($anchors)) {
                    if (!isset($anchors[$indexModel->next_index])) {
                        $indexModel->next_index = 0;
                    }
                    $anchor = $anchors[$indexModel->next_index];
                    $indexModel->next_index++;
                    $indexModel->save();
                    $bind = new LinkAnchorBinding(
                        [
                            'link_anchor_id' => $anchor->id,
                            'view_file' => $view_file,
                            'params_hash' => $hash,
                            'model_name' => $this->owner->className(),
                            'model_id' => $model_id,
                        ]
                    );
                    $bind->save();
                    $returnAnchor = $anchor->anchor;
                } else {
                    throw new NotFoundHttpException();
                }
            } else {
                $returnAnchor = $anchorModel->anchor->anchor;
            }
            \Yii::$app->getCache()->set(
                "Anchor::{$this->owner->className()}::{$model_id}::{$view_file}::{$hash}",
                $returnAnchor,
                $this->cache_expire
            );
        }
        return $returnAnchor;
    }

    public function afterDelete()
    {
        /* @var ActiveRecord $AR */
        $AR = $this->owner;
        $model_id = Json::encode($AR->primaryKey);
        LinkAnchor::deleteAll(['and', ['model_name' => $AR->className(), 'model_id' => $model_id]]);
        LinkAnchorBinding::deleteAll(['and', ['model_name' => $AR->className(), 'model_id' => $model_id]]);
        ModelAnchorIndex::deleteAll(['and', ['model_name' => $AR->className(), 'model_id' => $model_id]]);
    }

    public function setAnchors($anchors)
    {
        $model_id = Json::encode($this->owner->primaryKey);
        $message = '';
        $count = 0;
        $added = 0;
        foreach ($anchors as $anchor) {
            $anchor = trim($anchor);
            if ($anchor) {
                $count++;
                $anchorModel = LinkAnchor::find()->where(
                    [
                        'and',
                        [
                            'model_name' => $this->owner->className(),
                            'model_id' => $model_id,
                            'anchor' => $anchor
                        ]
                    ]
                )->one();
                if ($anchorModel === null) {
                    $anchorModel = new LinkAnchor(
                        [
                            'model_name' => $this->owner->className(),
                            'model_id' => $model_id,
                            'anchor' => $anchor,
                        ]
                    );
                    if ($anchorModel->save()) {
                        $added++;
                    } else {
                        $message .= "Anchor $anchor can't be added\n";
                    }
                } else {
                    $message .= "Anchor $anchor is exists\n";
                }
            }
        }
        return "Added $added/$count\n$message";
    }
}
