<?php

namespace app\widgets;

use app\models\Object;
use app\models\Category;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Widget;
use yii\helpers\Json;
use yii\helpers\Url;

class FilteredCategoriesWidget extends PlainCategoriesWidget
{

    public $viewFile = 'categories-list';
    public $values_by_property_id = [];
    public $limit = null;

    public function run()
    {
        $query = Category::find();
        $query->andWhere([Category::tableName() . '.active' => 1]);
        if ($this->root_category_id !== null) {
            $query->andWhere([Category::tableName() . '.parent_id' => $this->root_category_id]);
        }
        $query->groupBy(Category::tableName().".id");
        $query->orderBy(Category::tableName().".sort_order");

        if ($this->limit !== null) {
            $query->limit($this->limit);
        }

        $object = Object::getForClass(Category::className());

        \app\properties\PropertiesHelper::appendPropertiesFilters(
            $object,
            $query,
            $this->values_by_property_id,
            []
        );

        $cacheKey = "FilteredCategoriesWidget:".$this->root_category_id.":".$this->viewFile.":".Json::encode($this->values_by_property_id);
        $result = Yii::$app->cache->get($cacheKey);
        
        if ($result === false) {
            $categories = Category::findBySql($query->createCommand()->getRawSql())->all();

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
                new \yii\caching\TagDependency([
                    'tags' => ActiveRecordHelper::getCommonTag(Category::tableName())
                ])
            );
        }

        return $result;
    }


}
