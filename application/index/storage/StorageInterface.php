<?php

namespace app\index\storage;

use app;
use Yii;

interface StorageInterface
{
    /**
     * Creates document index
     * @param string $name Index name
     * @param array $params Parameters
     * @return boolean Result
     */
    public function createIndex($name, array $params);

    /**
     * Deletes document index
     * @param string $name
     * @return boolean Result
     */
    public function deleteIndex($name);

    /**
     * @return string Returns namespace with model's storage-specific implementations(ie. app\index\models\ElasticSearch)
     */
    public function modelNamespace();
}