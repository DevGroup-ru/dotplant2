<?php

namespace app\modules\shop\controllers;

use app\modules\core\behaviors\DisableRobotIndexBehavior;
use app\modules\shop\models\UserPreferences;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class UserPreferencesController extends Controller
{
    public function behaviors()
    {
        return [
            [
                'class' => DisableRobotIndexBehavior::className(),
            ]
        ];
    }

    public function actionSet($key, $value)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $preferences = UserPreferences::preferences();
        $preferences->setAttributes([$key=>$value]);
        return $preferences->validate();
    }
}
