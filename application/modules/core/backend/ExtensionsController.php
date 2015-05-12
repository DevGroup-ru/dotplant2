<?php

namespace app\modules\core\backend;

use app;
use app\backend\components\BackendController;
use app\modules\core\models\Extensions;
use Packagist\Api\Result\Package\Version;
use Yii;
use yii\data\ArrayDataProvider;
use yii\helpers\VarDumper;
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

    public function actionInstallExtension($name, $updateComposer = '0')
    {
        $updateComposer = boolval($updateComposer);

        if (Yii::$app->request->isPost === true || true) {
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
                    return $this->renderContent('');
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


                if ($extension->save() === false) {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Unable to save extension to database') . var_export($extension->errors, true));
                    return $this->renderContent('');
                }

            }

            if ($extension === null) {
                throw new NotFoundHttpException;
            }
            try {
                $result = $extension->activateExtension($updateComposer);
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Unable to activate extension').': '.$e->getMessage());
                return $this->renderContent('');
            }
            return $this->renderContent($result ? 'yes' : 'no');
        }
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
            return $this->renderContent('');
        }
    }
}