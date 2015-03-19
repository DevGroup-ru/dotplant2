<?php

namespace app\backend\controllers;

use app\models\Config;
use app\models\Form;
use app\models\Object;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use app\models\Submission;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\db\Query;
use yii\helpers\Json;

class PropertiesController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['property manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new PropertyGroup();
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionGroup($id = null)
    {
        if ($id === null) {
            $model = new PropertyGroup();
        } else {
            $model = PropertyGroup::findById($id);
        }

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            $save_result = $model->save();
            if ($save_result) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/backend/properties/index']);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/backend/properties/group',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            [
                                '/backend/properties/group',
                                'id' => $model->id,
                                'returnUrl' => $returnUrl,
                            ]
                        );
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }

        $searchModel = new Property();
        $searchModel->property_group_id = $model->id;
        $dataProvider = $searchModel->search($_GET);


        return $this->render(
            'group',
            [
                'model' => $model,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * @param $value_type
     * @return string
     * @throws \Exception
     */
    private function getColumnType($value_type)
    {
        switch ($value_type) {
            case 'STRING':
                return 'TINYTEXT';
            case 'NUMBER':
                return 'FLOAT';
            default:
                throw new \Exception('Unknown value type');
        }
    }

    public function actionEditProperty($property_group_id, $id = null)
    {
        if ($id === null) {
            $model = new Property();
            $model->handler_additional_params = '[]';
        } else {
            $model = Property::findById($id);
        }
        $model->property_group_id = $property_group_id;

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            if ($model->is_column_type_stored) {
                if ($model->isNewRecord) {
                    $object = Object::findById($model->group->object_id);
                    Yii::$app->db->createCommand()
                        ->addColumn($object->column_properties_table_name, $model->key, "TINYTEXT")
                        ->execute();
                    if ($object->object_class == Form::className()) {
                        $submissionObject = Object::getForClass(Submission::className());
                        $col_type = $this->getColumnType($model->value_type);
                        Yii::$app->db->createCommand()
                            ->addColumn($submissionObject->column_properties_table_name, $model->key, $col_type)
                            ->execute();
                    }
                } else {
                    if ($model->key != $model->getOldAttribute('key')) {
                        $object = Object::findById($model->group->object_id);
                        Yii::$app->db->createCommand()
                            ->renameColumn(
                                $object->column_properties_table_name,
                                $model->getOldAttribute('key'),
                                $model->key
                            )->execute();
                        if ($object->object_class == Form::className()) {
                            $submissionObject = Object::getForClass(Submission::className());
                            Yii::$app->db->createCommand()
                                ->renameColumn(
                                    $submissionObject->column_properties_table_name,
                                    $model->getOldAttribute('key'),
                                    $model->key
                                )->execute();
                        }
                    }
                    if ($model->value_type != $model->getOldAttribute('value_type')) {
                        $object = Object::findById($model->group->object_id);
                        $new_type = $this->getColumnType($model->value_type);
                        Yii::$app->db->createCommand()
                            ->alterColumn(
                                $object->column_properties_table_name,
                                $model->getOldAttribute('key'),
                                $new_type
                            )->execute();
                        if ($object->object_class == Form::className()) {
                            $submissionObject = Object::getForClass(Submission::className());
                            Yii::$app->db->createCommand()
                                ->renameColumn(
                                    $submissionObject->column_properties_table_name,
                                    $model->getOldAttribute('key'),
                                    $new_type
                                )->execute();
                        }
                    }
                }
            }

            $save_result = $model->save();
            if ($save_result) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get(
                    'returnUrl',
                    [
                        '/backend/properties/group',
                        'id' => $property_group_id,
                    ]
                );
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/backend/properties/edit-property',
                                'property_group_id' => $property_group_id,
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/backend/properties/edit-property',
                                    'id' => $model->id,
                                    'property_group_id' => $model->property_group_id,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }

        $searchModel = new PropertyStaticValues();

        $searchModel->property_id = $model->id;
        $dataProvider = $searchModel->search($_GET);

        $spamCheckerConfig = (new Config())->getValue("spamCheckerConfig.configFieldsParentId");

        return $this->render(
            'edit-property',
            [
                'model' => $model,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'fieldinterpretParentId' => null == $spamCheckerConfig ? 0 : $spamCheckerConfig
            ]
        );
    }

    public function actionEditStaticValue($property_id, $id = null)
    {
        if ($id === null) {
            $model = new PropertyStaticValues();
        } else {
            $model = PropertyStaticValues::findOne($id);
        }
        $model->property_id = $property_id;
        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $save_result = $model->save();
            if ($save_result) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Record has been saved'));
                $returnUrl = Yii::$app->request->get(
                    'returnUrl',
                    [
                        '/backend/properties/edit-property',
                        'id' => $model->property_id,
                        'property_group_id' => $model->property->property_group_id,
                    ]
                );
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/backend/properties/edit-static-value',
                                'property_id' => $model->property_id,
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/backend/properties/edit-static-value',
                                    'id' => $model->id,
                                    'property_id' => $model->property_id,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }
        return $this->render(
            'edit-static-value',
            [
                'model' => $model,
            ]
        );
    }

    public function actionDeleteStaticValue($id, $property_id, $property_group_id)
    {
        $model = PropertyStaticValues::findOne($id);
        $model->delete();
        Yii::$app->session->setFlash('danger', Yii::t('app', 'Object removed'));
        return $this->redirect(
            Url::to(
                [
                    '/backend/properties/edit-property',
                    'id'=>$property_id,
                    'property_group_id'=>$property_group_id
                ]
            )
        );
    }

    public function actionDeleteProperty($id, $property_group_id)
    {
        $model = Property::findOne($id);
        $static_values = PropertyStaticValues::find()
            ->where(['property_id'=>$model->id])
            ->all();
        foreach ($static_values as $psv) {
            $psv->delete();
        }
        if ($model->is_column_type_stored) {
            $object = Object::findById($model->group->object_id);
            Yii::$app->db->createCommand()
                ->dropColumn($object->column_properties_table_name, $model->key)
                ->execute();
            if ($object->object_class == Form::className()) {
                $submissionObject = Object::getForClass(Submission::className());
                Yii::$app->db->createCommand()
                    ->dropColumn($submissionObject->column_properties_table_name, $model->key)
                    ->execute();
            }
        }
        $model->delete();
        Yii::$app->session->setFlash('danger', Yii::t('app', 'Object removed'));
        return $this->redirect(
            Url::to(
                [
                    '/backend/properties/group',
                    'id'=>$property_group_id,
                ]
            )
        );
    }

    public function actionRemoveAllProperties($group_id)
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Property::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $static_values = PropertyStaticValues::find()
                    ->where(['property_id' => $item->id])
                    ->all();
                foreach ($static_values as $psv) {
                    $psv->delete();
                }
                if ($item->is_column_type_stored) {
                    $object = Object::findById($item->group->object_id);
                    Yii::$app->db->createCommand()
                        ->dropColumn($object->column_properties_table_name, $item->key)
                        ->execute();
                    if ($object->object_class == Form::className()) {
                        $submissionObject = Object::getForClass(Submission::className());
                        Yii::$app->db->createCommand()
                            ->dropColumn($submissionObject->column_properties_table_name, $item->key)
                            ->execute();
                    }
                }
                $item->delete();
            }
        }

        return $this->redirect(['group', 'id' => $group_id]);
    }

    public function actionDeleteGroup($id)
    {
        $model = PropertyGroup::findOne($id);
        $properties = Property::find()
            ->where(['property_group_id'=>$model->id])
            ->all();
        foreach ($properties as $prop) {
            $static_values = PropertyStaticValues::find()
                ->where(['property_id'=>$prop->id])
                ->all();
            foreach ($static_values as $psv) {
                $psv->delete();
            }
            $prop->delete();
        }
        $model->delete();
        Yii::$app->session->setFlash('danger', Yii::t('app', 'Object removed'));
        return $this->redirect(
            Url::to(
                [
                    '/backend/properties/index',
                ]
            )
        );
    }

    public function actionRemoveAllGroups()
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = PropertyGroup::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $properties = Property::find()
                    ->where(['property_group_id' => $item->id])
                    ->all();
                foreach ($properties as $prop) {
                    $static_values = PropertyStaticValues::find()
                        ->where(['property_id' => $prop->id])
                        ->all();
                    foreach ($static_values as $psv) {
                        $psv->delete();
                    }
                    $prop->delete();
                }
                $item->delete();
            }
        }

        return $this->redirect(['index']);
    }


    public function actionAutocomplete($search = null, $id = null, $object_id = null)
    {
        /**
         * @todo Добавить отображение вложенности
         */
        $out = ['more' => false];
        if (!is_null($search)) {
            $query = new Query;
            $query->select(
                Property::tableName().'.id, '.Property::tableName().'.name AS text'
            )
                ->from(Property::tableName())
                ->andWhere(['like', Property::tableName().'.name', $search])
                ->limit(100);
            if (!is_null($object_id)) {
                $query->leftJoin(
                    PropertyGroup::tableName(),
                    PropertyGroup::tableName().'.id = '.Property::tableName().'.property_group_id'
                );

                $query->andWhere([
                    PropertyGroup::tableName().'.id' => $object_id
                ]);

            }

            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        } elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Property::findOne($id)->name];
        } else {
            $out['results'] = ['id' => 0, 'text' => Yii::t('app', 'No matching records found')];
        }
        echo Json::encode($out);
    }



    public function actionHandlers()
    {
        return $this->render('handlers');
    }
}
