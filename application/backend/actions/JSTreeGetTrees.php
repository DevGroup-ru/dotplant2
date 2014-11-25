<?php

namespace app\backend\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;

class JSTreeGetTrees extends Action
{
    public $modelName = null;
    public $id_attribute = 'id';
    public $label_attribute = 'name';
    public $parent_attribute = 'parent_id';
    public $additional_search_conditions = [];
    public $query_parent_attribute = 'id';
    public $query_selected_attribute = 'selected_id';
    public $vary_by_type_attribute = 'show_type';
    public $show_deleted = null;


    public function init()
    {
        if (!isset($this->modelName)) {
            throw new InvalidConfigException("Model name should be set in controller actions");
        }
        if (!class_exists($this->modelName)) {
            throw new InvalidConfigException("Model class does not exists");
        }
    }

    public function run()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        if (null === $current = Yii::$app->request->get($this->query_selected_attribute)) {
            $current = Yii::$app->request->get($this->query_parent_attribute);
        }

        $cacheKey = "JSTree:$this->modelName:$this->show_deleted";
        if (false === $result = Yii::$app->cache->get($cacheKey)) {
            $modelName = $this->modelName;

            /** @var \yii\db\ActiveQuery $query */
            $query = (new $modelName)->find()->orderBy('id ASC');
            if (null !== $this->show_deleted) {
                $query = $query->where(['is_deleted' => $this->show_deleted]);
            }

            if (null === $q = $query->asArray()->all()) {
                return;
            }

            $result = [];
            foreach ($q as $row) {
                $item = [
                    'id' => $row[$this->id_attribute],
                    'parent' => ($row[$this->parent_attribute] > 0) ? $row[$this->parent_attribute] : '#',
                    'text' => $row[$this->label_attribute],
                    'a_attr' => ['data-id'=>$row[$this->id_attribute], 'data-parent-id'=>$row[$this->parent_attribute]],
                ];

                if (null !== $this->vary_by_type_attribute) {
                    $item['type'] = $row[$this->vary_by_type_attribute];
                }

                $result[$row[$this->id_attribute]] = $item;
            }

            Yii::$app->cache->set(
                $cacheKey,
                $result,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag($modelName::className()),
                        ],
                    ]
                )
            );
        }

        if (array_key_exists($current, $result)) {
            $result[$current] = array_merge($result[$current], ['state' => ['opened' => true, 'selected' => true]]);
        }

        return array_values($result);
    }
}
