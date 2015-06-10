<?php

namespace app\controllers;

use app\actions\SubmitFormAction;
use app\modules\core\components\MailComponent;
use app\modules\shop\models\Product;
use app\models\Search;
use app\modules\seo\behaviors\MetaBehavior;
use Yii;
use yii\helpers\Url;
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
        $query = Product::find()
            ->select(['id', 'name', 'main_category_id'])
            ->orderBy('sort_order');
        foreach (['name', 'content'] as $attribute) {
            $query->orWhere(['like', $attribute, $term]);
        }
        $query->andWhere(['active'=>1]);
        $products = $query->limit(Yii::$app->getModule('core')->autoCompleteResultsCount)->all();
        $result = [];

        $serverName = 'http://'.Yii::$app->getModule('core')->serverName;

        foreach ($products as $product) {
            $result[] = [
                'id' => $product->id,
                'name' => $product->name,
                'url' => $serverName . Url::to([
                    '/shop/product/show',
                    'model' => $product,
                    'category_group_id' => $product->getMainCategory()->category_group_id,
                ]),
            ];
        }
        return $result;
    }
}
