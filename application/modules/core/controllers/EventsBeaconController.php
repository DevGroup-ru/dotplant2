<?php

namespace app\modules\core\controllers;

use app;
use app\components\Controller;
use Yii;
use yii\helpers\Json;

class EventsBeaconController extends Controller
{
    public function actionIndex()
    {
        $data = Json::decode(Yii::$app->request->rawBody);
        if (is_array($data) === true) {

            // now we can handle it all
            //! @todo Implement it
            //! @todo Get event models with one query and using Events identity map by class name

        }
    }
}