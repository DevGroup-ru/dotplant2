<?php

namespace app\modules\core\backend;

use app;
use app\backend\components\BackendController;
use app\modules\core\models\Extensions;
use Packagist\Api\Result\Package\Version;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\VarDumper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\filters\AccessControl;


class ExtensionsController extends BackendController
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
                        'actions' => [
                            'activate-extension',
                            'deactivate-extension',
                            'install-extension',
                            'remove-extension',
                            'update-extension',
                            'update-composer',
                        ],
                        'verbs' => ['POST'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['setting manage'],
                        'actions' => [
                            'index',
                            'explore',
                            'show-package',
                        ],
                    ],

                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new Extensions();
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionExplore($q='', $p=1)
    {
        $p = intval($p);
        $client = new app\modules\core\components\PackagistClient();
        $packages = $client->search($q, ['type'=>'dotplant2-extension'], $p);




        return $this->render(
            'explore',
            [
                'packages' => $packages,
                'query' => $q,
                'page' => $p,
            ]
        );
    }

    public function actionUpdateComposer()
    {
        /** @var app\modules\core\helpers\UpdateHelper $updateHelper */
        $updateHelper = Yii::$app->updateHelper;
        $process = $updateHelper->updateComposer();
        $process->mustRun();
        echo "<PRE>" . $process->getOutput() . $process->getErrorOutput() . "</PRE>";
    }

    private function handleActionEnd($returnUrl='')
    {
        if (Yii::$app->request->isAjax) {
            return $this->refresh();
        } else {
            return $this->goBack($returnUrl);
        }
    }

    public function actionInstallExtension($name, $updateComposer = '0')
    {
        $updateComposer = boolval($updateComposer);

        try {
            Extensions::installExtension($name, $updateComposer);
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Package not found on packagist.'));
        } catch (\yii\base\ErrorException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->handleActionEnd(['/core/backend-extensions/index']);

    }

    public function actionUpdateExtension($name)
    {
        $extension = Extensions::findByName($name);
        if ($extension === null) {
            throw new NotFoundHttpException;
        }
        $extension->installExtensionPackage();
        return $this->handleActionEnd(['/core/backend-extensions/index']);
    }

    public function actionShowPackage($name)
    {
        $client = new app\modules\core\components\PackagistClient();
        try {
            $package = $client->get($name);
            /** @var Version[] $versions */
            $versions = $package->getVersions();
            /** @var Version $version */
            // we are assuming that first version is latest
            $version = array_shift($versions);

            $data = [
                'package' => $package,
                'version' => $version,
            ];

            if (Yii::$app->request->isAjax === true) {
                return $this->renderAjax('show-package', $data);
            } else {
                return $this->render('show-package', $data);
            }
        } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Package not found on packagist.'));
            return $this->handleActionEnd();
        }
    }

    public function actionDeactivateExtension($name)
    {
        $extension = Extensions::findByName($name);
        if ($extension === null) {
            throw new NotFoundHttpException;
        }

        if ($extension->is_active) {

            if ($extension->deactivateExtension()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Extension deactivated.'));
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Can\'t deactivate extension.'));
            }

        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Extension already inactive'));
        }
        return $this->handleActionEnd(['/core/backend-extensions/index']);
    }

    public function actionRemoveExtension($name)
    {
        $extension = Extensions::findByName($name);
        if ($extension === null) {
            throw new NotFoundHttpException;
        }

        if ($extension->is_active) {
            if ($extension->deactivateExtension() === false) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Can\'t deactivate extension.'));
                return $this->handleActionEnd(['/core/backend-extensions/index']);
            }

        }

        if ($extension->removeExtensionPackage() === true) {
            if ($extension->delete()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Extension removed successfully.'));
            }
        }

        return $this->handleActionEnd(['/core/backend-extensions/index']);
    }
}