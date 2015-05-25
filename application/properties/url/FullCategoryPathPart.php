<?php

namespace app\properties\url;

use app\modules\shop\models\Category;
use Yii;

class FullCategoryPathPart extends CategoryPart
{
    /**
     * Атрибут модели, содержащий привязку к категории
     */
    public $model_category_attribute = 'main_category_id';

    public function appendPart($route, $parameters = [], &$used_params = [])
    {
        $used_params[] = 'categories';
        $used_params[] = 'category_group_id';
        $used_params[] = $this->model_category_attribute;

        $attribute_name = $this->model_category_attribute;
        $category_id = $this->model->$attribute_name;

        /** @var Category $category */
        $category = Category::findById($category_id);

        if (is_object($category) === true) {
            return $category->getUrlPath($this->include_root_category);
        } else {
            return false;
        }

    }
}
