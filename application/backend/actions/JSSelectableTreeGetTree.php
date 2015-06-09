<?php

namespace app\backend\actions;

use app;
use yii;
use yii\base\Action;
use yii\base\InvalidConfigException;

class JSSelectableTreeGetTree extends Action
{
    public $modelName = null;

    public $label_attribute = 'name';
    public $parent_attribute = 'parent_id';
    public $additional_search_conditions = [];
    public $query_parent_attribute = 'id';
    public $vary_by_type_attribute = 'show_type';


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
            throw new yii\web\NotFoundHttpException;
        }

        $modelName = $this->modelName;
        $selectedItems = Yii::$app->request->get('selectedItems', []);
        if (!empty($selectedItems)) {
            $selectedItems = explode(',', $selectedItems);
        }
        $parents = [0];
        $ids = $selectedItems;
        $q = new yii\db\Query;
        $q->select('parent_id')
            ->from($modelName::tableName());
        while (!empty($ids)) {
            $q->where(['id' => $ids]);
            $result = $q->all();
            $ids = [];
            foreach ($result as $row) {
                if (!in_array($row['parent_id'], $parents)) {
                    $parents[] = $row['parent_id'];
                }
                if ($row['parent_id'] != 0 && !in_array($row['parent_id'], $ids)) {
                    $ids[] = $row['parent_id'];
                }
            }
        }
        $q = null;
        $ids = null;
        //
        Yii::$app->response->format = yii\web\Response::FORMAT_JSON;

        $query = new yii\db\Query;

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

            if (in_array($row['id'], $parents)) {
                $item['state']['opened'] = true;
            }
            if (is_array($selectedItems) && in_array($row['id'], $selectedItems)) {
                $item['state']['selected'] = true;
            }

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
