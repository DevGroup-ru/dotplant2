<?php

namespace app\controllers;

use app\actions\SubmitFormAction;
use app\components\search\SearchEvent;
use app\models\Search;
use app\modules\shop\models\Product;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'app\actions\ErrorAction',
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

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'submit-form' => ['post'],
                ],
            ]
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

    /**
     * @param $term
     * @return string JSON
     */
    public function actionAutoCompleteSearch($term)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $search = new Search();
        $search->q = $term;
        $search->on(Search::QUERY_SEARCH_PRODUCTS_BY_DESCRIPTION, function (SearchEvent $event) {
            $event->setFunctionSearch(function ($activeQuery) {
                $activeQuery->limit(Yii::$app->getModule('core')->autoCompleteResultsCount);
                return Product::find()
                    ->select(['id', 'name', 'main_category_id', 'slug', 'sku'])
                    ->where(['id' => $activeQuery->all()])
                    ->all();
            });
        });
        $products = $search->searchProductsByDescription();
        $result = [];

        foreach ($products as $product) {
            /** @var Product $product */
            $result[] = [
                'id' => $product->id,
                'name' => $product->name,
                'url' => Url::toRoute(
                    [
                        '@product',
                        'model' => $product,
                        'category_group_id' => $product->getMainCategory()->category_group_id,
                    ],
                    true
                ),
            ];
        }
        return $result;
    }
}
