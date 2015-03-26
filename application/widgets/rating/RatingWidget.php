<?php

namespace app\widgets\rating;

use app\models\RatingItem;
use yii\base\Widget;

class RatingWidget extends Widget
{
    public $groupName = null;
    public $viewFile = 'rating-slim';

    /**
     *
     */
    public function init()
    {
        parent::init();

        RatingAsset::register($this->view);
    }

    /**
     * @return string
     */
    public function run()
    {
        parent::run();

        $items = RatingItem::getItemsByAttributes(['rating_group' => $this->groupName], true, true);
        if (empty($items)) {
            return '';
        }

        $group = current($items);

        return $this->render(
            $this->viewFile,
            [
                'items' => $items,
                'group' => $group,
            ]
        );
    }
}
?>