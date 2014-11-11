<?php

namespace app\backend\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;

class JSTreeGetTree extends Action
{
    public $modelName = null;

    public $label_attribute = 'name';
    public $parent_attribute = 'parent_id';
    public $additional_search_conditions = [];
    public $query_parent_attribute = 'id';
    public $vary_by_type_attribute = 'show_type';
    public $expand_in_admin_attribute = 'expand_in_admin';
    

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
        if (!isset($_GET[$this->query_parent_attribute])) {
            throw new \yii\web\NotFoundHttpException;
        }

        $modelName = $this->modelName;
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $query = new \yii\db\Query;

        $fields = 'id, '.$this->label_attribute.', '.$this->parent_attribute;
        if (isset($this->vary_by_type_attribute)) {
            $fields .= ', '.$this->vary_by_type_attribute;
        }

        if (isset($this->expand_in_admin_attribute)) {
            $fields .= ', '.$this->expand_in_admin_attribute;
        }

        $fields .= ', (';
        $fields .= 'SELECT count(id) FROM '.$modelName::tableName().' counter ';
        $fields .= 'WHERE counter.parent_id = '.$modelName::tableName().'.id)';
        $fields .= " AS 'children_count'";

        $query->select($fields)
            ->from($modelName::tableName())
            ->where([$this->parent_attribute => $_GET[$this->query_parent_attribute]])
            ->andWhere($this->additional_search_conditions);

        $rows = $query
            ->all();
        $result = [];
        foreach ($rows as $row) {
            $item = [
                'id' => $row['id'],
                'text' => $row[$this->label_attribute],
            ];
            if (isset($this->vary_by_type_attribute)) {
                $item['type'] = $row[$this->vary_by_type_attribute];
            }
            $item['children'] = $row['children_count'] > 0;
            $item['a_attr'] = ['data-id'=>$row['id'], 'data-parent-id'=>$row['parent_id']];
            $item['state'] = [];

            if (isset($this->expand_in_admin_attribute)) {
                if ($row[$this->expand_in_admin_attribute]) {
                    $item['state']['opened'] = true;
                }
            }
            if (isset($_GET['selected_id']) && $item['id'] == $_GET['selected_id']) {
                $item['state']['selected'] = true;
            }
            if (count($item['state']) == 0) {
                unset($item['state']);
            }
            

            $result[] = $item;
        }

        return $result;
    }
}
