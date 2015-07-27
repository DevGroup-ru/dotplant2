<?php

namespace app\backend\actions;

use app;
use yii;
use yii\base\Action;
use yii\base\InvalidConfigException;

class AutocompleteAction extends Action
{
    public $modelName = null;
    public $json_attributes = ['name', 'id'];
    public $limit = 10;
    public $query_variable = 'q';
    public $search_attributes = ['name'];

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
     * @return array
     * @throws yii\web\NotFoundHttpException
     */
    public function run()
    {
        if (!isset($_GET[$this->query_variable])) {
            throw new yii\web\NotFoundHttpException;
        }

        $modelName = $this->modelName;
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        $query = new yii\db\Query;

        $query->select($this->json_attributes)
            ->from($modelName::tableName());

        $search_query = $_GET[$this->query_variable];

        foreach ($this->search_attributes as $attribute) {
            $query->orWhere(['like', $attribute, $search_query]);
        }

        $suggest = $query
            ->limit($this->limit)
            ->all();

        return $suggest;
    }
}
