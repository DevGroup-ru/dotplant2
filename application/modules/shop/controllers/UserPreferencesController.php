<?php
namespace app\modules\shop\controllers;

use app\modules\core\behaviors\DisableRobotIndexBehavior;
use app\modules\shop\models\UserPreferences;
use Yii;
use yii\helpers\Url;
use yii\validators\UrlValidator;
use yii\web\Controller;
use yii\web\Response;

class UserPreferencesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => DisableRobotIndexBehavior::className(),
            ]
        ];
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function actionSet($key, $value)
    {
        $request = Yii::$app->request;

        $preferences = UserPreferences::preferences();
        $preferences->setAttributes([$key=>$value]);
        $result = $preferences->validate();

        if (true === $request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $result;
        } else {
            return $this->redirect(\Yii::$app->request->referrer, 301);
        }
    }
}
