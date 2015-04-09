<?php

namespace app\backend\controllers;

use app\backend\models\Yml;
use app\backgroundtasks\helpers\BackgroundTasks;
use app\models\Config;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;

class YmlController extends Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render(
            'index',
            []
        );
    }

    public function actionSettings()
    {
        if (Yii::$app->request->isPost) {
            $model = new Yml();
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $model->saveConfig();
            } else {
                $error = implode('<br />',
                    array_map(
                        function($i) {
                            if (is_array($i)) {
                                return array_pop($i);
                            }
                            return $i;
                        },
                        $model->errors)
                );
                Yii::$app->session->setFlash('error', $error);
            }
        }

        $model = new Yml();
        $model->loadConfig();

        return $this->render(
            'settings',
            [
                'model' => $model
            ]
        );
    }

    public function actionCreate()
    {
        BackgroundTasks::addTask(
            [
                'name' => 'yml_generate',
                'description' => 'Creating YML file',
                'action' => 'yml/generate',
                'params' => '',
                'init_event' => 'yml',
            ],
            [
                'create_notification' => true,
            ]
        );

        Yii::$app->session->setFlash('success', 'Task has been created.');
        return $this->redirect(['settings']);
    }
}
