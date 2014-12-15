<?php

namespace app\widgets;

use app\models\Category;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Widget;
use yii\helpers\Url;

class PlainCategoriesWidget extends Widget
{

    public $root_category_id = null;
    public $viewFile = 'categories-list';
    

    public function run()
    {

        $cacheKey = "PlainCategoriesWidget:".$this->root_category_id.":".$this->viewFile;
        $result = Yii::$app->cache->get($cacheKey);
        if (!is_array($result)) {
            $categories = Category::getByParentId($this->root_category_id);
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
