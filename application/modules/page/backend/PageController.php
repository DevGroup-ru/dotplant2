<?php

namespace app\modules\page\backend;

use app\backend\actions\PropertyHandler;
use app\backend\events\BackendEntityEditEvent;
use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\Property;
use app\modules\image\models\Image;
use app\modules\image\widgets\views\AddImageAction;
use app\modules\page\models\Page;
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
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class PageController extends \app\backend\components\BackendController
{

    const BACKEND_PAGE_EDIT = 'backend-page-edit';
    const BACKEND_PAGE_EDIT_SAVE = 'backend-page-edit-save';
    const BACKEND_PAGE_EDIT_FORM = 'backend-page-edit-form';
    const BACKEND_PAGE_AFTER_SAVE = 'backend-page-after-save';

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

    public function actions()
    {
        return [
            'getTree' => [
                'class' => AdjacencyFullTreeDataAction::className(),
                'class_name' => Page::className(),
                'model_label_attribute' => 'name',
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
                'modelName' => Page::className()
            ],
            'move' => [
                'class' => TreeNodeMoveAction::className(),
                'className' => Page::className(),
                'saveAttributes' => ['slug_compiled'],
            ],
            'reorder' => [
                'class' => TreeNodesReorderAction::className(),
                'className' => Page::className(),
            ],
        ];
    }

    public function actionIndex($parent_id = 1)
    {
        $searchModel = new Page();
        $searchModel->parent_id = $parent_id;

        $params = Yii::$app->request->get();
        $dataProvider = $searchModel->search($params);

        $model = null;
        if ($parent_id > 0) {
            $model = Page::findOne($parent_id);
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

    public function actionEdit($parent_id, $id = null)
    {
        $object = Object::getForClass(Page::className());

        /** @var null|Page|HasProperties $model */
        $model = new Page;
        $model->published = 1;
        if ($id !== null) {
            $model = Page::findOne($id);
            if ($model === null) {
                throw new NotFoundHttpException;
            }
        }
        $model->parent_id = $parent_id;

        $event = new BackendEntityEditEvent($model);
        $this->trigger(self::BACKEND_PAGE_EDIT, $event);

        $post = \Yii::$app->request->post();
        if ($event->isValid && $model->load($post)) {
            $saveStateEvent = new BackendEntityEditEvent($model);
            $this->trigger(self::BACKEND_PAGE_EDIT_SAVE, $saveStateEvent);

            if ($saveStateEvent->isValid && $model->validate()) {
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
                    $this->trigger(self::BACKEND_PAGE_AFTER_SAVE, $modelAfterSaveEvent);

                    $this->runAction('save-info', ['model_id' => $model->id]);
                    Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                    $returnUrl = Yii::$app->request->get('returnUrl', ['/page/backend/index']);
                    switch (Yii::$app->request->post('action', 'save')) {
                        case 'next':
                            return $this->redirect(
                                [
                                    '/page/backend/edit',
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
                                        '/page/backend/edit',
                                        'id' => $model->id,
                                        'returnUrl' => $returnUrl,
                                        'parent_id' => $model->parent_id
                                    ]
                                )
                            );
                    }
                } else {
                    \Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot update data'));
                }
            }
        }

        return $this->render(
            'page-form',
            [
                'model' => $model,
                'object' => $object,
            ]
        );
    }

    /*
     *
     */
    public function actionDelete($id = null)
    {
        if ((null === $id) || (null === $model = Page::findOne($id))) {
            throw new NotFoundHttpException;
        }

        if (!$model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'The object is placed in the cart'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }

        return $this->redirect(
            Yii::$app->request->get(
                'returnUrl',
                Url::toRoute(['index', 'parent_id' => $model->parent_id])
            )
        );
    }

    public function actionRemoveAll($parent_id)
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Page::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index', 'parent_id' => $parent_id]);
    }

    /*
     *
     */
    public function actionRestore($id = null, $parent_id = null)
    {
        if (null === $id) {
            new NotFoundHttpException();
        }

        if (null === $model = Page::findOne(['id' => $id])) {
            new NotFoundHttpException();
        }

        $model->restoreFromTrash();

        Yii::$app->session->setFlash('success', Yii::t('app', 'Object successfully restored'));

        return $this->redirect(
            Yii::$app->request->get(
                'returnUrl',
                Url::toRoute(['edit', 'id' => $id, 'parent_id' => $parent_id])
            )
        );
    }


    /**
     * Clone page action.
     * @param integer $id
     * @param array|string $returnUrl
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionClone($id, $returnUrl = ['index'])
    {
        /** @var Page|HasProperties $model */
        $model = Page::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        /** @var Page|HasProperties $newModel */
        $newModel = new Page;
        $newModel->setAttributes($model->attributes, false);
        $time = time();
        $newModel->name .= ' (copy ' . date('Y-m-d h:i:s', $time) . ')';
        $newModel->slug .= '-copy-' . date('Ymdhis', $time);
        $newModel->title .= '-copy-' . date('Ymdhis', $time);
        $newModel->id = null;
        if ($newModel->validate() === false) {
            $newModel->slug = substr(uniqid() . "-" . $model->slug, 0, 80);
        }
        if ($newModel->save()) {
            $object = Object::getForClass(get_class($newModel));
            $query = new Query();

            // save images bindings
            $params = $query->select(
                ['object_id', 'filename', 'image_title', 'image_alt', 'sort_order']
            )->from(Image::tableName())->where(
                [
                    'object_id' => $object->id,
                    'object_model_id' => $model->id
                ]
            )->all();
            if (!empty($params)) {
                $rows = [];
                foreach ($params as $param) {
                    $rows[] = [
                        $param['object_id'],
                        $newModel->id,
                        $param['filename'],
                        $param['image_title'],
                        $param['image_alt'],
                        $param['sort_order'],
                    ];
                }
                Yii::$app->db->createCommand()->batchInsert(
                    Image::tableName(),
                    [
                        'object_id',
                        'object_model_id',
                        'filename',
                        'image_title',
                        'image_alt',
                        'sort_order',
                    ],
                    $rows
                )->execute();
            }
            $newModelProps = [];
            foreach (array_keys($model->propertyGroups) as $key) {
                $opg = new ObjectPropertyGroup();
                $opg->attributes = [
                    'object_id' => $object->id,
                    'object_model_id' => $newModel->id,
                    'property_group_id' => $key,
                ];
                $opg->save();
                $props = Property::getForGroupId($key);
                foreach ($props as $prop) {
                    $propValues = $model->getPropertyValuesByPropertyId($prop->id);
                    if ($propValues !== null) {
                        foreach ($propValues->values as $val) {
                            $valueToSave = ArrayHelper::getValue($val, 'psv_id', $val['value']);
                            $newModelProps[$prop->key][] = $valueToSave;
                        }
                    }
                }
            }
            $newModel->saveProperties(
                [
                    'Properties_Page_' . $newModel->id => $newModelProps,
                ]
            );
            $view = ViewObject::findOne(['object_id' => $model->object->id, 'object_model_id' => $model->id]);
            if ($view !== null) {
                $newView = new ViewObject;
                $newView->setAttributes($view->attributes, false);
                $newView->id = null;
                $newView->object_model_id = $newModel->id;
                $newView->save();
            }
            Yii::$app->session->setFlash('success', Yii::t('app', 'Page has been cloned successfully.'));
            $this->redirect(
                ['edit', 'id' => $newModel->id, 'parent_id' => $newModel->parent_id, 'returnUrl' => $returnUrl]
            );
        }
    }
}
