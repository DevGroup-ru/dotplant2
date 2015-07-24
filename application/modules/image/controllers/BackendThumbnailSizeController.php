<?php

namespace app\modules\image\controllers;

use app\modules\image\models\ThumbnailSize;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

class BackendThumbnailSizeController extends \app\backend\components\BackendController
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

    public function actionIndex()
    {
        $searchModel = new ThumbnailSize;

        $params = Yii::$app->request->get();
        $dataProvider = $searchModel->search($params);

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
        if ($id !== null) {
            $model = ThumbnailSize::findOne($id);
        } else {
		$model = new ThumbnailSize();
		$model->loadDefaultValues();
	}

        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $save_result = $model->save();

            if ($save_result === true) {
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                'edit',
                                'returnUrl' => $returnUrl,
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
        $model = ThumbnailSize::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }
        $model->delete();

        Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));


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
            $items = ThumbnailSize::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index']);
    }
}