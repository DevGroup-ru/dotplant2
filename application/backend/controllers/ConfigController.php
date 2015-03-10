<?php

namespace app\backend\controllers;

use app\backend\actions\JSTreeGetTrees;
use app\components\SearchModel;
use app\models\Config;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;

/**
 * ConfigController implements the CRUD actions for Config model.
 */
class ConfigController extends Controller
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
                'class' => JSTreeGetTrees::className(),
                'modelName' => Config::className(),
                'label_attribute' => 'name',
                'vary_by_type_attribute' => null,
                'show_deleted' => null
            ],
        ];
    }

    /**
     * Lists all Config models.
     * @param int $parent_id
     * @return string
     */
    public function actionIndex($parent_id = 0)
    {
        $searchModel = new SearchModel(
            [
                'model' => Config::className(),
                'partialMatchAttributes' => ['name', 'key', 'value'],
            ]
        );
        $searchModel->parent_id = $parent_id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Updates an existing Config model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param null|string $id
     * @param int $parent_id
     * @return string|\yii\web\Response
     */
    public function actionUpdate($id = null, $parent_id = 0)
    {
        if ($id === null) {
            $model = new Config;
            $model->parent_id = $parent_id;
        } else {
            $model = $this->findModel($id);
        }
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                // email config?
                $emailParent = Config::find()->where(['path' => 'core.emailConfig'])->one();
                if ($emailParent->id === $model->parent_id) {
                    $emailConfAR = Config::find()->where(['parent_id' => $emailParent->id])->all();
                    $emailConf = [
                        'class' => 'yii\swiftmailer\Mailer',
                        'transport' => [],
                    ];
                    foreach($emailConfAR as $cf) {
                        if ($cf->name === 'transport') {
                            $cf->name = 'class';
                        }
                        $emailConf['transport'][$cf->name] = $cf->value;
                    }

                    file_put_contents(
                        Yii::getAlias('@config') . "/email-config.php",
                        "<?php\nreturn " . VarDumper::export($emailConf) . ";\n"
                    );
                }
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/backend/config/index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/backend/config/update',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/backend/config/update',
                                    'id' => $model->id,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }

            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }
        return $this->render(
            'update',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Deletes an existing Config model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionRemoveAll($parent_id)
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Config::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index', 'parent_id' => $parent_id]);
    }

    /**
     * Finds the Config model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Config the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Config::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
