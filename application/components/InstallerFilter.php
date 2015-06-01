<?php

namespace app\components;

use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

class InstallerFilter extends ActionFilter
{
    public function beforeAction($action)
    {
        if (file_get_contents(Yii::getAlias('@app/installed.mark'))==='1') {
            throw new ForbiddenHttpException("DotPlant2 is already installed");
        }
        return true;
    }
}