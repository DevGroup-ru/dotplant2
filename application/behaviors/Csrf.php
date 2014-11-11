<?php

namespace app\behaviors;

use yii\base\Behavior;
use yii\web\Controller;

class Csrf extends Behavior
{
    /**
     * @property Controller $owner
     */
    public $enabledActions = [];
    public $disabledActions = [];

    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'checkAction',
        ];
    }

    public function checkAction()
    {
        if (in_array($this->owner->action->id, $this->enabledActions)) {
            $this->owner->enableCsrfValidation = true;
        }
        if (in_array($this->owner->action->id, $this->disabledActions)) {
            $this->owner->enableCsrfValidation = false;
        }
        return true;
    }
}
