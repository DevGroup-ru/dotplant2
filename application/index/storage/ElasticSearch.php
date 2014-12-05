<?php

namespace app\index\storage;

use app;
use Yii;
use yii\base\Component;

class ElasticSearch extends Component implements StorageInterface
{
    public $index = 'dotplant2';
    /**
     * Creates document index
     * @param string $name  Index name
     * @param array $params Parameters
     * @return boolean Result
     */
    public function createIndex($name, array $params)
    {
        return Yii::$app->index->storageComponent()->createCommand()
            ->createIndex($name, $params);
    }

    /**
     * Deletes document index
     * @param string $name
     * @return boolean Result
     */
    public function deleteIndex($name)
    {
        return Yii::$app->index->storageComponent()->createCommand()
            ->deleteIndex($name);
    }

    /**
     * @return string Returns namespace with model's storage-specific implementations
     */
    public function modelNamespace()
    {
        return '\app\index\models\ElasticSearch';
    }
}