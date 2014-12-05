<?php

namespace app\index\behaviors;

use app;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class IndexableBehavior extends Behavior
{
    /**
     * @var \yii\db\BaseActiveRecord
     */
    public $owner;
    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteDocument',
            ActiveRecord::EVENT_AFTER_INSERT => 'insertDocument',
            ActiveRecord::EVENT_AFTER_UPDATE => 'updateDocument',
        ];
    }

    public function deleteDocument()
    {
        $model = $this->storageModel();
        $model->primaryKey = $this->getPk();
        return $model->delete();
    }

    public function insertDocument()
    {
        $model = $this->storageModel();
        $model->primaryKey = $this->getPk();
        return $model->save();
    }

    public function updateDocument()
    {
        $model = $this->storageModel();
        $model = $model->findByPk($this->getPk());
        $model->setAttributes($this->owner->getAttributes());
        return $model->save();
    }

    /**
     * @return string composite primary key for the record
     */
    private function getPk()
    {
        return implode("_", $this->owner->getPrimaryKey(true));
    }

    /**
     * @return \yii\db\BaseActiveRecord
     */
    private function storageModel()
    {
//        return Yii::$app->index->storage()->storageModel(
//            $this->owner->getAttributes(
//                null,
//                array_keys(
//                    $this->owner->getPrimaryKey(true)
//                )
//            )
//        );

        $modelClassname = Yii::$app->index->storage()->modelNamespace();
        $modelClassname .= "\\" . $this->owner->tableName();
        $model = new $modelClassname(
            $this->owner->getAttributes(
                null,
                array_keys(
                    $this->owner->getPrimaryKey(true)
                )
            )
        );

        return $model;
    }
} 