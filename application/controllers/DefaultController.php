<?php

namespace app\controllers;

use app\actions\SubmitFormAction;
use app\models\Config;
use app\models\Product;
use app\models\Search;
use app\modules\seo\behaviors\MetaBehavior;
use Yii;
use yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'seo' => [
                'class' => MetaBehavior::className(),
                'index' => $this->defaultAction,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'submit-form' => [
                'class' => SubmitFormAction::className(),
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }


    public function actionSearch()
    {
        $model = new Search();
        $model->load(Yii::$app->request->get());
        return $this->render(
            'search',
            [
                'model' => $model,
            ]
        );
    }

    public function actionAutoCompleteSearch($term)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = Product::find()->orderBy('sort_order');
        foreach (['name', 'content'] as $attribute) {
            $query->orWhere(['like', $attribute, $term]);
        }
        $query->andWhere(['active'=>1]);
        $products = $query->limit(Config::getValue('core.autoCompleteResultsCount', 5))->all();
        $result = [];
        foreach ($products as $product) {
            $result[] = [
                'template' => $this->renderPartial(
                    'auto-complete-item-template',
                    [
                        'product' => $product,
                    ]
                ),
            ];
        }
        return $result;
    }
}
