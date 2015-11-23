<?php

namespace app\controllers;

use app\actions\SubmitFormAction;
use app\backend\actions\PropertyHandler;
use app\models\Form;
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
                'class' => MetaBehavior::className()
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
//            'property-handler' => [
//                'class' => PropertyHandler::className(),
//                'modelName' => Form::className()
//            ]
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
        $query = Product::find()
            ->select(['id', 'name', 'main_category_id', 'slug', 'sku'])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_DESC]);
        foreach (['name', 'content', 'sku'] as $attribute) {
            $query->orWhere(['like', $attribute, $term]);
        }
        $query->andWhere(['active'=>1]);
        $products = $query->limit(Yii::$app->getModule('core')->autoCompleteResultsCount)->all();
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
