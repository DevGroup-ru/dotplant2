<?php

namespace app\controllers;

use app\actions\SubmitFormAction;
use app\models\Config;
use app\models\LoginForm;
use app\models\Product;
use app\models\RegistrationForm;
use app\models\Search;
use app\models\User;
use app\models\UserService;
use app\seo\behaviors\MetaBehavior;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

class DefaultController extends Controller
{
    public function behaviors()
    {
        return [
            'seo' => [
                'class' => MetaBehavior::className(),
                'index' => $this->defaultAction,
            ],
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'profile'],
                'rules' => [
                    [
                        'actions' => ['logout', 'profile'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'auth' => [
                'class' => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'successCallback'],
            ],
            'submit-form' => [
                'class' => SubmitFormAction::className(),
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin($returnUrl = null)
    {
        if (!\Yii::$app->user->isGuest) {
            $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load($_POST) && $model->login()) {
            return $this->goBack($returnUrl);
        } else {
            return $this->render(
                'login',
                [
                    'model' => $model,
                ]
            );
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    public function actionSignup()
    {
        $model = new RegistrationForm();
        if ($model->load($_POST) && $model->signup()) {
            return $this->goHome();
        } else {
            return $this->render(
                'signup',
                [
                    'model' => $model,
                ]
            );
        }
    }

    public function successCallback($client)
    {
        $userAttributes = $client->getUserAttributes();
        $serviceType = $client->className();
        $serviceId = $userAttributes['id'];
        $userService = UserService::findOne(
            [
                'service_type' => $serviceType,
                'service_id' => $serviceId,
            ]
        );
        if (is_null($userService)) {
            if (Yii::$app->user->isGuest) {
                preg_match('#^(.+?)([^\\\\]+)$#', $serviceType, $service);
                switch ($service[2]) {
                    case 'GoogleOpenId':
                    case 'VK':
                    case 'Facebook':
                        $firstName = $userAttributes['first_name'];
                        $lastName = $userAttributes['last_name'];
                        $email = isset($userAttributes['email']) ? $userAttributes['email'] : '';
                        break;
                    case 'YandexOpenId':
                        $firstName = $userAttributes['name'];
                        $lastName = '';
                        $email = $userAttributes['email'];
                        break;
                    default:
                        $firstName = '';
                        $lastName = '';
                        $email = '';
                }
                $user = new User(['scenario' => 'registerService']);
                $user->attributes = [
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ];
                $user->save();
                $userService = new UserService;
                $userService->setAttributes(
                    [
                        'user_id' => $user->id,
                        'service_type' => $serviceType,
                        'service_id' => (string) $serviceId,
                    ]
                );
                $userService->save();
            } else {
                $userService = new UserService;
                $userService->setAttributes(
                    [
                        'user_id' => Yii::$app->user->id,
                        'service_type' => $serviceType,
                        'service_id' => (string) $serviceId,
                    ]
                );
                $userService->save();
                Yii::$app->user->setReturnUrl(['cabinet/profile']);
            }
        } elseif (!Yii::$app->user->isGuest) {
            $userService->delete();
            Yii::$app->user->setReturnUrl(['cabinet/profile']);
        }
        if (!is_null($userService->user)) {
            Yii::$app->user->login($userService->user, 0);
        }
    }

    public function actionSearch()
    {
        $model = new Search();
        $model->load(Yii::$app->request->get());
        return $this->render(
            'search',
            [
                'model' => $model,
            ]
        );
    }

    public function actionAutoCompleteSearch($term)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $query = Product::find()->orderBy('sort_order');
        foreach (['name', 'content'] as $attribute) {
            $query->orWhere(['like', $attribute, $term]);
        }
        $query->andWhere(['active'=>1]);
        $products = $query->limit(Config::getValue('core.autoCompleteResultsCount', 5))->all();
        $result = [];
        foreach ($products as $product) {
            $result[] = [
                'template' => $this->renderPartial(
                    'auto-complete-item-template',
                    [
                        'product' => $product,
                    ]
                ),
            ];
        }
        return $result;
    }
}
