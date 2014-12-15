<?php

namespace app\backend\controllers;

use app\models\Route;
use app\properties\url\FullCategoryPathPart;
use app\properties\url\ObjectSlugPart;
use app\properties\url\PartialCategoryPathPart;
use app\properties\url\PropertyPart;
use app\properties\url\StaticPart;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;

class RouteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['setting manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new Route();
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionEdit($id = null)
    {
        $model = new Route;
        if ($id !== null) {
            $model = Route::findOne($id);
        }


        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            $save_result = $model->save();
            if ($save_result) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                $this->redirect(Url::toRoute(['/backend/route/edit', 'id'=>$model->id]));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }

        $settings_by_class = [];
        $temporary_objects = [
            new StaticPart(),
            new PropertyPart(),
            new PartialCategoryPathPart(),
            new ObjectSlugPart(),
            new FullCategoryPathPart(),
            
        ];
        foreach ($temporary_objects as $obj) {
            $vars = get_object_vars($obj);
            $vars_by_type = [];
            foreach ($vars as $key => $value) {
                if (in_array($key, ['object', 'model', 'rest_part', 'gathered_part'])) {
                    continue;
                }
                if ($key != 'parameters') {
                    $vars_by_type[$key] = gettype($value);
                } else {
                    $vars_by_type[$key]=[];
                    foreach ($value as $param_key => $param_value) {
                        $vars_by_type[$key][$param_key] = gettype($param_value);
                        if ($vars_by_type[$key][$param_key] == 'NULL') {
                            $vars_by_type[$key][$param_key] = 'string';
                        }
                    }
                }
            }
            $settings_by_class[get_class($obj)] = $vars_by_type;
        }

        return $this->render(
            'edit',
            [
                'model' => $model,
                'settings_by_class' => $settings_by_class,
            ]
        );
    }
}
