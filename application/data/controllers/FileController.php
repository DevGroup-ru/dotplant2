<?php

namespace app\data\controllers;

use Yii;
use app\data\models\Import;
use app\data\models\Export;
use app\models\Object;
use app\data\models\ImportModel;
use app\backgroundtasks\helpers\BackgroundTasks;
use yii\base\ErrorException;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * Class FileController
 * @package app\data\controllers
 */
class FileController extends Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['data manage'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $objects = new ActiveDataProvider([
            'query' => Object::find()->with(['lastExport', 'lastImport']),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => false,
        ]);

        return $this->render('index', ['objects' => $objects]);
    }

    /**
     * Unified action for import and export
     * @param string $id
     * @param string $import
     */
    private function _unifiedAction($id, $importMode)
    {
        $object = Object::findById($id);
        /* @var $className \app\data\models\Import */
        $className =
            $importMode ?
            'app\data\models\Import' :
            'app\data\models\Export';

        if ($object !== null) {
            $model = new ImportModel(['object' => $id]);

            $fields = \app\data\components\Import::getFields($model->object);
            $fieldList = [];
            foreach ($fields['object'] as $field) {
                $fieldList['object'][$field] = $field;
            }
            foreach ($fields['property'] as $field) {
                $fieldList['property'][$field] = $field;
            }

            if (\Yii::$app->request->isPost) {
                if (
                    $model->load(\Yii::$app->request->post()) && $model->validate()
                ) {
                    if ($importMode === true) {
                        $file = UploadedFile::getInstance($model, 'file');
                        $model->type = $file->extension;
                        $filename = $model->getFilename('Import');
                        $fullFilename =
                            Yii::$app->getModule('data')->importDir .
                            '/' .
                            $filename;
                        if ($file->saveAs($fullFilename) === false) {
                            throw new ErrorException("Unable to save file");
                        }
                    }



                    $import = $className::find()->where(
                        [
                            'user_id' => Yii::$app->user->id,
                            'object_id' => $id,
                        ]
                    )->one();

                    if ($import === null) {
                        $import = new $className(
                            [
                                'user_id' => Yii::$app->user->id,
                                'object_id' => $id,
                            ]
                        );
                    }
                    if ($importMode === true) {
                        $import->filename = $filename;
                    } else {
                        $import->filename = null;
                    }
                    $import->status = $className::STATUS_PROCESS;

                    if ($import->save()) {
                        BackgroundTasks::addTask([
                            'name' =>
                                $importMode ? 'import' : 'export',
                            'description' =>
                                ($importMode ? 'import' : 'export') .
                                " {$model->object}",
                            'action' =>
                                'data/file/' .
                                ($importMode ? 'import' : 'export'),
                            'params' =>
                                $model->serialize(),
                            'init_event' =>
                                ($importMode ? 'import' : 'export'),
                        ]);
                        \Yii::$app->session->setFlash(
                            'info',
                            \Yii::t('app', 'Task is queued. Come back later.')
                        );
                    } else {
                        \Yii::$app->session->setFlash(
                            'error',
                            \Yii::t('app', 'Import Error')
                        );
                    }



                    return $this->redirect(['/data/file']);
                }
            }

            $availablePropertyGroups = ArrayHelper::map(\app\models\PropertyGroup::getForObjectId($object->id), 'id', 'name');

            \Yii::$app->session->setFlash(
                'info',
                \Yii::t('app', 'Specify fields to import and select the file')
            );
            return $this->render(($importMode ? 'import' : 'export'), [
                'model' => $model,
                'object' => $object,
                'fields' => $fieldList,
                'availablePropertyGroups' => $availablePropertyGroups,
            ]);
        } else {
            \Yii::$app->session->setFlash(
                'error',
                \Yii::t('app', 'Object not found')
            );
            return $this->redirect(['/data/file']);
        }
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws ErrorException
     */
    public function actionImport($id)
    {
        return $this->_unifiedAction($id, true);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws ErrorException
     */
    public function actionExport($id)
    {
        return $this->_unifiedAction($id, false);
    }
}
