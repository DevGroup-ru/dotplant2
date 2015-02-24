<?php

namespace app\backend\controllers;

use app\backend\actions\DeleteOne;
use app\backend\actions\MultipleDelete;
use app\models\CurrencyRateProvider;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class CurrencyRateProviderController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['shop manage'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'remove-all' => [
                'class' => MultipleDelete::className(),
                'modelName' => CurrencyRateProvider::className(),
            ],
            'delete' => [
                'class' => DeleteOne::className(),
                'modelName' => CurrencyRateProvider::className(),
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new CurrencyRateProvider();
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
        $model = new CurrencyRateProvider;
        $model->loadDefaultValues();
        
        if ($id !== null) {
            $model = CurrencyRateProvider::findOne($id);
        }
        


        $post = \Yii::$app->request->post();

        if ($model->load($post) && $model->validate() && !isset($_GET['CurrencyRateProvider'])) {

            $save_result = $model->save();
            if ($save_result) {
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                return $this->redirect(['/backend/currency-rate-provider/edit', 'id' => $model->id]);
            } else {
                \Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot update data'));
            }


        }

        return $this->render(
            'form',
            [
                'model' => $model,
            ]
        );
    }

}
