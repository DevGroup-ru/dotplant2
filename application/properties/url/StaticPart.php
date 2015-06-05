<?php

namespace app\properties\url;

use app\modules\shop\models\Category;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;

class StaticPart extends UrlPart
{
    public $static_part = 'dummy_static_part';

    public $parameters = [
        'last_category_id' => null,
        'category_group_id' => null,
    ];

    /**
     * @inheritdoc
     */
    public function getNextPart($full_url, $next_part, &$previous_parts)
    {
        if (mb_strpos($next_part, $this->static_part) === 0) {
            if (count($this->parameters) === 0) {
                $this->parameters = ['static_part' => $this->static_part,];
            }
            $cacheTags = [];

            if (isset($this->parameters['last_category_id']) && $this->parameters['last_category_id'] !== null) {
                $cacheTags[] = ActiveRecordHelper::getObjectTag(Category::className(), $this->parameters['last_category_id']);
            }

            $part = new self([
                'gathered_part' => $this->static_part,
                'rest_part' => mb_substr($next_part, mb_strlen($this->static_part)),
                'parameters' => $this->parameters,
                'cacheTags' => $cacheTags,
            ]);

            return $part;
        } else {
            return false;
        }
    }
    /**
     * @inheritdoc
     */
    public function appendPart($route, $parameters = [], &$used_params = [], &$cacheTags = [])
    {
        if (isset($this->parameters['category_group_id'], $parameters['category_group_id'])) {
            $used_params[] = 'category_group_id';
            $used_params[] = 'last_category_id';
            if ($this->parameters['category_group_id'] != $parameters['category_group_id']) {
                return false;
            }
        }
        return $this->static_part;
    }
}
