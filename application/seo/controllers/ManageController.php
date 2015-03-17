<?php

namespace app\seo\controllers;

use app\seo\models\Config;
use app\seo\models\Counter;
use app\seo\models\Meta;
use app\seo\models\Redirect;
use app\seo\models\Robots;
use devgroup\ace\AceHelper;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;
use Yii;

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
                        'actions' => ['get-robots'],
                        'roles' => ['?']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['flush-meta-cache', 'flush-counter-cache', 'flush-robots-cache'],
                        'roles' => ['cache manage'],
                    ],
                    [
                        'allow' => false,
                        'actions' => ['flush-meta-cache', 'flush-counter-cache', 'flush-robots-cache'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['seo manage'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //
                ],
            ],
            [
              'class' => 'yii\filters\PageCache',
              'only' => [ 'GetRobots' ],
              'duration' => 24 * 60 * 60,
              'dependency' => ActiveRecordHelper::getCommonTag(Config::className()),
            ]
        ];
    }

    /**
     * Index page
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * return robots.txt
     */
    public function actionGetRobots()
    {
        $robots = Robots::getRobots();
        $response = \Yii::$app->response;
        $response->headers->set('Content-Type', 'text/plain');
        $response->format = \yii\web\Response::FORMAT_RAW;
        $response->data = $robots;
        \Yii::$app->end();
    }

    /**
     * Show list of Meta tag models
     * @return string
     */
    public function actionMeta()
    {
        $searchModel = new Meta();
        $dataProvider = $searchModel->search($_GET);
        return $this->render(
            'meta',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * Updates an existing Meta model.
     * If update is successful, the browser will be redirected to the 'meta' page.
     * @param $id
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdateMeta($id)
    {
        /* @var $model Meta */
        $model = Meta::find()->where(
            [
                'key' => $id,
            ]
        )->one();
        if ($model !== null) {
            if ($model->load($_POST) && $model->validate()) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                    $returnUrl = Yii::$app->request->get('returnUrl', ['/seo/manage/meta']);
                    switch (Yii::$app->request->post('action', 'save')) {
                        case 'next':
                            return $this->redirect(
                                [
                                    '/seo/manage/create-meta',
                                    'returnUrl' => $returnUrl,
                                ]
                            );
                        case 'back':
                            return $this->redirect($returnUrl);
                        default:
                            return $this->redirect(
                                Url::toRoute(
                                    [
                                        '/seo/manage/update-meta',
                                        'id' => $model->getPrimaryKey(),
                                        'returnUrl' => $returnUrl,
                                    ]
                                )
                            );
                    }
                }

            } else {
                return $this->render(
                    'updateMeta',
                    [
                        'model' => $model,
                    ]
                );
            }
        } else {
            throw new NotFoundHttpException('Meta tag '.$id.' not found');
        }
    }

    /**
     * Creates a new Meta model.
     * If creation is successful, the browser will be redirected to the 'meta' page.
     * @return string|\yii\web\Response
     */
    public function actionCreateMeta()
    {
        $model = new Meta();
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
            $returnUrl = Yii::$app->request->get('returnUrl', ['/seo/manage/meta']);
            switch (Yii::$app->request->post('action', 'save')) {
                case 'next':
                    return $this->redirect(
                        [
                            '/seo/manage/create-meta',
                            'returnUrl' => $returnUrl,
                        ]
                    );
                case 'back':
                    return $this->redirect($returnUrl);
                default:
                    return $this->redirect(
                        Url::toRoute(
                            [
                                '/seo/manage/update-meta',
                                'id' => $model->id,
                                'returnUrl' => $returnUrl,
                            ]
                        )
                    );
            }
        } else {
            return $this->render(
                'createMeta',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Deletes an existing Meta model.
     * If deletion is successful, the browser will be redirected to the 'meta' page.
     * @param $id
     * @return \yii\web\Response
     */
    public function actionDeleteMeta($id)
    {
        Meta::deleteAll('`key` = :id', [':id' => $id]);
        return $this->redirect(['meta']);
    }

    /**
     * Deletes an existing Meta models.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @return \yii\web\Response
     */
    public function actionDeleteMetas()
    {
        if (isset($_POST['metas'])) {
            return Meta::deleteAll(
                [
                    'in',
                    'key',
                    $_POST['metas'],
                ]
            );
        }
        return false;
    }

    /**
     * Deletes meta value from cache
     * @return bool if no error happens during deletion
     */
    public function actionFlushMetaCache()
    {
        return \Yii::$app->getCache()->delete(\Yii::$app->getModule('seo')->cacheConfig['metaCache']['name']);
    }

    /**
     * Show list of Counter models
     * @return string
     */
    public function actionCounter()
    {
        $searchModel = new Counter();
        $dataProvider = $searchModel->search($_GET);
        AceHelper::setAceScript($this);
        return $this->render(
            'counter',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * Updates an existing Counter model.
     * If update is successful, the browser will be redirected to the 'meta' page.
     * @param $id
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdateCounter($id)
    {
        /* @var $model Counter */
        $model = Counter::find()->where(
            [
                'id' => $id,
            ]
        )->one();
        AceHelper::setAceScript($this);
        if ($model !== null) {
            if ($model->load($_POST) && $model->validate()) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                    $returnUrl = Yii::$app->request->get('returnUrl', ['/seo/manage/counter']);
                    switch (Yii::$app->request->post('action', 'save')) {
                        case 'next':
                            return $this->redirect(
                                [
                                    '/seo/manage/create-counter',
                                    'returnUrl' => $returnUrl,
                                ]
                            );
                        case 'back':
                            return $this->redirect($returnUrl);
                        default:
                            return $this->redirect(
                                Url::toRoute(
                                    [
                                        '/seo/manage/update-counter',
                                        'id' => $model->id,
                                        'returnUrl' => $returnUrl
                                    ]
                                )
                            );
                    }
                }

            } else {
                return $this->render(
                    'updateCounter',
                    [
                        'model' => $model,
                    ]
                );
            }
        } else {
            throw new NotFoundHttpException('counter #'.$id.' not found');
        }
    }

    /**
     * Creates a new Counter model.
     * If creation is successful, the browser will be redirected to the 'counter' page.
     * @return string|\yii\web\Response
     */
    public function actionCreateCounter()
    {
        $model = new Counter();
        AceHelper::setAceScript($this);
        if ($model->load($_POST) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
            $returnUrl = Yii::$app->request->get('returnUrl', ['/seo/manage/counter']);
            if (Yii::$app->request->post('action', 'back') == 'next') {
                $route = ['/seo/manage/create-counter', 'returnUrl' => $returnUrl];
                if (!is_null(Yii::$app->request->get('parent_id', null))) {
                    $route['parent_id'] = Yii::$app->request->get('parent_id');
                }
                return $this->redirect($route);
            } else {
                return $this->redirect($returnUrl);
            }

        } else {
            return $this->render(
                'createCounter',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Deletes an existing Meta model.
     * If deletion is successful, the browser will be redirected to the 'counter' page.
     * @param $id
     * @return \yii\web\Response
     */
    public function actionDeleteCounter($id)
    {
        Counter::deleteAll('`id` = :id', [':id' => $id]);
        return $this->redirect(['counter']);
    }

    /**
     * Deletes an existing Counter models.
     * If deletion is successful, the browser will be redirected to the 'counter' page.
     * @return \yii\web\Response
     */
    public function actionDeleteCounters()
    {
        if (isset($_POST['counters'])) {
            return Counter::deleteAll(
                [
                    'in',
                    'id',
                    $_POST['counters'],
                ]
            );
        }
        return false;
    }

    /**
     * Deletes counter value from cache
     * @return bool if no error happens during deletion
     */
    public function actionFlushCounterCache()
    {
        return \Yii::$app->getCache()->delete(\Yii::$app->getModule('seo')->cacheConfig['counterCache']['name']);
    }

    /**
     * Update robots.txt
     * @return string|\yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionRobots()
    {
        $model = Config::findOne(Robots::KEY_ROBOTS);
        if ($model === null) {
            $model = new Robots();
        }
        if ($model->load($_POST) && $model->validate()) {
            $model->save();
            return $this->redirect(['robots']);
        }
        return $this->render('robots', ['model' => $model]);
    }

    /**
     * Deletes robots value from cache
     * @return bool if no error happens during deletion
     */
    public function actionFlushRobotsCache()
    {
        return \Yii::$app->getCache()->delete(\Yii::$app->getModule('seo')->cacheConfig['robotsCache']['name']);
    }

    /**
     * Show list of Redirect models
     * @return string
     */
    public function actionRedirect()
    {
        $searchModel = new Redirect(['scenario' => 'search']);
        $dataProvider = $searchModel->search($_GET);
        return $this->render(
            'redirect',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * Creates a new Redirect model.
     * If creation is successful, the browser will be redirected to the 'redirect' page.
     * @return string|\yii\web\Response
     */
    public function actionCreateRedirect()
    {
        $model = new Redirect(['active' => true]);
        if ($model->load($_POST) && $model->save()) {
            return $this->redirect(['redirect']);
        } else {
            return $this->render(
                'createRedirect',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Creates a new Redirect models.
     * @return string
     */
    public function actionCreateRedirects()
    {
        if (isset($_POST['redirects'])) {
            $all = 0;
            $added = 0;
            if (isset($_POST['redirects']['static']) && !empty($_POST['redirects']['static'])) {
                $static = explode("\n", $_POST['redirects']['static']);
                foreach ($static as $redirectStr) {
                    $all++;
                    $r = explode("\t", $redirectStr);
                    $redirect = new Redirect(
                        [
                            'type' => Redirect::TYPE_STATIC,
                            'from' => trim($r[0]),
                            'to' => trim($r[1]),
                            'active' => true,
                        ]
                    );
                    if ($redirect->save()) {
                        $added++;
                    }
                }
            }
            if (isset($_POST['redirects']['regular']) && !empty($_POST['redirects']['regular'])) {
                $regular = explode("\n", $_POST['redirects']['regular']);
                foreach ($regular as $redirectStr) {
                    $all++;
                    $r = explode("\t", $redirectStr);
                    $redirect = new Redirect(
                        [
                            'type' => Redirect::TYPE_PREG,
                            'from' => trim($r[0]),
                            'to' => trim($r[1]),
                            'active' => true,
                        ]
                    );
                    if ($redirect->save()) {
                        $added++;
                    }
                }
            }

            Yii::$app->session->setFlash('success', Yii::t('app', 'Records has been saved'));
            $returnUrl = Yii::$app->request->get('returnUrl', ['/seo/manage/redirect']);
            switch (Yii::$app->request->post('action', 'save')) {
                case 'next':
                    return $this->redirect(
                        [
                            '/seo/manage/create-redirects',
                            'returnUrl' => $returnUrl,
                        ]
                    );
                case 'back':
                    return $this->redirect($returnUrl);
                default:
                    return $this->redirect(
                        Url::toRoute(
                            [
                                '/seo/manage/update-redirect',
                                'id' => $redirect->id,
                                'returnUrl' => $returnUrl,
                            ]
                        )
                    );
            }

        } else {
            return $this->render('createRedirects');
        }

    }

    /**
     * Updates an existing Redirect model.
     * If update is successful, the browser will be redirected to the 'redirect' page.
     * @param $id
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionUpdateRedirect($id)
    {
        /* @var $model Redirect */
        $model = Redirect::find()->where(
            [
                'id' => $id,
            ]
        )->one();
        if ($model !== null) {
            if ($model->load($_POST) && $model->validate()) {

                if ($model->save()) {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Records has been saved'));
                    $returnUrl = Yii::$app->request->get('returnUrl', ['/seo/manage/redirect']);
                    switch (Yii::$app->request->post('action', 'save')) {
                        case 'next':
                            return $this->redirect(
                                [
                                    '/seo/manage/create-redirects',
                                    'returnUrl' => $returnUrl,
                                ]
                            );
                        case 'back':
                            return $this->redirect($returnUrl);
                        default:
                            return $this->redirect(
                                Url::toRoute(
                                    [
                                        '/seo/manage/update-redirect',
                                        'id' => $model->id,
                                        'returnUrl' => $returnUrl,
                                    ]
                                )
                            );
                    }
                }
            } else {
                return $this->render(
                    'updateRedirect',
                    [
                        'model' => $model,
                    ]
                );
            }
        } else {
            throw new NotFoundHttpException('redirect #'.$id.' not found');
        }
    }

    /**
     * Deletes an existing Redirect model.
     * If deletion is successful, the browser will be redirected to the 'redirect' page.
     * @param $id
     * @return \yii\web\Response
     */
    public function actionDeleteRedirect($id)
    {
        Redirect::deleteAll('`id` = :id', [':id' => $id]);
        return $this->redirect(['redirect']);
    }

    /**
     * Deletes an existing Redirect models.
     * If deletion is successful, the browser will be redirected to the 'redirect' page.
     * @return \yii\web\Response
     */
    public function actionDeleteRedirects()
    {
        if (isset($_POST['redirects'])) {
            return Redirect::deleteAll(
                [
                    'in',
                    'id',
                    $_POST['redirects'],
                ]
            );
        }
        return false;
    }

    public function actionGenerateRedirectFile()
    {
        echo Redirect::generateRedirectFile();
    }

    public function actionDeleteRedirectFile()
    {
        echo (int)Redirect::deleteRedirectFile();
    }
}
