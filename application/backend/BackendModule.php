<?php

namespace app\backend;

use yii\base\Module;

class BackendModule extends Module
{
    public $administratePermission = 'administrate';

    public $defaultRoute = 'dashboard/index';

    public function init()
    {
        parent::init();
    }
}
