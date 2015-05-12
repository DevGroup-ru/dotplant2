<?php

namespace app\properties\url;

use app\modules\shop\models\Category;
use Yii;

abstract class CategoryPart extends UrlPart
{
    public $category_group_id = 1;

    /**
     * Должен ли урл включать в себя главную категорию
     */
    public $include_root_category = false;

    public function getNextPart($full_url, $next_part, &$previous_parts)
    {
        $next_parts = explode("/", $next_part);
        $gathered_parts = [];
        $previous_model = null;

        $root_id = 0;
        $title_append = "";

        if ($this->include_root_category === false) {
            $root_category = Category::findRootForCategoryGroup($this->category_group_id);
            if (is_object($root_category)) {
                $root_id = $root_category->id;
            } else {
                return false;
            }
        }

        foreach ($next_parts as $slug) {
            if (empty($slug)) {
                break;
            }
            $model = null;
            if ($previous_model === null) {
                $model = Category::findBySlug($slug, $this->category_group_id, $root_id);
            } else {
                $model = Category::findBySlug($slug, $this->category_group_id, $previous_model->id);
            }
            if ($model === null) {
                // выходим из цикла - тут никого нет
                break;
            }
            if (!empty($model->title_append)) {
                $title_append = $model->title_append;
            }

            
            $gathered_parts[$slug] = $model->id;
            $previous_model = $model;
        }

        if (count($gathered_parts) === 0) {
            if ($this->include_root_category === false) {
                $this->parameters['last_category_id'] = $root_category->id;
                $part = new $this([
                    'gathered_part' => '',
                    'rest_part' => $next_part,
                    'parameters' => $this->parameters,
                ]);
                
                return $part;
            }
            return false;
        }

        $this->parameters['categories'] = [];
        if (!empty($title_append)) {
            $this->parameters['title_append'] = [$title_append];
        }

        $last_category_id = null;

        foreach ($gathered_parts as $slug => $id) {
            $this->parameters['categories'][] = $id;
            $last_category_id = $id;
        }
        $this->parameters['last_category_id'] = $last_category_id;

        $full_categories_url = implode("/", array_keys($gathered_parts));

        $part = new $this([
            'gathered_part' => $full_categories_url,
            'rest_part' => mb_substr($next_part, mb_strlen($full_categories_url)),
            'parameters' => $this->parameters,
        ]);
        return $part;
    }

    public function appendPart($route, $parameters = [], &$used_params = [])
    {
        $used_params[] = 'categories';

        return false;
    }
}
