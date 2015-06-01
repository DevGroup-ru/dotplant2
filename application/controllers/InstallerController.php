<?php

namespace app\controllers;

use app\models\DbConfig;
use Yii;
use yii\web\Controller;
use app\components\InstallerFilter;

class InstallerController extends Controller
{
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
                'file_permissions' => $this->checkPermissions(),
            ]
        );
    }

    private function checkPermissions()
    {
        $files = [
            '@app/config/db-local.php',
            '@app/config/web-local.php',
            '@app/config/common-configurable.php',
            '@app/config/console-configurable.php',
            '@app/config/web-configurable.php',
            '@app/config/kv-configurables.php',
            '@app/config/aliases.php',
        ];
        return array_reduce(
            $files,
            function($carry, $item) {
                $carry[$item] = is_writeable(Yii::getAlias($item));
                return $carry;
            },
            []
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
}