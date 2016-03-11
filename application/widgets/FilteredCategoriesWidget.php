<?php

namespace app\widgets;

use app\models\Object;
use app\modules\shop\models\Category;
use app\properties\PropertiesHelper;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;

class FilteredCategoriesWidget extends PlainCategoriesWidget
{

    public $viewFile = 'categories-list';
    public $values_by_property_id = [];
    public $limit = null;
    public $category_group_id = null;
    public $sort = ['sort_order' => SORT_ASC];

    public function run()
    {
        $query = Category::find();
        $query->andWhere([Category::tableName() . '.active' => 1]);
        if ($this->root_category_id !== null) {
            $query->andWhere([Category::tableName() . '.parent_id' => $this->root_category_id]);
        }
        if ($this->category_group_id !== null) {
            $query->andWhere([Category::tableName() . '.category_group_id' => $this->category_group_id]);
        }
        $query->groupBy(Category::tableName().".id");
        $query->orderBy($this->sort);

        if ($this->limit !== null) {
            $query->limit($this->limit);
        }

        $object = Object::getForClass(Category::className());
        $multiFilterMode = Yii::$app->getModule('shop')->multiFilterMode;
        PropertiesHelper::appendPropertiesFilters($object, $query, $this->values_by_property_id, [], $multiFilterMode);
        $sql = $query->createCommand()->getRawSql();
        $cacheKey = "FilteredCategoriesWidget:".md5($sql);
        $result = Yii::$app->cache->get($cacheKey);

        if ($result === false) {
            $categories = Category::findBySql($sql)->all();

            $result = $this->render(
                $this->viewFile,
                [
                    'categories' => $categories,
                ]
            );
            Yii::$app->cache->set(
                $cacheKey,
                $result,
                86400,
                new TagDependency([
                    'tags' => ActiveRecordHelper::getCommonTag(Category::tableName())
                ])
            );
        }

        return $result;
    }


}
