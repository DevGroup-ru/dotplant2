<?php

namespace app\modules\review\widgets\rating;

use app\modules\review\models\RatingItem;
use app\modules\review\models\RatingValues;
use app\modules\review\models\Review;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class RatingShowWidget extends Widget
{
    public $objectModel;
    public $calcFunction;
    public $viewFile = 'rating-show';
    /**
     * @var Review|null
     */
    public $reviewModel = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        RatingAsset::register($this->view);
        if (is_array($this->calcFunction) && count($this->calcFunction) >= 2) {
            $class = array_shift($this->calcFunction);
            $method = array_shift($this->calcFunction);
            if (class_exists($class) && method_exists($class, $method)) {
                $this->calcFunction = [$class, $method];
            } else {
                $this->calcFunction = [$this, 'calculateRating'];
            }
        } elseif (!$this->calcFunction instanceof \Closure) {
            $this->calcFunction = [$this, 'calculateRating'];
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        parent::run();
        if (!is_null($this->objectModel)) {
            $groupedRatingValues = RatingValues::getValuesByObjectModel($this->objectModel);
            if (!empty($groupedRatingValues)) {
                $groups = [];
                foreach ($groupedRatingValues as $groupId => $group) {
                    $ratingItem = RatingItem::findById($groupId);
                    $groups[] = [
                        'name' => !is_null($ratingItem) ? $ratingItem->name : Yii::t('app', 'Unknown rating'),
                        'rating' => call_user_func($this->calcFunction, $group),
                        'votes' => count($group),
                    ];
                }
            } else {
                $groups = null;
            }
            return $this->render(
                $this->viewFile,
                [
                    'groups' => $groups,
                ]
            );
        } elseif (!is_null($this->reviewModel)) {
            $value = RatingValues::findOne(['rating_id' => $this->reviewModel->rating_id]);
            $groups = [];

            $ratingItem = RatingItem::findById(ArrayHelper::getValue($value, 'rating_item_id', 0));
            $group = [ArrayHelper::getValue($value, 'value')];

            $groups[] = [
                'name' => !is_null($ratingItem) ? $ratingItem->name : Yii::t('app', 'Unknown rating'),
                'rating' => call_user_func($this->calcFunction, $group),
            ];

            return $this->render(
                $this->viewFile,
                [
                    'groups' => $groups,
                ]
            );
        } else {
            return '';
        }

    }

    /**
     * @param $values
     * @return float|int
     */
    private function calculateRating($values)
    {
        $count = count($values);
        if (0 === $sum = array_sum($values)) {
            return 0;
        }
        return $sum / $count;
    }
}
