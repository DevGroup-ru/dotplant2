<?php

namespace app\modules\shop\controllers;


use app\backend\components\BackendController;
use app\components\Helper;
use app\modules\shop\models\GoogleFeed;
use Yii;

class BackendGoogleFeedController extends BackendController
{


    public $defaultAction = 'settings';

    /**
     * @return string|\yii\web\Response
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionSettings()
    {
        if (\Yii::$app->request->isPost) {
            $model = new GoogleFeed();
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $model->saveConfig();
            } elseif ($model->hasErrors()) {
                Yii::$app->session->setFlash('error', Helper::formatModelErrors($model, '<br />'));
            }
            return $this->refresh();
        }

        $model = new GoogleFeed();
        $model->loadConfig();

        return $this->render(
            'settings',
            [
                'model' => $model
            ]
        );
    }


}