<?php

namespace app\data\controllers;

use Yii;
use app\data\models\Export;
use app\models\Object;
use app\backend\models\ImportModel;
use app\backgroundtasks\helpers\BackgroundTasks;
use app\data\components\Import;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

class FileController extends Controller
{
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

    public function actionImport($id)
    {
        $object = Object::findById($id);

        if ($object !== null) {
            $model = new ImportModel(['object' => $id]);

            $fields = Import::getFields($model->object);
            $fieldList = [];
            foreach ($fields['object'] as $field) {
                $fieldList['object'][$field] = $field;
            }
            foreach ($fields['property'] as $field) {
                $fieldList['property'][$field] = $field;
            }

            if (\Yii::$app->request->isPost) {
                if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
                    $file = UploadedFile::getInstance($model, 'file');
                    $model->type = $file->extension;
                    $filename = $model->getFilename('Import');

                    if ($file->saveAs(Yii::$app->getModule('data')->importDir . '/' . $filename)) {
                        $import = \app\data\models\Import::find()->where(
                            [
                                'user_id' => Yii::$app->user->id,
                                'object_id' => $id,
                            ]
                        )->one();

                        if ($import === null) {
                            $import = new \app\data\models\Import(
                                [
                                    'user_id' => Yii::$app->user->id,
                                    'object_id' => $id,
                                ]
                            );
                        }

                        $import->filename = $filename;
                        $import->status = \app\data\models\Import::STATUS_PROCESS;

                        if ($import->save()) {
                            BackgroundTasks::addTask([
                                'name' => 'import',
                                'description' => "import {$model->object}",
                                'action' => 'data/file/import',
                                'params' => serialize($model),
                                'init_event' => 'import',
                            ]);
                            \Yii::$app->session->setFlash('info', \Yii::t('app', 'Task is queued. Come back later.'));
                        } else {
                            \Yii::$app->session->setFlash('error', \Yii::t('app', 'Import Error'));
                        }

                    }

                    return $this->redirect(['/data/file']);
                }
            }
            \Yii::$app->session->setFlash('info', \Yii::t('app', 'Specify fields to import and select the file'));
            return $this->render('import', [
                'model' => $model,
                'object' => $object,
                'fields' => $fieldList
            ]);
        } else {
            \Yii::$app->session->setFlash('error', \Yii::t('app', 'Object not found'));
            return $this->redirect(['/data/file']);
        }
    }

    public function actionExport($id)
    {
        $object = Object::findById($id);

        if ($object !== null) {
            $model = new ImportModel(['object' => $id]);

            $fields = Import::getFields($model->object);
            $fieldList = [];
            foreach ($fields['object'] as $field) {
                $fieldList['object'][$field] = $field;
            }
            foreach ($fields['property'] as $field) {
                $fieldList['property'][$field] = $field;
            }

            if (\Yii::$app->request->isPost) {
                if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
                    if (isset($model->fields['object']) && !empty($model->fields['object'])) {
                        $export = Export::find()->where(
                            [
                                'user_id' => Yii::$app->user->id,
                                'object_id' => $id,
                            ]
                        )->one();

                        if ($export === null) {
                            $export = new Export(
                                [
                                    'user_id' => Yii::$app->user->id,
                                    'object_id' => $id,
                                ]
                            );
                        }

                        $export->filename = null;
                        $export->status = Export::STATUS_PROCESS;

                        if ($export->save()) {
                            BackgroundTasks::addTask([
                                'name' => 'export',
                                'description' => "export {$model->object}",
                                'action' => 'data/file/export',
                                'params' => serialize($model),
                                'init_event' => 'export',
                            ]);
                            \Yii::$app->session->setFlash('info', \Yii::t('app', 'Task is queued. Come back later.'));
                        } else {
                            \Yii::$app->session->setFlash('error', \Yii::t('app', 'Export Error'));
                        }
                    }

                    return $this->redirect(['/data/file']);
                }
            }
            \Yii::$app->session->setFlash('info', \Yii::t('app', 'Specify fields for export'));

            return $this->render('export', [
                'model' => $model,
                'object' => $object,
                'fields' => $fieldList
            ]);
        } else {
            \Yii::$app->session->setFlash('error', \Yii::t('app', 'Object not found'));
            return $this->redirect(['/data/file']);
        }
    }
}
