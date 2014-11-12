<?php

namespace app\backend\controllers;

use app\models\Object;
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

    public function actionIndex()
    {
        $searchModel = new Product;
        $params = Yii::$app->request->get();

        if (null !== $catId = Yii::$app->request->get('parent_id')) {
            $query = $searchModel::find();
            $categoriesTableName = Object::getForClass(Product::className())->categories_table_name;
            $query->select('p.*')
                ->from(Product::tableName() . ' p')
                ->leftJoin($categoriesTableName . ' cp', 'cp.object_model_id=p.id')
                ->where('p.is_deleted=1 AND p.parent_id=0 AND cp.category_id=:cur', [':cur' => $catId])
                ->orderBy('p.id');

            $dataProvider = new ActiveDataProvider(
                [
                    'query' => $query,
                    'pagination' => new Pagination([
                            'defaultPageSize' => 10
                        ])
                ]
            );
        } else {
            $params[$searchModel->formName()]['is_deleted'] = 1;
            $dataProvider = $searchModel->search($params);
        }

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

}
