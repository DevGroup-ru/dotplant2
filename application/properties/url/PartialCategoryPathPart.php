<?php

namespace app\properties\url;

use app\modules\shop\models\Category;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;

class PartialCategoryPathPart extends CategoryPart
{
    /**
     * @inheritdoc
     */
    public function appendPart($route, $parameters = [], &$used_params = [], &$cacheTags = [])
    {
        /*
            В parameters должен храниться last_category_id
            Если его нет - используем parameters.category_id
        */
        $category_id = null;
        if (isset($parameters['last_category_id'])) {
            $category_id = $parameters['last_category_id'];
        } elseif (isset($parameters['category_id'])) {
            $category_id = $parameters['category_id'];
        }

        $used_params[] = 'last_category_id';
        $used_params[] = 'category_id';

        if ($category_id === null) {
            return false;
        }

        $category = Category::findById($category_id);
        if (is_object($category) === true) {
            $parentIds = $category->getParentIds();
            foreach ($parentIds as $id) {
                $cacheTags[] = ActiveRecordHelper::getObjectTag(Category::className(), $id);
            }
            $cacheTags[] = ActiveRecordHelper::getObjectTag(Category::className(), $category_id);
            return $category->getUrlPath($this->include_root_category);
        } else {
            return false;
        }

    }
}
