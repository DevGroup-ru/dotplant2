<?php

namespace app\modules\installer\controllers;

use app\modules\core\helpers\UpdateHelper;
use app\modules\installer\models\AdminUser;
use app\modules\installer\models\DbConfig;
use app\modules\installer\components\InstallerFilter;
use app\modules\installer\components\InstallerHelper;
use app\modules\installer\models\FinalStep;
use app\modules\installer\models\MigrateModel;
use Yii;
use yii\base\DynamicModel;
use yii\web\Controller;


class InstallerController extends Controller
{
    // set simple layout
    public $layout = 'installer';
    private $db = null;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'installer' => [
                'class' => InstallerFilter::className(),
            ],
        ];
    }

    public function actionIndex()
    {
        $minPhpVersion = version_compare(PHP_VERSION, '5.5.0') >= 0;
        $docRoot = strpos(Yii::$app->request->url, '/installer.php') === 0;

        return $this->render(
            'index',
            [
                'file_permissions' => InstallerHelper::checkPermissions(),
                'minPhpVersion' => $minPhpVersion,
                'docRoot' => $docRoot,
            ]
        );
    }

    public function actionLanguage()
    {
        $model = new DynamicModel(['language']);
        $model->addRule(['language'], 'required');
        $model->setAttributes(['language' => Yii::$app->session->get('language', 'en')]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->session->set('language', $model->language);
            return $this->redirect(['db-config']);
        }
        return $this->render(
            'language',
            [
                'languages' => InstallerHelper::getLanguagesArray(),
                'model' => $model,
            ]
        );
    }

    public function actionDbConfig()
    {
        $config = $this->getDbConfigFromSession();

        $model = new DbConfig();
        $model->setAttributes($config);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $config = $model->getAttributes();
            $config['connectionOk'] = false;

            if ($model->testConnection()) {

                $config['connectionOk'] = true;

                Yii::$app->session->setFlash('success', Yii::t('app', 'Database connection - ok'));
                if (isset($_POST['next'])) {
                    Yii::$app->session->set('db-config', $config);
                    return $this->redirect(['migrate']);
                }

            }

            Yii::$app->session->set('db-config', $config);
        }

        return $this->render(
            'db-config',
            [
                'config' => $config,
                'model' => $model,
            ]
        );
    }

    private function getDbConfigFromSession()
    {
        return Yii::$app->session->get('db-config', [
            'db_host' => 'localhost',
            'db_name' => 'dotplant2',
            'username' => 'root',
            'password' => '',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 86400,
            'schemaCache' => 'cache',
            'connectionOk' => false,
        ]);
    }

    public function actionMigrate()
    {
        $model = new MigrateModel();
        $model->ignore_time_limit_warning = Yii::$app->session->get('ignore_time_limit_warning', false);
        $model->manual_migration_run = false;
        $model->composerHomeDirectory = Yii::$app->session->get('composerHomeDirectory', './.composer/');
        $model->updateComposer = Yii::$app->session->get('updateComposer', false);
        if ($model->load(Yii::$app->request->post())) {
            $model->validate();
            foreach ($model->getAttributes() as $key => $value) {
                Yii::$app->session->set($key, $value);
            }
        }

        /** @var UpdateHelper $helper */
        $helper = Yii::createObject([
            'class' => UpdateHelper::className(),
            'composerHomeDirectory' => $model->composerHomeDirectory,
        ]);
        $process = $helper->applyAppMigrations(
            false
        );

        $commandToRun = $process->getCommandLine();

        $check = $this->checkTime($model->ignore_time_limit_warning);
        if (Yii::$app->request->isPost) {
            if ($check && $model->manual_migration_run === false) {

                // create config
                $config = $this->getDbConfigFromSession();

                $dbConfigModel = new DbConfig();
                $dbConfigModel->setAttributes($config);
                $config = InstallerHelper::createDatabaseConfig($dbConfigModel->getAttributes());
                $configOk = true;
                if (InstallerHelper::createDatabaseConfigFile($config) === false) {
                    Yii::$app->session->setFlash('warning', Yii::t('app', 'Unable to create db-local config'));
                    $configOk = false;
                }

                if ($configOk === true) {
                    if ($model->updateComposer) {
                        $composerProcess = $helper->updateComposer();
                        $composerProcess->run();
                        gc_collect_cycles();
                        if ($composerProcess->getExitCode() !== 0) {
                            $process = $composerProcess;
                        } else {
                            $process->run();
                        }
                    } else {
                        $process->run();
                    }
                    if ($process->getExitCode() === 0) {
                        Yii::$app->session->setFlash('info', Yii::t('app', 'Migrations completed successfully'));
                        return $this->redirect(['admin-user']);
                    }
                }
            }
            if ($model->manual_migration_run === true) {
                return $this->redirect(['admin-user']);
            }
        }
        return $this->render(
            'migrate',
            [
                'check' => $check,
                'commandToRun' => $commandToRun,
                'process' => $process,
                'model' => $model,
            ]
        );
    }

    public function actionAdminUser()
    {
        $model = new AdminUser();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if (InstallerHelper::createAdminUser($model, $this->db())) {
                Yii::$app->session->setFlash('info', Yii::t('app', 'Admin user created'));
                return $this->redirect(['final']);
            }
        }

        return $this->render(
            'admin-user',
            [
                'model' => $model,
            ]
        );
    }

    public function actionFinal()
    {
        $model = new FinalStep();
        $model->serverName = Yii::$app->request->serverName;

        if (extension_loaded('memcached') || extension_loaded('memcache')) {
            $model->cacheClass = 'yii\caching\MemCache';
            if (extension_loaded('memcached')) {
                $model->useMemcached = true;
            }
        }

        if (Yii::$app->request->serverPort !== 80) {
            $model->serverName .= ':' . Yii::$app->request->serverPort;
        }
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if (InstallerHelper::writeCommonConfig($model) && InstallerHelper::updateConfigurables()) {

                return $this->redirect(['complete']);
            } else {
                Yii::$app->session->setFlash('warning', Yii::t('app', 'Unable to write common-local.php'));
            }
        }
        $cacheClasses = [
            'yii\caching\FileCache',
            'yii\caching\MemCache',
            'yii\caching\XCache',
            'yii\caching\ZendDataCache',
            'yii\caching\ApcCache',
        ];

        return $this->render(
            'final',
            [
                'model' => $model,
                'cacheClasses' => $cacheClasses,
            ]
        );
    }

    public function actionComplete()
    {
        file_put_contents(Yii::getAlias('@app/installed.mark'), '1');
        return $this->render(
            'complete'
        );
    }

    /**
     * @return \yii\db\Connection
     * @throws \yii\base\InvalidConfigException
     */
    private function db()
    {
        if ($this->db === null) {
            $config = InstallerHelper::createDatabaseConfig($this->getDbConfigFromSession());
            $dbComponent = Yii::createObject(
                $config
            );
            $dbComponent->open();
            $this->db = $dbComponent;
        }
        return $this->db;
    }


    private function checkTime($ignoreTimeLimit=false)
    {
        if (InstallerHelper::unlimitTime() === false && $ignoreTimeLimit === false) {
            Yii::$app->session->setFlash('warning', Yii::t('app', 'Can\'t set time limit to 0. Some operations may not complete.'));
            return false;
        } else {
            return true;
        }
    }
}