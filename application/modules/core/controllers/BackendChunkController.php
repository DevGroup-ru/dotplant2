<?php

namespace app\modules\core\controllers;

use app\modules\config\controllers\BackendController;
use yii\filters\AccessControl;
use app\modules\core\models\ContentBlock;
use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class BackendChunkController extends BackendController
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

    public function actionIndex()
    {
        $searchModel = new ContentBlock(['scenario' => 'search']);
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
        
    }

    public function actionEdit($id = null)
    {
        /** @var null|Page|HasProperties $model */
        $model = new ContentBlock();
        if ($id !== null) {
            $model = ContentBlock::findOne($id);
        }

        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {

            $save_result = $model->save();

            if ($save_result) {
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/core/backend-chunk/index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/core/backend-chunk/edit',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/core/backend-chunk/index',
                                    'id' => $model->id,
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
}