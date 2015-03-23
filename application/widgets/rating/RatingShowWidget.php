<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 20.03.15
 * Time: 16:47
 */

namespace app\widgets\rating;

use app\models\RatingValues;
use yii\base\Widget;

class RatingShowWidget extends Widget
{
    public $objectModel = null;
    public $calcFunction = null;
    public $viewFile = 'rating-show';

    /**
     *
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
        }
        else if (!$this->calcFunction instanceof \Closure) {
            $this->calcFunction = [$this, 'calculateRating'];
        }
    }

    /**
     * @return string
     */
    public function run()
    {
        parent::run();

        if (null === $this->objectModel) {
            return '';
        }

        $items = RatingValues::getValuesByObjectModel($this->objectModel);
        if (empty($items)) {
            $items = [0];
        } else {
            $items = array_column($items, 'value');
        }

        return $this->render(
            $this->viewFile,
            [
                'rating' => call_user_func($this->calcFunction, $items),
                'votes' => count($items),
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
?>