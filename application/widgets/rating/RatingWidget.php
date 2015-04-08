<?php

namespace app\widgets\rating;

use app\models\RatingItem;
use yii\base\Widget;
use yii\helpers\VarDumper;

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
        $rating = RatingItem::getGroupByName($this->groupName);
        if (!is_array($rating) || empty($rating)) {
            return '';
        }
        if (!!$rating['allow_guest'] === false ) {
            return \Yii::t('app', 'Only authorized users can rate it');
        }
        $items = RatingItem::getItemsByAttributes(['rating_group' => $this->groupName], true, true);

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