<?php

namespace app\controllers;

use app\backend\components\Yml;
use Yii;
use yii\web\Controller;

class YmlController extends Controller
{
    public function actionGet($regenerate = false)
    {
        $yml = Yml::getInstance();

        if ($regenerate) {
            $yml->createYml('file', Yii::getAlias("@webroot") . "/yml.xml");
            return Yii::$app->response->sendFile(Yii::getAlias("@webroot") . "/yml.xml", null, [ 'mime' => 'text/xml', 'inline' => true ]);
        } else {
            if (file_exists(Yii::getAlias("@webroot") . "/yml.xml")) {
                return Yii::$app->response->sendFile(Yii::getAlias("@webroot") . "/yml.xml", null, [ 'mime' => 'text/xml', 'inline' => true ]);
            } else {
                return "file not exist";
            }
        }
    }
}
