<?php

namespace app\widgets;

use app\models\ObjectStaticValues;
use app\modules\shop\models\Product;
use app\models\Object;
use app\modules\shop\models\Category;
use Yii;
use yii\base\Widget;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Json;
use yii\helpers\Url;

class CategoriesWidget extends Widget
{
    private $possible_selections = null;
    public $category_group_id = null;
    public $current_selections = [];
    public $omit_root = true;
    public $route = '/shop/product/list';
    public $viewFile = 'categoriesWidget';
    public $recursive = true;
    public $onlyAvailableProducts = false;

    /**
     * @inheritdoc
     */
    public function run()
    {
        Yii::beginProfile("CategoriesWidget - get possible selections");
        $this->getPossibleSelections();
        Yii::endProfile("CategoriesWidget - get possible selections");
        return $this->render(
            $this->viewFile,
            [
                'current_selections' => $this->current_selections,
                'possible_selections' => $this->possible_selections,
                'route' => $this->route,
                'category_group_id' => $this->category_group_id,
            ]
        );
    }

    public function getPossibleSelections()
    {
        $allowed_category_ids = [];

        if ($this->onlyAvailableProducts) {
            $object = Object::getForClass(Product::className());
            if (!is_null($object) && isset($this->current_selections['last_category_id'])) {

                $cacheKey = 'CategoriesFilterWidget: ' . $object->id . ':' . $this->current_selections['last_category_id'] . ':'
                    . Json::encode($this->current_selections['properties']);
                $allowed_category_ids = Yii::$app->cache->get($cacheKey);
                if ($allowed_category_ids === false) {

                    $query = new Query();
                    $query = $query->select($object->categories_table_name . '.category_id')
                        ->distinct()
                        ->from($object->categories_table_name);

                    if (count($this->current_selections['properties']) > 0) {
                        foreach ($this->current_selections['properties'] as $property_id => $values) {
                            $joinTableName = 'OSVJoinTable'.$property_id;
                            $query->join(
                                'JOIN',
                                ObjectStaticValues::tableName() . ' '.$joinTableName,
                                $joinTableName.'.object_id = :objectId AND '
                                . $joinTableName.'.object_model_id = ' . $object->categories_table_name . '.object_model_id  ',
                                [
                                    ':objectId' => $object->id,
                                ]
                            );

                            $imploded_values = implode(', ', array_map('intval', $values));
                            $query->andWhere(new Expression('`'.$joinTableName.'`.`property_static_value_id`' . ' in (' . $imploded_values.')'));
                        }
                    }
                    $allowed_category_ids = $query->column();

                    Yii::$app->cache->set(
                        $cacheKey,
                        $allowed_category_ids,
                        86400,
                        new \yii\caching\TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag($object->object_class),
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(Category::className())
                                ],
                            ]
                        )
                    );
                    $object = null;
                }
            }
        }

        $this->possible_selections = [];
        $models = Category::getByLevel($this->category_group_id);
        if (isset($models[0]) && $this->omit_root == true) {
            $models = Category::getByParentId($models[0]->id);
        }
        $this->possible_selections = [];
        foreach ($models as $model) {
            if ($this->onlyAvailableProducts === true && !in_array($model->id, $allowed_category_ids)) {
                continue;
            }
            $this->possible_selections [] = $this->recursiveGetTree($model, $allowed_category_ids);
        }



        return $this->possible_selections;
    }

    private function recursiveGetTree($model, $allowed_category_ids)
    {
        $params = [$this->route];
        $params += $this->current_selections;
        $params['category_group_id'] = $this->category_group_id;
        $params['last_category_id'] = $model->id;
        if (!isset($params['categories'])) {
            $params['categories'] = [];
        }
        $active = false;
        if (isset($this->current_selections['last_category_id'])) {
            $active = $this->current_selections['last_category_id'] == $model->id;
        }
        $result = [
            'label' => $model->name,
            'url' => Url::to($params),
            'items' => [],
            'active' => in_array($model->id, $params['categories']) || $active,
            '_model' => &$model,
        ];
        if ($this->recursive === true) {
            $children = Category::getByParentId($model->id);

            foreach ($children as $child) {
                if ($this->onlyAvailableProducts === true && !in_array($child->id, $allowed_category_ids)) {
                    continue;
                }
                $result['items'][] = $this->recursiveGetTree($child, $allowed_category_ids);
            }
        }
        return $result;
    }
}
