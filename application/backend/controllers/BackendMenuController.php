<?php

namespace app\backend\controllers;

use app\backend\models\BackendMenu;
use devgroup\JsTreeWidget\AdjacencyFullTreeDataAction;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class BackendMenuController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['setting manage'],
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
                'class_name' => BackendMenu::className(),
                'model_label_attribute' => 'name',
            ],
        ];
    }

    public function actionIndex($parent_id = 1)
    {
        $searchModel = new BackendMenu();
        $searchModel->parent_id = $parent_id;

        $params = Yii::$app->request->get();

        $dataProvider = $searchModel->search($params);

        $model = null;
        if ($parent_id > 0) {
            $model = BackendMenu::findOne($parent_id);
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

        /** @var null|BackendMenu|HasProperties $model */
        $model = null;
        if (null !== $id) {
            $model = BackendMenu::findById($id);
        } else {
            if (null !== $parent = BackendMenu::findById($parent_id)) {
                $model = new BackendMenu;
                $model->loadDefaultValues();
                $model->parent_id = $parent_id;

            } else {
                $model = new BackendMenu;
                $model->loadDefaultValues();
                $model->parent_id = 0;
            }
        }

        if (null === $model) {
            throw new ServerErrorHttpException;
        }

        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            if ($model->save()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/backend/backend-menu/index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/backend/backend-menu/edit',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/backend/backend-menu/edit',
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
            'form',
            [
                'model' => $model,
            ]
        );
    }

    public function actionDelete($id = null, $parent_id = null)
    {

        if ((null === $id) || (null === $model = BackendMenu::findById($id))) {
            throw new NotFoundHttpException;
        }

        if (!$model->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'The object is placed in the cart'));
        } else {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Object has been removed'));
        }

        return $this->redirect(Url::to(['index', 'parent_id' => $model->parent_id]));
    }

    public function actionRemoveAll($parent_id)
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = BackendMenu::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index', 'parent_id' => $parent_id]);
    }


    public function actionAjaxToggle($status)
    {
        if (!Yii::$app->request->isAjax) {
             throw new HttpException(403);
        }
        $currentStatus = Yii::$app->request->cookies->getValue('backend_menu', 'normal');
        $cookieData =[
            'name' => 'backend_menu',
            'value' => 'normal',
            'expire' => time() + 86400 * 365,
        ];
        if ($status !== $currentStatus) {
            $cookieData['value'] = $status;
        }
        Yii::$app->response->cookies->add(new Cookie($cookieData));

    }
}
