<?php

namespace app\backend\controllers;

use app\backend\actions\JSTreeGetTrees;
use app\models\Category;
use app\models\Object;
use app\models\ViewObject;
use app\properties\HasProperties;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class CategoryController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['category manage'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'getTree' => [
                'class' => JSTreeGetTrees::className(),
                'modelName' => Category::className(),
                'label_attribute' => 'name',
                'vary_by_type_attribute' => null,
            ],
        ];
    }

    public function actionIndex($parent_id = 1)
    {
        $searchModel = new Category();
        $searchModel->parent_id = $parent_id;

        $params = Yii::$app->request->get();

        $dataProvider = $searchModel->search($params);

        $model = null;
        if ($parent_id > 0) {
            $model = Category::findOne($parent_id);
        }

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'model' => $model,
            ]
        );
    }

    public function actionEdit($parent_id = null, $id = null)
    {
        if (null === $parent_id) {
            throw new NotFoundHttpException;
        }

        if (null === $object = Object::getForClass(Category::className())) {
            throw new ServerErrorHttpException;
        }

        /** @var null|Category|HasProperties $model */
        $model = null;
        if (null !== $id) {
            $model = Category::findById($id, null, null);
        } else {
            if (null !== $parent = Category::findById($parent_id, null, null)) {
                $model = new Category;
                $model->parent_id = $parent_id;
                $model->category_group_id = $parent->category_group_id;
            } else {
                throw new ServerErrorHttpException;
            }
        }

        if (null === $model) {
            throw new ServerErrorHttpException;
        }

        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $save_result = $model->save();
            $model->saveProperties($post);

            if (null !== $view_object = ViewObject::getByModel($model, true)) {
                if ($view_object->load($post, 'ViewObject')) {
                    if ($view_object->view_id <= 1) {
                        $view_object->delete();
                    } else {
                        $view_object->save();
                    }
                }
            }

            if ($save_result) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                return $this->redirect(
                    [
                        '/backend/category/edit',
                        'id' => $model->id,
                        'parent_id' => $model->parent_id
                    ]
                );
            } else {
                throw new ServerErrorHttpException;
            }
        }

        return $this->render(
            'category-form',
            [
                'model' => $model,
                'object' => $object,
            ]
        );
    }

    public function actionDelete($id = null, $parent_id = null)
    {

        if ((null === $id) || (null === $model = Category::findById($id, null, null))) {
            throw new NotFoundHttpException;
        }

        if (!$model->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('shop', 'The object is placed in the cart'));
        } else {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Object has been removed'));
        }

        return $this->redirect(Url::to(['index', 'parent_id' => $model->parent_id]));
    }

    public function actionRemoveAll($parent_id)
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Category::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index', 'parent_id' => $parent_id]);
    }

    public function actionAutocomplete($search = null, $id = null)
    {
        /**
         * @todo Добавить отображение вложенности
         */
        $out = ['more' => false];
        if (!is_null($search)) {
            $query = new Query;
            $query->select('id, name AS text')
                ->from(Category::tableName())
                ->andWhere(['like', 'name', $search])
                ->limit(100);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Category::findOne($id)->name];
        } else {
            $out['results'] = ['id' => 0, 'text' => 'No matching records found'];
        }
        echo Json::encode($out);
    }

    public function actionRestore($id = null)
    {
        if (null === $id) {
            throw new NotFoundHttpException;
        }

        if (null === $model = Category::findOne(['id' => $id])) {
            throw new NotFoundHttpException;
        }

        $model->restoreFromTrash();

        Yii::$app->session->setFlash('success', Yii::t('app', 'Object successfully restored'));

        return $this->redirect(Url::toRoute(['edit', 'id' => $id, 'parent_id' => $model->parent_id]));
    }
}
