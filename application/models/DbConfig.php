<?php

namespace app\models;

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

    public function testConnection()
    {
        $config = $this->getAttributes();
        $config['dsn'] = 'mysql:host='.$config['db_host'].';dbname='.$config['db_name'];
        $config['class'] = 'yii\db\Connection';
        unset($config['db_name'], $config['db_host']);

        $result = false;

        try {
            /** @var \yii\db\Connection $dbComponent */
            $dbComponent = Yii::createObject(
                $config
            );


            $dbComponent->open();
            $result = true;
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('danger', $e->getMessage());
        }
        return $result;
    }
}