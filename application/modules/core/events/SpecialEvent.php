<?php

namespace app\modules\core\events;


use Yii;

abstract class SpecialEvent extends ViewEvent
{
    public $user_id = null;
    public $session_id = null;
    public $timestamp;

    /**
     * @return array Array of event data that will be passed though application or through js
     */
    abstract public function eventData();

    public function init()
    {
        parent::init();
        $this->timestamp = time();

        if (Yii::$app->user->isGuest === false) {
            $this->user_id = Yii::$app->user->id;
        } else {
            // some session identifier here is needed
            $this->session_id = Yii::$app->session->id;
        }
    }

    public function selector()
    {
        return null;
    }
}