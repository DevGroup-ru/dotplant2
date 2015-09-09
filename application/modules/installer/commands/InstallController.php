<?php

namespace app\modules\installer\commands;

use app\modules\core\helpers\UpdateHelper;
use app\modules\installer\models\AdminUser;
use app\modules\installer\models\DbConfig;
use app\modules\installer\components\InstallerFilter;
use app\modules\installer\components\InstallerHelper;
use app\modules\installer\models\FinalStep;
use app\modules\installer\models\MigrateModel;
use Yii;
use yii\base\DynamicModel;
use yii\console\Controller;
use yii\helpers\Console;

class InstallController extends Controller
{
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
        $this->stdout("Checking permissions\n", Console::FG_YELLOW);
        $permissions = InstallerHelper::checkPermissions();
        $ok = true;
        foreach ($permissions as $file => $result) {

            if ($result) {
                $this->stdout('[ OK ]    ', Console::FG_GREEN);
            } else {
                $this->stdout('[ Error ] ', Console::FG_RED);
            }
            $this->stdout($file);
            $this->stdout("\n");
            $ok = $ok && $result;
        }
        if (!$ok) {
            if ($this->confirm("\nSome of your files are not accessible.\nContinue at your own risk?", true)) {
                return $this->language();
            } else {
                return 1;
            }
        } else {
            return $this->language();
        }
    }

    private function language()
    {
        Yii::$app->session->set('language', $this->prompt('Enter language(ie. ru, zh-CN, en)',[
            'required' => true,
            'default' => 'en',
        ]));
        return $this->dbConfig();
    }

    private function dbConfig()
    {
        $config = $this->getDbConfigFromSession();

        $model = new DbConfig();
        $model->setAttributes($config);

        $this->stdout("Enter your database configuration:\n", Console::FG_YELLOW);
        foreach ($model->attributes() as $attribute) {
            if ($attribute !== 'enableSchemaCache') {
                $model->setAttributes(
                    [
                        $attribute => $this->prompt("-> $attribute", [
                            'required' => in_array($attribute, ['db_host', 'db_name', 'username']),
                            'default' => $model->$attribute,
                        ]),
                    ]
                );
            }
        }

        if (!$this->interactive) {
            if (getenv('DB_USER')) {
                $model->username = getenv('DB_USER');
            }
            if (getenv('DB_PASS')) {
                $model->password = getenv('DB_PASS');
            }
            if (getenv('DB_NAME')) {
                $model->db_name = getenv('DB_NAME');
            }
        }
        $config = $model->getAttributes();
        $config['connectionOk'] = false;

        if ($model->testConnection() === false) {
            $config['connectionOk'] = true;
            $this->stderr("Could not connect to databse!\n", Console::FG_RED);
            $this->dbConfig();
        }
        Yii::$app->session->set('db-config', $config);
        return $this->migration();

    }

    private function migration()
    {
        $config = $this->getDbConfigFromSession();

        $dbConfigModel = new DbConfig();
        $dbConfigModel->setAttributes($config);
        $config = InstallerHelper::createDatabaseConfig($dbConfigModel->getAttributes());
        if (InstallerHelper::createDatabaseConfigFile($config) === false) {
            $this->stderr(Yii::t('app', 'Unable to create db-local config'), Console::FG_RED);
            return false;
        }
        $this->stdout("Running migrations...\n", Console::FG_YELLOW);
        /** @var UpdateHelper $helper */
        $helper = Yii::createObject([
            'class' => UpdateHelper::className(),
        ]);
        $process = $helper->applyAppMigrations(
            false
        );

        $process->run();

        if (!$this->interactive && getenv("DP2_SKIP_ADMIN")) {
            return $this->finalStep();
        } else {
            return $this->adminUser();
        }
    }

    private function adminUser()
    {
        $model = new AdminUser();
        foreach ($model->attributes() as $attribute) {

            $model->setAttributes(
                [
                    $attribute => $this->prompt("-> $attribute", [
                        'required' => true,
                        'default' => $model->$attribute,
                    ]),
                ]
            );

        }
        if (!$this->interactive) {
            $model->password = 'password';
        }
        if ($model->validate()) {
            InstallerHelper::createAdminUser($model, $this->db());
            return $this->finalStep();
        } else {
            $this->stderr("Error in input data: ".var_export($model->errors, true), Console::FG_RED);
            return $this->adminUser();
        }
    }

    private function finalStep()
    {
        $model = new FinalStep();
        Yii::setAlias('@webroot', Yii::getAlias('@app/web/'));
        foreach ($model->attributes() as $attribute) {
            if ($attribute !== 'useMemcached') {
                $model->setAttributes(
                    [
                        $attribute => $this->prompt("-> $attribute", [
                            'required' => true,
                            'default' => $model->$attribute,
                        ]),
                    ]
                );
            } else {
                $model->useMemcached = $this->confirm("Use memcached extension?", false);
            }

        }
        if (getenv('DP2_SERVER_NAME')) {
            $model->serverName = getenv('DP2_SERVER_NAME');
        }
        if (InstallerHelper::writeCommonConfig($model) && InstallerHelper::updateConfigurables()) {
            file_put_contents(Yii::getAlias('@app/installed.mark'), '1');
            $this->stdout("Installation complete!\n", Console::FG_GREEN);
        } else {
            $this->stderr("Unable to write configs!\n", Console::FG_RED);
        }
        return 0;
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
}
