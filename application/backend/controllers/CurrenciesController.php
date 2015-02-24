<?php

namespace app\backend\controllers;

use app\backend\actions\DeleteOne;
use app\backend\actions\MultipleDelete;
use app\backend\actions\UpdateEditable;
use app\models\Currency;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

class CurrenciesController extends Controller
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
                        'roles' => ['content manage'],
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
                'modelName' => Currency::className(),
            ],
            'delete' => [
                'class' => DeleteOne::className(),
                'modelName' => Currency::className(),
            ],
            'update-editable' => [
                'class' => UpdateEditable::className(),
                'modelName' => Currency::className(),
                'allowedAttributes' => [
                    'currency_rate_provider_id' => function(Currency $model, $attribute) {
                            if ($model === null || $model->rateProvider === null || $model->currency_rate_provider_id===0) {
                                return null;
                            }
                            return \yii\helpers\Html::tag('div', $model->rateProvider->name, ['class' => $model->rateProvider->name]);
                        },
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new Currency();
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
        $model = new Currency;
        $model->loadDefaultValues();
        
        if ($id !== null) {
            $model = Currency::findOne($id);
        }
        


        $post = \Yii::$app->request->post();

        if ($model->load($post) && $model->validate() && !isset($_GET['Currency'])) {

            $save_result = $model->save();
            if ($save_result) {
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                return $this->redirect(['/backend/currencies/edit', 'id' => $model->id]);
            } else {
                \Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot update data'));
            }


        }

        return $this->render(
            'currency-form',
            [
                'model' => $model,
            ]
        );
    }

}
