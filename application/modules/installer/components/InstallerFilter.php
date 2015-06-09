<?php

namespace app\modules\installer\components;

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

        Yii::$app->language = Yii::$app->session->get('language', 'en');

        return true;
    }
}