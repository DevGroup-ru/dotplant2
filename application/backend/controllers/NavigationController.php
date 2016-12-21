<?php

namespace app\backend\controllers;

use app\backend\actions\UpdateEditable;
use app\modules\image\widgets\RemoveAction;
use app\modules\image\widgets\SaveInfoAction;
use app\modules\image\widgets\UploadAction;
use app\modules\image\widgets\views\AddImageAction;
use app\widgets\navigation\models\Navigation;
use devgroup\JsTreeWidget\AdjacencyFullTreeDataAction;
use devgroup\JsTreeWidget\TreeNodeMoveAction;
use devgroup\JsTreeWidget\TreeNodesReorderAction;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;

class NavigationController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['navigation manage'],
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
                'class_name' => Navigation::className(),
                'model_label_attribute' => 'name',
            ],
            'move' => [
                'class' => TreeNodeMoveAction::className(),
                'className' => Navigation::className(),
            ],
            'reorder' => [
                'class' => TreeNodesReorderAction::className(),
                'className' => Navigation::className(),
            ],
            'addImage' => [
                'class' => AddImageAction::className(),
            ],
            'upload' => [
                'class' => UploadAction::className(),
            ],
            'remove' => [
                'class' => RemoveAction::className(),
            ],
            'save-info' => [
                'class' => SaveInfoAction::className(),
            ],
            'update-editable' => [
                'class' => UpdateEditable::className(),
                'modelName' => Navigation::className(),
                'allowedAttributes' => [
                    'active' => function (Navigation $model) {
                        if ($model === null || $model->active === null) {
                            return null;
                        }
                        if ($model->active === 1) {
                            $label_class = 'label-success';
                            $value = 'Active';
                        } else {
                            $value = 'Inactive';
                            $label_class = 'label-default';
                        }
                        return \yii\helpers\Html::tag(
                            'span',
                            Yii::t('app', $value),
                            ['class' => "label $label_class"]
                        );
                    },
                ],
            ],
        ];
    }

    public function actionIndex($parent_id = 0)
    {
        $searchModel = new Navigation(['scenario' => 'search']);
        $searchModel->parent_id = $parent_id;
        $dataProvider = $searchModel->search($_GET);

        $model = null;
        if ($parent_id > 0) {
            $model = Navigation::findOne($parent_id);
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
        if ($id === null) {
            $model = new Navigation(['parent_id' => $parent_id]);
        } else {
            $model = Navigation::findOne($id);
        }

        $post = Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            if ($model->save()) {
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/backend/navigation/index', 'id' => $model->id]);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/backend/navigation/edit',
                                'parent_id' => $model->parent_id,
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/backend/navigation/edit',
                                    'id' => $model->id,
                                    'parent_id' => $model->parent_id,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
            } else {
                \Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot update data'));
            }
        }

        return $this->render(
            'navigation-form',
            [
                'model' => $model,
            ]
        );
    }

    public function actionDelete($id)
    {
        $model = Navigation::findOne($id);
        $model->delete();
        Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        return $this->redirect(
            Url::to(
                [
                    '/backend/navigation/index',
                    'parent_id' => $model->parent_id,
                ]
            )
        );
    }

    public function actionRemoveAll($parent_id)
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Navigation::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index', 'parent_id' => $parent_id]);
    }
}
