<?php

namespace app\index\storage;

use app;
use Yii;

class ArangoDb implements StorageInterface
{

    /**
     * Creates document index
     * @param string $name Index name
     * @param array $params Parameters
     * @return boolean Result
     */
    public function createIndex($name, array $params)
    {
        return Yii::$app->index->storageComponent()->getCollectionHandler()
            ->create($name, $params);
    }

    /**
     * Deletes document index
     * @param string $name
     * @return boolean Result
     */
    public function deleteIndex($name)
    {
        return Yii::$app->index->storageComponent()->getCollectionHandler()
            ->drop($name);
    }
}