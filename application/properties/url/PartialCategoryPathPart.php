<?php

namespace app\properties\url;

use app\modules\shop\models\Category;
use Yii;

class PartialCategoryPathPart extends CategoryPart
{
    public function appendPart($route, $parameters = [], &$used_params = [])
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
            return $category->getUrlPath($this->include_root_category);
        } else {
            return false;
        }

    }
}
