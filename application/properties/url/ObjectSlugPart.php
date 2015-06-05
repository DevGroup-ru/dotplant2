<?php

namespace app\properties\url;

use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;

class ObjectSlugPart extends UrlPart
{
    /**
     * @inheritdoc
     */
    public function getNextPart(
        $full_url,
        $next_part,
        &$previous_parts
    ) {

        if (!is_object($this->object)) {
            return false;
        }
        $model_class = $this->object->object_class;

        $next_parts = explode("/", $next_part);
        $slug = $next_parts[0];

        $last_category_id = null;
        foreach ($previous_parts as $part) {
            if (isset($part->parameters['last_category_id'])) {
                $last_category_id = $part->parameters['last_category_id'];
            }
        }

        $model = $model_class::findBySlug($slug, $last_category_id);
        if ($model !== null) {
            $this->parameters['model_id'] = $model->id;
            $part = new self([
                'gathered_part' => $slug,
                'rest_part' => mb_substr($next_part, mb_strlen($slug)),
                'parameters' => $this->parameters,
                'cacheTags' => [
                    ActiveRecordHelper::getObjectTag($model_class, $this->parameters['model_id']),
                ],
            ]);
            return $part;
        }
        return false;

    }
    /**
     * @inheritdoc
     */
    public function appendPart($route, $parameters = [], &$used_params = [], &$cacheTags = [])
    {
        if (!is_object($this->object)) {
            return false;
        }
        $slug_attribute = $this->object->object_slug_attribute;
        return $this->model->$slug_attribute;
    }
}
