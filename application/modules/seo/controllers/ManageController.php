<?php

namespace app\modules\seo\controllers;

use app\backend\components\BackendController;
use app\modules\shop\models\Category;
use app\modules\shop\models\OrderTransaction;
use app\modules\shop\models\Product;
use app\modules\seo\models\Config;
use app\modules\seo\models\Counter;
use app\modules\seo\models\Meta;
use app\modules\seo\models\Redirect;
use app\modules\seo\models\Robots;
use devgroup\ace\AceHelper;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\base\Event;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;
use yii\helpers\Url;
use Yii;

class ManageController extends BackendController
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
            [
              'class' => 'yii\filters\PageCache',
              'only' => ['GetRobots'],
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
                    $returnUrl = Yii::$app->request->get('returnUrl', ['meta']);
                    switch (Yii::$app->request->post('action', 'save')) {
                        case 'next':
                            return $this->redirect(
                                [
                                    'create-meta',
                                    'returnUrl' => $returnUrl,
                                ]
                            );
                        case 'back':
                            return $this->redirect($returnUrl);
                        default:
                            return $this->redirect(
                                Url::toRoute(
                                    [
                                        'update-meta',
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
            $returnUrl = Yii::$app->request->get('returnUrl', ['meta']);
            switch (Yii::$app->request->post('action', 'save')) {
                case 'next':
                    return $this->redirect(
                        [
                            'create-meta',
                            'returnUrl' => $returnUrl,
                        ]
                    );
                case 'back':
                    return $this->redirect($returnUrl);
                default:
                    return $this->redirect(
                        Url::toRoute(
                            [
                                'update-meta',
                                'id' => $model->key,
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
                    $returnUrl = Yii::$app->request->get('returnUrl', ['counter']);
                    switch (Yii::$app->request->post('action', 'save')) {
                        case 'next':
                            return $this->redirect(
                                [
                                    'create-counter',
                                    'returnUrl' => $returnUrl,
                                ]
                            );
                        case 'back':
                            return $this->redirect($returnUrl);
                        default:
                            return $this->redirect(
                                Url::toRoute(
                                    [
                                        'update-counter',
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
            $returnUrl = Yii::$app->request->get('returnUrl', ['counter']);
            if (Yii::$app->request->post('action', 'back') == 'next') {
                $route = ['create-counter', 'returnUrl' => $returnUrl];
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
        $model = Robots::getModel();
        if ($model === null) {
            $model = new Robots();
        }

        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $model->save();
                return $this->refresh();
            }
        }

        return $this->render('robots', ['model' => $model]);
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
            $action = Yii::$app->request->post('action', 'save');
            $returnUrl = Yii::$app->request->get('returnUrl', ['index']);
            switch ($action) {
                case 'next':
                    return $this->redirect(
                        [
                            'create-redirect',
                            'returnUrl' => $returnUrl,
                        ]
                    );
                case 'back':
                    return $this->redirect($returnUrl);
                default:
                    return $this->redirect(
                        Url::toRoute(
                            [
                                'update-redirect',
                                'id' => $model->id,
                                'returnUrl' => $returnUrl,
                            ]
                        )
                    );
            }
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
            $returnUrl = Yii::$app->request->get('returnUrl', ['/redirect']);
            switch (Yii::$app->request->post('action', 'save')) {
                case 'next':
                    return $this->redirect(
                        [
                            'create-redirects',
                            'returnUrl' => $returnUrl,
                        ]
                    );
                case 'back':
                    return $this->redirect($returnUrl);
                default:
                    return $this->redirect(
                        Url::toRoute(
                            [
                                'redirects',
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
                    $returnUrl = Yii::$app->request->get('returnUrl', ['redirect']);
                    switch (Yii::$app->request->post('action', 'save')) {
                        case 'next':
                            return $this->redirect(
                                [
                                    'create-redirect',
                                    'returnUrl' => $returnUrl,
                                ]
                            );
                        case 'back':
                            return $this->redirect($returnUrl);
                        default:
                            return $this->redirect(
                                Url::toRoute(
                                    [
                                        'update-redirect',
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

    public function actionEcommerce()
    {
        $yaCounter = $gaCounter = ['id' => '', 'active' => 0];

        if (Yii::$app->request->isPost) {
            $gaCounter = array_merge($gaCounter, Yii::$app->request->post('GoogleCounter', []));
            $yaCounter = array_merge($yaCounter, Yii::$app->request->post('YandexCounter', []));

            $model = Config::getModelByKey('ecommerceCounters');
            if (empty($model)) {
                $model = new Config();
                $model->key = 'ecommerceCounters';
            }
            $model->value = Json::encode(['google' => $gaCounter, 'yandex' => $yaCounter]);
            $model->save();
        }

        $counters = Config::getModelByKey('ecommerceCounters');
        if (!empty($counters)) {
            $counters = Json::decode($counters->value);
            $gaCounter = !empty($counters['google']) ? $counters['google'] : $gaCounter;
            $yaCounter = !empty($counters['yandex']) ? $counters['yandex'] : $yaCounter;
        }

        return $this->render(
            'ecommerce-counters',
            [
                'gaCounter' => $gaCounter,
                'yaCounter' => $yaCounter
            ]
        );
    }

    public static function renderEcommerceCounters(Event $event)
    {
        /** @var OrderTransaction $orderTransaction */
        $orderTransaction = OrderTransaction::findOne($event->data['transactionId']);
        $config = Config::getModelByKey('ecommerceCounters');
        if (empty($event->data['transactionId']) || empty($config) || !isset($orderTransaction->order)) {
            return ;
        }

        if (!empty($orderTransaction->order->items)) {
            $products = [];
            foreach ($orderTransaction->order->items as $item) {
                $product = Product::findById($item->product_id, null, null);
                if (empty($product)) {
                    continue;
                }
                $category = Category::findById($product->main_category_id);
                $category = empty($category) ? 'Магазин' : str_replace('\'', '', $category->name);

                $products[] = [
                    'id' => $product->id,
                    'name' => str_replace('\'', '', $product->name),
                    'price' => number_format($product->price, 2, '.', ''),
                    'category' => $category,
                    'qnt' => $item->quantity
                ];
            }

            $order = [
                'id' => $orderTransaction->order->id,
                'total' => number_format($orderTransaction->order->total_price, 2, '.', ''),
            ];

            echo Yii::$app->view->renderFile(Yii::getAlias('@app/modules/seo/views/manage/_ecommerceCounters.php'), [
                'order' => $order,
                'products' => $products,
                'config' => Json::decode($config->value)
            ]);
        }
    }
}
