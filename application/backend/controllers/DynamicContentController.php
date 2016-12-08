<?php

namespace app\backend\controllers;

use app\backend\actions\DeleteOne;
use app\backend\actions\MultipleDelete;
use app\backend\components\BackendController;
use app\backend\events\BackendEntityEditEvent;
use app\backend\traits\BackendRedirect;
use app\models\DynamicContent;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;

class DynamicContentController extends BackendController
{
    use BackendRedirect;

    const BACKEND_DYNAMIC_CONTENT_EDIT = 'backend-dynamic-content-edit';
    const BACKEND_DYNAMIC_CONTENT_EDIT_SAVE = 'backend-dynamic-content-edit-save';
    const BACKEND_DYNAMIC_CONTENT_EDIT_FORM = 'backend-dynamic-content-edit-form';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['content manage'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'remove-all' => [
                'class' => MultipleDelete::className(),
                'modelName' => DynamicContent::className(),
            ],
            'delete' => [
                'class' => DeleteOne::className(),
                'modelName' => DynamicContent::className(),
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new DynamicContent();
        $dataProvider = $searchModel->search($_GET);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    public function actionEdit($id = null)
    {
        $model = new DynamicContent;
        $model->loadDefaultValues();

        if ($id !== null) {
            $model = DynamicContent::findOne($id);
        }

        $static_values_properties = [];
        if (isset($_GET['DynamicContent'])) {
            if (isset($_GET['DynamicContent']['object_id'])) {
                $model->object_id = intval($_GET['DynamicContent']['object_id']);
            }
        }
        $property_groups_ids_for_object = (new Query)->select('id')->from(PropertyGroup::tableName())->where([])->column();

        $properties = Property::find()->where(
            [
                'has_static_values' => 1,
                'has_slugs_in_values' => 1,
            ]
        )->andWhere(['in', 'property_group_id', $property_groups_ids_for_object])->all();
        foreach ($properties as $prop) {
            $static_values_properties[$prop->id] = [
                'has_static_values' => true,
                'property' => $prop,
                'static_values_select' => PropertyStaticValues::getSelectForPropertyId($prop->id),
            ];
        }

        $post = \Yii::$app->request->post();
        if (isset($_GET['DynamicContent'])) {
            $post = $_GET;
        }
        if ($model->load($post) && $model->validate() && !isset($_GET['DynamicContent'])) {
            $save_result = $model->save();
            if ($save_result) {
                $saveStateEvent = new BackendEntityEditEvent($model);
                $this->trigger(self::BACKEND_DYNAMIC_CONTENT_EDIT_SAVE, $saveStateEvent);
                return $this->redirectUser($model->id);
            } else {
                \Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot update data'));
            }
        }

        return $this->render(
            'dynamic-content-form',
            [
                'model' => $model,
                'static_values_properties' => $static_values_properties,
            ]
        );
    }
}
