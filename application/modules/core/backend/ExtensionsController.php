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
                        'roles' => ['administrate'],
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
                        'roles' => ['administrate'],
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

    private function handleActionEnd()
    {
        if (Yii::$app->request->isAjax) {
            return $this->refresh();
        } else {
            return $this->goBack();
        }
    }

    public function actionInstallExtension($name, $updateComposer = '0')
    {
        $updateComposer = boolval($updateComposer);


        $extension = null;

        /** @var app\modules\core\helpers\UpdateHelper $updateHelper */

        if (Extensions::isPackageInstalled($name) === true) {
            // we should just activate it
            $extension = Extensions::findByName($name);
        } else {
            $client = new app\modules\core\components\PackagistClient();
            try {
                $package = $client->get($name);
            } catch (\Guzzle\Http\Exception\ClientErrorResponseException $e) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Package not found on packagist.'));
                return $this->handleActionEnd();
            }
            /** @var Version[] $versions */
            $versions = $package->getVersions();
            /** @var Version $version */
            // we are assuming that first version is latest
            $version = array_shift($versions);

            $extension = new Extensions();
            $extension->name = $name;
            $extension->homepage = $version->getHomepage();
            $extension->current_package_version_timestamp = date('Y-m-d H:i:s', strtotime($version->getTime()));
            $extension->latest_package_version_timestamp = date('Y-m-d H:i:s', strtotime($version->getTime()));

            $autoload = $version->getAutoload();
            if (isset($autoload['psr-4'])) {
                $namespaces = array_keys($autoload['psr-4']);
                $prefix = array_shift($namespaces);

                if (isset(array_keys($autoload['psr-4'])[0])) {
                    $extension->namespace_prefix = $prefix;
                }
            }
            $extension->is_active = 0;


            if ($extension->save() === false) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Unable to save extension to database') . var_export($extension->errors, true));
                return $this->handleActionEnd();
            }

            if ($extension->installExtensionPackage() === false) {
                return $this->handleActionEnd();
            }
            $loader = require(Yii::getAlias('@app/vendor/autoload.php'));
            $psr4 = require(Yii::getAlias('@app/vendor/composer/autoload_psr4.php'));
            foreach ($psr4 as $prefix => $paths) {
                $loader->setPsr4($prefix, $paths);
            }
        }

        if ($extension === null) {
            throw new NotFoundHttpException;
        }
        try {
            $result = $extension->activateExtension($updateComposer);
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Unable to activate extension').': '.$e->getMessage());
            return $this->handleActionEnd();
        }
        if ($result) {
            $extension->is_active = 1;
            $extension->save();
        }
        return $this->handleActionEnd();

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
        return $this->handleActionEnd();
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
                return $this->handleActionEnd();
            }

        }

        if ($extension->removeExtensionPackage() === true) {
            if ($extension->delete()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Extension removed successfully.'));
            }
        }

        return $this->handleActionEnd();
    }
}