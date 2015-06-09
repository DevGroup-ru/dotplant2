<?php

namespace app\modules\review\widgets\rating;

use app\modules\review\models\RatingItem;
use app\modules\review\models\RatingValues;
use Yii;
use yii\base\Widget;

class RatingShowWidget extends Widget
{
    public $objectModel;
    public $calcFunction;
    public $viewFile = 'rating-show';

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
        if (null === $this->objectModel) {
            return '';
        }
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
        return $sum/$count;
    }
}
