<?php

namespace app\modules\seo\controllers;


use app\backend\components\BackendController;

class OpenGraphController extends BackendController
{
    public function actionIndex()
    {
        return $this->render('index');
    }

}