<?php

namespace app\components;

use Yii;

class User extends \yii\web\User
{
    public function getFrontendEditing()
    {
        if (!isset(Yii::$app->session['frontend_editing'])) {
            Yii::$app->session['frontend_editing'] = false;
        }
        return Yii::$app->session['frontend_editing'];
    }
}
