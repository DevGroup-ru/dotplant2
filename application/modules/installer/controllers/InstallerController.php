<?php

namespace app\modules\installer\controllers;

use app\modules\core\helpers\UpdateHelper;
use app\modules\installer\models\DbConfig;
use app\components\InstallerHelper;
use app\modules\installer\models\MigrateModel;
use Yii;
use yii\base\DynamicModel;
use yii\web\Controller;
use app\components\InstallerFilter;

class InstallerController extends Controller
{
    // set simple layout
    public $layout = 'installer';
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
        return $this->render(
            'index',
            [
                'file_permissions' => InstallerHelper::checkPermissions(),
            ]
        );
    }

    public function actionLanguage()
    {
        $model = new DynamicModel(['language']);
        $model->addRule(['language'], 'required');
        $model->setAttributes(['language' => Yii::$app->session->get('lang', 'en')]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->session->set('lang', $model->language);
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
        $config = Yii::$app->session->get('db-config', [
            'db_host' => 'localhost',
            'db_name' => 'dotplant2',
            'username' => 'root',
            'password' => '',
            'enableSchemaCache' => true,
            'schemaCacheDuration' => 86400,
            'schemaCache' => 'cache',
            'connectionOk' => false,
        ]);

        $model = new DbConfig();
        $model->setAttributes($config);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $config = $model->getAttributes();
            $config['connectionOk'] = false;

            if ($model->testConnection()) {

                $config['connectionOk'] = true;

                Yii::$app->session->setFlash('success', Yii::t('app', 'Database connection - ok'));

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

    public function actionMigrate()
    {
        $model = new MigrateModel();
        $model->ignore_time_limit_warning = Yii::$app->session->get('ignore_time_limit_warning', false);
        $model->manual_migration_run = false;
        $model->composerHomeDirectory = Yii::$app->session->get('composerHomeDirectory', './.composer/');
        $model->updateComposer = Yii::$app->session->get('updateComposer', true);
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
                if ($model->updateComposer) {
                    $composerProcess = $helper->updateComposer();
                    $composerProcess->run();
                    if ($composerProcess->getExitCode()!==0) {
                        $process = $composerProcess;
                    } else {
                        $process->run();
                    }
                }
                if ($process->getExitCode()===0) {
                    Yii::$app->session->setFlash('info', Yii::t('app', 'Migrations completed successfully'));
                    return $this->redirect(['admin-user']);
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