<?php

namespace app\backgroundtasks\controllers;

use app\backgroundtasks\models\Task;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;

/**
 * Class ManageController
 * @package app\backgroundtasks\controllers
 * @author evgen-d <flynn068@gmail.com>
 */
class ManageController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => Yii::$app->getModule('background')->manageRoles,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    /**
     * View all Task models.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new Task(['scenario' => 'search']);
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * Updates an existing Task model.
     * If update is successful, the browser will be redirected to the 'index' page.
     * @param $id
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        /* @var $model Task|null */
        $model = Task::find()->where(
            [
                'id' => $id,
                'type' => Task::TYPE_REPEAT,
            ]
        )->one();
        if ($model !== null) {
            $model->scenario = 'repeat';
            if ($model->load($_POST) && $model->validate()) {
                $model->fail_counter = 0;
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                    $returnUrl = Yii::$app->request->get('returnUrl', ['background/manage/index']);
                    switch (Yii::$app->request->post('action', 'save')) {
                        case 'next':
                            return $this->redirect(
                                [
                                    '/background/manage/create',
                                    'returnUrl' => $returnUrl,
                                ]
                            );
                        case 'back':
                            return $this->redirect($returnUrl);
                        default:
                            return $this->redirect(
                                Url::toRoute(
                                    [
                                        '/background/manage/update',
                                        'id' => $model->id,
                                        'returnUrl' => $returnUrl,
                                    ]
                                )
                            );
                    }
                }

            } else {
                return $this->render(
                    'update',
                    [
                        'model' => $model,
                    ]
                );
            }
        } else {
            throw new NotFoundHttpException('repeated task #'.$id.' not found');
        }
    }

    /**
     * Creates a new Task model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Task(['scenario' => 'repeat']);
        $model->initiator = Yii::$app->user->id;
        $model->type = Task::TYPE_REPEAT;

        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
            $returnUrl = Yii::$app->request->get('returnUrl', ['background/manage/index']);
            switch (Yii::$app->request->post('action', 'save')) {
                case 'next':
                    return $this->redirect(
                        [
                            '/background/manage/create',
                            'returnUrl' => $returnUrl,
                        ]
                    );
                case 'back':
                    return $this->redirect($returnUrl);
                default:
                    return $this->redirect(
                        Url::toRoute(
                            [
                                '/background/manage/update',
                                'id' => $model->id,
                                'returnUrl' => $returnUrl,
                            ]
                        )
                    );
            }

        } else {
            return $this->render(
                'create',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Deletes an existing Task model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        Task::deleteAll('id = :id', [':id' => $id]);
        return $this->redirect(['index']);
    }

    /**
     * Deletes an existing Task models.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @return int|false number of tasks deleted or false if there was no task provided for deletion
     */
    public function actionDeleteTasks()
    {
        if (isset($_POST['tasks'])) {
            return Task::deleteAll([
                    'in',
                    'id',
                    $_POST['tasks'],
            ]);
        }
        return false;
    }
}
