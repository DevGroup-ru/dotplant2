<?php

namespace app\backend;

use yii\base\Module;
use yii\web\ForbiddenHttpException;

class BackendModule extends Module
{
    public $administratePermission = 'administrate';

    public $defaultRoute = 'dashboard/index';

    public function init()
    {
        if (!\Yii::$app->user->can($this->administratePermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        parent::init();
    }
}
