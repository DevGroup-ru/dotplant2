<?php

namespace app\modules\core\behaviors;

use yii\base\Behavior;
use yii\web\Controller;

class DisableRobotIndexBehavior extends Behavior
{
    /**
     * @return array
     */
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'disableRobotIndex',
        ];
    }

    /*
     * Add headers to response
     */
    public function disableRobotIndex()
    {
        $headers = \Yii::$app->response->getHeaders();
        $headers->set('X-Robots-Tag', 'none');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('X-Content-Type-Options', 'nosniff');
    }
}