<?php

namespace app\modules\shop\controllers;

use app\backend\actions\PropertyHandler;
use app\backend\components\BackendController;
use app\backend\events\BackendEntityEditEvent;
use app\modules\image\widgets\views\AddImageAction;
use app\modules\shop\models\Category;
use app\models\Object;
use app\models\ViewObject;
use app\properties\HasProperties;
use app\modules\image\widgets\RemoveAction;
use app\modules\image\widgets\SaveInfoAction;
use app\modules\image\widgets\UploadAction;
use devgroup\JsTreeWidget\AdjacencyFullTreeDataAction;
use devgroup\JsTreeWidget\TreeNodeMoveAction;
use devgroup\JsTreeWidget\TreeNodesReorderAction;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\modules\shop\actions\BatchEditPriceAction;

class BackendCategoryController extends BackendController
{

    const BACKEND_CATEGORY_EDIT = 'backend-category-edit';
    const BACKEND_CATEGORY_EDIT_SAVE = 'backend-category-edit-save';
    const BACKEND_CATEGORY_EDIT_FORM = 'backend-category-edit-form';
    const BACKEND_CATEGORY_AFTER_SAVE = 'backend-category-after-save';

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
                'class' => AdjacencyFullTreeDataAction::className(),
                'class_name' => Category::className(),
                'model_label_attribute' => 'name',
            ],
            'move' => [
                'class' => TreeNodeMoveAction::className(),
                'className' => Category::className(),
            ],
            'reorder' => [
                'class' => TreeNodesReorderAction::className(),
                'className' => Category::className(),
            ],
            'addImage' => [
                'class' => AddImageAction::className(),
            ],
            'upload' => [
                'class' => UploadAction::className(),
                'upload' => 'theme/resources/product-images',
            ],
            'remove' => [
                'class' => RemoveAction::className(),
                'uploadDir' => 'theme/resources/product-images',
            ],
            'save-info' => [
                'class' => SaveInfoAction::className(),
            ],
            'property-handler' => [
                'class' => PropertyHandler::className(),
                'modelName' => Category::className()
            ],
            'batch-edit-price' => [
                'class' => BatchEditPriceAction::className(),
            ]
        ];
    }

    public function actionIndex($parent_id = 0)
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
            $parent = Category::findById($parent_id, null, null);
            if ($parent_id === '0' || !is_null($parent)) {
                $model = new Category;
                $model->loadDefaultValues();
                $model->parent_id = $parent_id;
                if ($parent_id !== '0') {
                    $model->category_group_id = $parent->category_group_id;
                }
            } else {
                throw new ServerErrorHttpException;
            }
        }

        if (null === $model) {
            throw new ServerErrorHttpException;
        }

        $event = new BackendEntityEditEvent($model);
        $this->trigger(self::BACKEND_CATEGORY_EDIT, $event);

        $post = \Yii::$app->request->post();
        if ($event->isValid && $model->load($post) && $model->validate()) {
            $saveStateEvent = new BackendEntityEditEvent($model);
            $this->trigger(self::BACKEND_CATEGORY_EDIT_SAVE, $saveStateEvent);

            $save_result = $model->save();
            $model->saveProperties($post);

            if (null !== $view_object = ViewObject::getByModel($model, true)) {
                if ($view_object->load($post, 'ViewObject')) {
                    if ($view_object->view_id <= 0) {
                        $view_object->delete();
                    } else {
                        $view_object->save();
                    }
                }
            }

            if ($save_result) {
                $modelAfterSaveEvent = new BackendEntityEditEvent($model);
                $this->trigger(self::BACKEND_CATEGORY_AFTER_SAVE, $modelAfterSaveEvent);

                $this->runAction('save-info', ['model_id'=>$model->id]);
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                'edit',
                                'returnUrl' => $returnUrl,
                                'parent_id' => Yii::$app->request->get('parent_id', null)
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    'edit',
                                    'id' => $model->id,
                                    'returnUrl' => $returnUrl,
                                    'parent_id' => $model->parent_id
                                ]
                            )
                        );
                }
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

    public function actionDelete($id = null, $parent_id = null, $mode = null)
    {

        if ((null === $id) || (null === $model = Category::findById($id, null, null))) {
            throw new NotFoundHttpException;
        }

        $model->deleteMode = $mode;
        if (!$model->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'The object is placed in the cart'));
        } else {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Object has been removed'));
        }

        return $this->redirect(
            Yii::$app->request->get(
                'returnUrl',
                Url::to(['index', 'parent_id' => $model->parent_id])
            )
        );

    }

    public function actionRemoveAll($parent_id, $mode = null)
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Category::findAll(['id' => $items]);
            /** @var Category[] $items */
            foreach ($items as $item) {
                $item->deleteMode = $mode;
                $item->delete();
            }
        }
        return $this->redirect(['index', 'parent_id' => $parent_id]);
    }

    public function actionAutocomplete()
    {
        /**
         * @todo Добавить отображение вложенности
         */
        $search = Yii::$app->request->get('search');
        $out = ['more' => false];
        if (!is_null($search['term'])) {
            $query = new Query;
            $query->select('id, name AS text')->from(Category::tableName())->andWhere(['like', 'name', $search['term']])->limit(
                100
            );
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }/* elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Category::findOne($id)->name];
        } else {
            $out['results'] = ['id' => 0, 'text' => Yii::t('app', 'No matching records found')];
        }*/
        echo Json::encode($out);
    }
}
