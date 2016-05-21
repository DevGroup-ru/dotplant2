<?php

namespace app\modules\core\controllers;

use app\backend\components\BackendController;
use app\backend\traits\BackendRedirect;
use app\modules\core\models\ContentBlockGroup;
use devgroup\JsTreeWidget\AdjacencyFullTreeDataAction;
use devgroup\JsTreeWidget\TreeNodeMoveAction;
use devgroup\JsTreeWidget\TreeNodesReorderAction;
use yii\filters\AccessControl;
use app\modules\core\models\ContentBlock;
use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class BackendChunkController extends BackendController
{
    use BackendRedirect;

    public function actions()
    {
        return [
            'getTree' => [
                'class' => AdjacencyFullTreeDataAction::className(),
                'class_name' => ContentBlockGroup::className(),
                'model_label_attribute' => 'name',
            ],
            'move' => [
                'class' => TreeNodeMoveAction::className(),
                'className' => ContentBlockGroup::className(),
            ],
            'reorder' => [
                'class' => TreeNodesReorderAction::className(),
                'className' => ContentBlockGroup::className(),
            ],
        ];
    }

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

    public function actionIndex()
    {
        $searchModel = new ContentBlock(['scenario' => ContentBlock::SCENARIO_SEARCH]);
        $group_id = Yii::$app->request->get('group_id', null);
        $searchModel->group_id = $group_id;
        $dataProvider = $searchModel->search($_GET);


        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'parent_id' => $group_id
            ]
        );
    }

    public function actionEdit($id = null, $group_id = null)
    {
        /** @var null|ContentBlock $model */
        $model = new ContentBlock();
        if ($id !== null) {
            $model = ContentBlock::findOne($id);
        }

        if ($model->isNewRecord === true && $group_id !== null) {
            $model->group_id = $group_id;
        }
        $model->loadDefaultValues();


        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            if (!empty($model->newGroup)) {
                $group = new ContentBlockGroup([
                    'name' => $model->newGroup,
                ]);
                $group->loadDefaultValues();
                if ($group->save()) {
                    $model->group_id = $group->id;
                }
            }
            $save_result = $model->save();
            if ($save_result) {
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                $this->redirectUser($model->id);
            } else {
                \Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot update data'));
            }
        }
        return $this->render(
            'edit',
            [
                'model' => $model,
            ]
        );
    }

    public function actionDelete($id = null)
    {
        if ((null === $id) || (null === $model = ContentBlock::findOne($id))) {
            throw new NotFoundHttpException;
        }
        if ($model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        }
        return $this->redirect(
            Yii::$app->request->get(
                'returnUrl',
                Url::toRoute(['index'])
            )
        );
    }

    public function actionRemoveAll()
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = ContentBlock::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }
        return $this->redirect(['index']);
    }
}