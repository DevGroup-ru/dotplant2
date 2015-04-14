<?php

namespace app\backend\components;

use Yii;
use app\components\Controller;

/**
 * BackendController is base class for backend controllers in DotPlant2.
 * In such controllers we should not see backend floating panel.
 * @package app\backend\components
 */
class BackendController extends Controller
{
    public $layout = '@app/backend/views/layouts/main';
}