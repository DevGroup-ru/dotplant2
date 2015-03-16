<?php

namespace app\backend\controllers;

use app\models\Object;
use app\models\Page;
use app\models\ViewObject;
use app\properties\HasProperties;
use app\widgets\image\RemoveAction;
use app\widgets\image\SaveInfoAction;
use app\widgets\image\UploadAction;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PageController extends Controller
{

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
                'class' => 'app\backend\actions\JSTreeGetTrees',
                'modelName' => 'app\models\Page',
                'label_attribute' => 'name',
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
        }
        $model->parent_id = $parent_id;

        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
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
                $this->runAction('save-info');
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/backend/page/index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/backend/page/edit',
                                'returnUrl' => $returnUrl,
                                'parent_id' =>Yii::$app->request->get('parent_id', null)
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/backend/page/edit',
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
            Yii::$app->session->setFlash('info', Yii::t('shop', 'The object is placed in the cart'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }

        return $this->redirect(Url::toRoute(['index', 'parent_id' => $model->parent_id]));
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

        return $this->redirect(Url::toRoute(['edit', 'id' => $id, 'parent_id' => $parent_id]));
    }
}
