<?php

namespace app\modules\data\commands;

use app\modules\data\models\ImportModel;
use app\modules\data\models\Export;
use app\modules\data\models\Import;
use app\models\Object;
use yii\console\Controller;
use yii\console\Exception;


/**
 * Import/export
 * @package app\modules\data\commands
 */
class FileController extends Controller
{
    public function actionImport($importModel)
    {
        /** @var ImportModel $model */
        $model = new ImportModel();
        $model->unserialize($importModel);

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

                    $import = \app\modules\data\components\Import::createInstance(
                        [
                            'object' => $object,
                            'filename' => $filename,
                            'type' => $model->type,
                            'addPropertyGroups' => $model->addPropertyGroups,
                            'createIfNotExists' => boolval($model->createIfNotExists),
                            'multipleValuesDelimiter' => $model->multipleValuesDelimiter,
                            'additionalFields' => $model->additionalFields,
                        ]
                    );
                    if ($import->processImport($model->fields)) {
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

    public function actionExport($importModel)
    {
        /** @var ImportModel $model */
        $model = new ImportModel();
        $model->unserialize($importModel);

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

                $import = \app\modules\data\components\Import::createInstance(
                    [
                        'object' => $object,
                        'filename' => $filename,
                        'type' => $model->type,
                    ]
                );
                $import->processExport($model->fields, $model->conditions);

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
 