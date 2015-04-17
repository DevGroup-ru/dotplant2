<?php
/**
 * Created by PhpStorm.
 * User: ivansal
 * Date: 14.04.15
 * Time: 17:35
 */

namespace app\backend\controllers;


use app\models\Config;
use app\models\Watermark;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

class WatermarkController extends Controller
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
                $path = Config::getValue(
                        'image.waterDir',
                        '/theme/resources/product-images/watermark'
                    ) . '/' . $image->name;
                $model->watermark_src = $path;
                $image->saveAs(Yii::getAlias('@webroot') . $path);
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