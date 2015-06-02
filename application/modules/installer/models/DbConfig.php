<?php

namespace app\modules\installer\models;

use app\modules\installer\components\InstallerHelper;
use Yii;
use yii\base\Model;

class DbConfig extends Model
{
    public $db_host = 'localhost';
    public $db_name = 'dotplant2';
    public $username = 'root';
    public $password = '';
    public $enableSchemaCache = true;
    public $schemaCacheDuration = 86400;
    public $schemaCache = 'cache';

    public function rules()
    {
        return [
            [
                [
                    'db_host',
                    'db_name',
                    'username',
                    'schemaCache',
                ],
                'required',
            ],
            [
                [
                    'password',
                ],
                'string',
            ],
            [
                [
                    'enableSchemaCache',
                ],
                'filter',
                'filter' => 'boolval',
            ],
            [
                [
                    'enableSchemaCache',
                ],
                'boolean',
            ],
            [
                [
                    'schemaCacheDuration',
                ],
                'integer',
            ],
            [
                [
                    'schemaCacheDuration',
                ],
                'filter',
                'filter' => 'intval',
            ],
        ];
    }

    /**
     * @return bool If connection ok?
     */
    public function testConnection()
    {
        $config = InstallerHelper::createDatabaseConfig($this->getAttributes());

        $result = false;

        try {
            /** @var \yii\db\Connection $dbComponent */
            $dbComponent = Yii::createObject(
                $config
            );


            $dbComponent->open();
            $result = true;
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('danger', Yii::t('app', 'Database connection error:') . ' ' . $e->getMessage());
        }
        return $result;
    }
}