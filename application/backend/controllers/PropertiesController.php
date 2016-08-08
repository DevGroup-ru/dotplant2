<?php

namespace app\backend\controllers;

use app\components\Helper;
use app\models\Form;
use app\models\Object;
use app\models\ObjectStaticValues;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use app\models\Submission;
use app\modules\image\widgets\views\AddImageAction;
use app\properties\PropertyHandlers;
use app\modules\image\widgets\SaveInfoAction;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class PropertiesController extends Controller
{
    protected function checkDoubledSlugs($slug)
    {
        if (empty($slug) === false) {
            $propertyStaticValues = PropertyStaticValues::find()
                ->where(['slug' => $slug])
                ->all();
            if (count($propertyStaticValues) > 1) {
                $result = Html::tag('h4', Yii::t('app', 'You have doubled slugs. Fix it please.'));
                $result .= Html::beginTag('ul');
                foreach ($propertyStaticValues as $propertyStaticValue) {
                    $property = Property::findById($propertyStaticValue->property_id);
                    if ($property !== null) {
                        $propertyGroup = PropertyGroup::findById($property->property_group_id);
                        $result .= Html::tag(
                            'li',
                            ($propertyGroup !== null ? Html::a(
                                $propertyGroup->name,
                                [
                                    '/backend/properties/group',
                                    'id' => $property->property_group_id,
                                ]
                            ) : '')
                            . ' > '
                            . Html::a(
                                $property->name,
                                [
                                    '/backend/properties/edit-property',
                                    'id' => $propertyStaticValue->property_id,
                                    'property_group_id' => $property->property_group_id,
                                ]
                            )
                            . ' > '
                            . Html::a(
                                $propertyStaticValue->name,
                                [
                                    '/backend/properties/edit-static-value',
                                    'id' => $propertyStaticValue->id,
                                    'property_id' => $propertyStaticValue->property_id,
                                    'property_group_id' => $property->property_group_id,
                                ]
                            )
                        );
                    }
                }
                $result .= Html::endTag('ul');
                Yii::$app->session->setFlash('warning', $result);
            }
        }
    }

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

    public function actions()
    {
        return [
            'save-info' => [
                'class' => SaveInfoAction::className(),
            ],
            'addImage' => [
                'class' => AddImageAction::className(),
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
                return 'TEXT';
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
        $object = Object::getForClass(Property::className());
        $model->property_group_id = $property_group_id;

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            $propertyHandler = PropertyHandlers::createHandler($model->handler);
            if (!$propertyHandler->changePropertyType($model)) {
                if ($model->is_column_type_stored) {
                    if ($model->isNewRecord) {
                        $col_type = $this->getColumnType($model->value_type);
                        $object = Object::findById($model->group->object_id);
                        Yii::$app->db->createCommand()
                            ->addColumn($object->column_properties_table_name, $model->key, $col_type)
                            ->execute();
                        if ($object->object_class == Form::className()) {
                            $submissionObject = Object::getForClass(Submission::className());
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
            }

            $save_result = $model->save();
            if ($save_result) {
                $this->runAction('save-info');
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

        return $this->render(
            'edit-property',
            [
                'model' => $model,
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'fieldinterpretParentId' => 0,
                'object' => $object,
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
        $object = Object::getForClass(PropertyStaticValues::className());
        $model->property_id = $property_id;
        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $save_result = $model->save();
            if ($save_result) {
                $this->checkDoubledSlugs($model->slug);
                $this->runAction('save-info');
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
                'object' => $object,
            ]
        );
    }


    public function actionAddStaticValue($key, $value, $returnUrl, $objectId = null, $objectModelId = null)
    {
        $model = new PropertyStaticValues();
        /** @var Property $property */
        $property = Property::findOne(['key'=>$key]);
        if (is_null($property)) {
            throw new NotFoundHttpException;
        }
        $model->property_id = $property->id;
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $this->checkDoubledSlugs($model->slug);
                $tags = [
                    ActiveRecordHelper::getCommonTag(Property::className()),
                    ActiveRecordHelper::getObjectTag(Property::className(), $property->id),
                    ActiveRecordHelper::getCommonTag(PropertyGroup::className()),
                    ActiveRecordHelper::getObjectTag(PropertyGroup::className(), $property->property_group_id),
                ];
                if (!is_null($objectId) && !is_null($objectModelId)) {
                    if ($property->multiple == 0) {
                        $propertyStaticValueIds = PropertyStaticValues::find()
                            ->select('id')
                            ->where(['property_id' => $property->id])
                            ->column();
                        ObjectStaticValues::deleteAll(
                            [
                                'object_id' => $objectId,
                                'object_model_id' => $objectModelId,
                                'property_static_value_id' => $propertyStaticValueIds,
                            ]
                        );
                    }
                    $objectStaticValues = new ObjectStaticValues;
                    $objectStaticValues->attributes = [
                        'object_id' => $objectId,
                        'object_model_id' => $objectModelId,
                        'property_static_value_id' => $model->id,
                    ];
                    $objectStaticValues->save();
                    $tags[] = ActiveRecordHelper::getCommonTag(Object::findById($objectId)->object_class);
                    $tags[] = ActiveRecordHelper::getObjectTag(
                        Object::findById($objectId)->object_class,
                        $objectModelId
                    );
                }
                TagDependency::invalidate(Yii::$app->cache, $tags);
                return $this->redirect($returnUrl);
            }
        } elseif ($value !== "") {
            $model->name = $value;
            $model->value = $value;
            $model->slug = Helper::createSlug($value);
            $model->sort_order = 0;
        }
        return $this->renderAjax('ajax-static-value', ['model' => $model]);
    }


    public function actionDeleteStaticValue($id, $property_id, $property_group_id)
    {
        /** @var PropertyStaticValues $model */
        $model = PropertyStaticValues::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
        }
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
        /** @var Property $model */
        $model = Property::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
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
                $item->delete();
            }
        }
        return $this->redirect(['group', 'id' => $group_id]);
    }

    public function actionDeleteGroup($id)
    {
        /** @var PropertyGroup $model */
        $model = PropertyGroup::findOne($id);
        if (is_null($model)) {
            throw new NotFoundHttpException;
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
                    $prop->delete();
                }
                $item->delete();
            }
        }

        return $this->redirect(['index']);
    }

    public function actionHandlers()
    {
        return $this->render('handlers');
    }
}
