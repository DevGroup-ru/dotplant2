<?php

namespace app\backend\controllers;

use app\backend\models\ImportModel;
use app\data\import\Import;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\UploadedFile;

class DataController extends Controller
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

    public function actionImport()
    {
        $model = new ImportModel();
        $fieldList = [
            'object' => [],
            'property' => [],
        ];

        if (\Yii::$app->request->isPost) {
            if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
                $fields = Import::getFields($model->object);
                $fieldList = [];
                foreach ($fields['object'] as $field) {
                    $fieldList['object'][$field] = $field;
                }
                foreach ($fields['property'] as $field) {
                    $fieldList['property'][$field] = $field;
                }
                if ($model->file = UploadedFile::getInstance($model, 'file')) {
                    $type = $model->file->extension;
                    $import = Import::createInstance($model->object, $type, fopen($model->file->tempName, 'r'));
                    if ($import->setData($model->fields)) {
                        \Yii::$app->session->setFlash('info', \Yii::t('app', 'Data imported'));
                    } else {
                        \Yii::$app->session->setFlash('error', \Yii::t('app', 'Data can not be imported'));
                    }
                } else {
                    \Yii::$app->session->setFlash(
                        'info',
                        \Yii::t('app', 'Specify fields to import and select the file')
                    );
                }
            }
        }

        return $this->render('import', ['model' => $model, 'fields' => $fieldList]);
    }

    public function actionExport()
    {
        $model = new ImportModel();
        $fieldList = [
            'object' => [],
            'property' => [],
        ];

        if (\Yii::$app->request->isPost) {
            if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
                $fields = Import::getFields($model->object);
                $fieldList = [];
                foreach ($fields['object'] as $field) {
                    $fieldList['object'][$field] = $field;
                }
                foreach ($fields['property'] as $field) {
                    $fieldList['property'][$field] = $field;
                }
                if (isset($model->fields['object']) && !empty($model->fields['object'])) {
                    $import = Import::createInstance($model->object, $model->type);
                    $import->getData($model->fields);
                } else {
                    \Yii::$app->session->setFlash('info', \Yii::t('app', 'Specify fields for export'));
                }
            }
        }

        return $this->render('export', ['model' => $model, 'fields' => $fieldList]);
    }
}
