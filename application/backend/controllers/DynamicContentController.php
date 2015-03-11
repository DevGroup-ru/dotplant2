<?php

namespace app\backend\controllers;

use app\backend\actions\DeleteOne;
use app\backend\actions\MultipleDelete;
use app\models\DynamicContent;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;

class DynamicContentController extends Controller
{

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
        $property_groups_ids_for_object = (new Query)
            ->select('id')
            ->from(PropertyGroup::tableName())
            ->where(
                [
//                'object_id' => $model->object_id,
                ]
            )->column();

        $properties = Property::find()
            ->where(
                [
                'has_static_values' => 1,
                'has_slugs_in_values' => 1,
                ]
            )->andWhere(['in', 'property_group_id', $property_groups_ids_for_object])
            ->all();
        foreach ($properties as $prop) {
            $static_values_properties[$prop->id] = [
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
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));
                $returnUrl = Yii::$app->request->get('returnUrl', ['/backend/dynamic-content/index', 'id' => $model->id]);
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                '/backend/dynamic-content/edit',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    '/backend/dynamic-content/edit',
                                    'id' => $model->id,
                                    'returnUrl' => $returnUrl,
                                ]
                            )
                        );
                }
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
