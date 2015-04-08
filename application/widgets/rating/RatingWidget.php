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
        $rating = RatingItem::findOne(['rating_group' => $this->groupName]);
        if (is_null($rating)) {
            return '';
        }
        if (0 == $rating->allow_guest) {
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