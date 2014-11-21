<?php

namespace app\data\commands;

use app\backend\models\ImportModel;
use app\data\models\Export;
use app\data\models\Import;
use app\models\Object;
use yii\console\Controller;
use yii\console\Exception;

/**
 * Import/export
 * @package app\data\commands
 */
class FileController extends Controller
{
    public function actionImport($importModel)
    {
        /** @var ImportModel $model */
        $model = unserialize($importModel);

        if ($model->validate()) {
            /** @var Export $exportStatus */
            $importStatus = Import::find()->where(
                [
                    'user_id' => $model->getUser(),
                    'object_id' => $model->object,
                ]
            )->one();
            $importStatus->status = Import::STATUS_PROCESS;
            $importStatus->save();

            $filename = $model->getFilename('Import');
            $path = \Yii::$app->getModule('data')->importDir . '/' . $filename;
            if (file_exists($path)) {
                try {
                    $object = Object::findById($model->object);
                    if ($object === null) {
                        throw new Exception('Object not found');
                    }

                    $import = \app\data\components\Import::createInstance(
                        [
                            'object' => $object,
                            'filename' => $filename,
                            'type' => $model->type,
                        ]
                    );
                    if ($import->setData($model->fields)) {
                        $importStatus->status = Import::STATUS_COMPLETE;
                    } else {
                        $importStatus->status = Import::STATUS_FAILED;
                    }

                    $importStatus->save();
                } catch (\Exception $e) {
                    $importStatus->status = Import::STATUS_FAILED;
                    $importStatus->save();
                    echo $e->getMessage();
                    unset($path);
                    throw $e;
                }
            } else {
                echo "File '{$filename}' is not exist";
                throw new Exception("File '{$filename}' is not exist");
            }
        } else {
            echo 'Model is not valid';
            throw new Exception('Model is not valid');
        }
    }

    public function actionExport($inputModel)
    {
        /** @var ImportModel $model */
        $model = unserialize($inputModel);

        if ($model->validate()) {
            /** @var Export $exportStatus */
            $exportStatus = Export::find()->where(
                [
                    'user_id' => $model->getUser(),
                    'object_id' => $model->object,
                ]
            )->one();
            $exportStatus->status = Export::STATUS_PROCESS;
            $exportStatus->save();

            try {
                $object = Object::findById($model->object);
                if ($object === null) {
                    throw new Exception('Object not found');
                }

                $filename = $model->getFilename('Export');

                $import = \app\data\components\Import::createInstance(
                    [
                        'object' => $object,
                        'filename' => $filename,
                        'type' => $model->type,
                    ]
                );
                $import->getData($model->fields);

                $exportStatus->filename = $filename;
                $exportStatus->status = Export::STATUS_COMPLETE;
                $exportStatus->save();
            } catch (\Exception $e) {
                $exportStatus->status = Export::STATUS_FAILED;
                $exportStatus->save();
                echo $e->getMessage();
                throw $e;
            }
        } else {
            echo 'Model is not valid';
            throw new Exception('Model is not valid');
        }
    }
}
 