<?php

namespace app\modules\image\controllers;

use app\modules\image\models\Watermark;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class BackendWatermarkController extends \app\backend\components\BackendController
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
        $searchModel = new Watermark();

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
        $model = new Watermark();
        if ($id !== null) {
            $model = Watermark::findOne($id);
        }

        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            $image = UploadedFile::getInstance($model, 'image');
            if ($image !== null) {
                $path = Yii::$app->getModule('image')->watermarkDirectory . '/' . $image->name;
                $model->watermark_path = $path;
                $stream = fopen($image->tempName, 'r+');
                Yii::$app->getModule('image')->fsComponent->writeStream($path, $stream);
                fclose($stream);
            }
            if ($model->validate() && $model->save()) {
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
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot update data'));
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
        $model = Watermark::findOne($id);
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
            $items = Watermark::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index']);
    }
}