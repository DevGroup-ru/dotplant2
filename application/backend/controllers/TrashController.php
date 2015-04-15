<?php

namespace app\backend\controllers;

use app\models\Category;
use app\models\Object;
use app\models\Order;
use app\models\Page;
use app\models\Product;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\web\Controller;

class TrashController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['administrate'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'getTree' => [
                'class' => 'app\backend\actions\JSTreeGetTrees',
                'modelName' => 'app\models\Category',
                'label_attribute' => 'name',
                'vary_by_type_attribute' => null,
                'show_deleted' => null
            ],
        ];
    }


    public function actionClean($returnUrl = '/backend', $modelName = false)
    {
        $models = [];
        if ($modelName) {
            $models[] = new $modelName;
        } else {
            $models = [
                new Category(),
                new Product(),
                new Page(),
                new Order(),
            ];
        }
        foreach ($models as $model) {
            $query = $model::find()->where(['is_deleted' => 1]);
            if (Yii::$app->request->post('items')) {
                $query->andWhere(['id' => Yii::$app->request->post('items')]);
            }
            foreach ($query->all() as $position) {
                $position->delete();
            }
        }
        Yii::$app->session->setFlash('warning', Yii::t('app', 'trash cleared'));
        $this->redirect($returnUrl);
    }

    public function actionIndex()
    {

        $categoryModel = new Category();
        $categoryProvider = new ActiveDataProvider(
            [
                'query' => $categoryModel::find()->andWhere(['is_deleted' => 1]),
                'pagination' => new Pagination(['defaultPageSize' => 10])
            ]
        );

        $productModel = new Product();
        $productProvider = new ActiveDataProvider(
            [
                'query' => $productModel::find()->andWhere(['is_deleted' => 1]),
                'pagination' => new Pagination(['defaultPageSize' => 10])
            ]
        );

        $pageModel = new Page();
        $pageProvider = new ActiveDataProvider(
            [
                'query' => $pageModel::find()->andWhere(['is_deleted' => 1]),
                'pagination' => new Pagination(['defaultPageSize' => 10])
            ]
        );


        $orderModel = new Order();
        $orderProvider = new ActiveDataProvider(
            [
                'query' => $orderModel::find()->andWhere(['is_deleted' => 1]),
                'pagination' => new Pagination(['defaultPageSize' => 10])
            ]
        );


        return $this->render(
            'index',
            [
                'categoryModel' => $categoryModel,
                'categoryProvider' => $categoryProvider,
                'productModel' => $productModel,
                'productProvider' => $productProvider,
                'pageModel' => $pageModel,
                'pageProvider' => $pageProvider,
                'orderModel' => $orderModel,
                'orderProvider' => $orderProvider
            ]
        );


    }
}