<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class UserPreferencesController extends Controller{
    public function actionSet($key, $value)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $preferences = \app\models\UserPreferences::preferences();
        $preferences->setAttributes([$key=>$value]);

        return $preferences->validate();
    }
} 