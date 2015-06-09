<?php

namespace app\modules\review\widgets\rating;

use app\modules\review\models\RatingItem;
use Yii;
use yii\base\Widget;

class RatingWidget extends Widget
{
    public $groupName;
    public $viewFile = 'rating-slim';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        RatingAsset::register($this->view);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();
        /** @var RatingItem $rating */
        $rating = RatingItem::findOne(['rating_group' => $this->groupName]);
        if (is_null($rating)) {
            return '';
        }
        if (0 == $rating->allow_guest && Yii::$app->user->isGuest) {
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
