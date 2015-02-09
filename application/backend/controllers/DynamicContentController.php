<?php

namespace app\backend\controllers;

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
                'object_id' => $model->object_id,
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
                return $this->redirect(['/backend/dynamic-content/edit', 'id' => $model->id]);
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

    public function actionDelete($id)
    {
        $model = DynamicContent::findOne($id);
        $model->delete();
        Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        return $this->redirect(
            Url::to(
                [
                '/backend/dynamic-content/index',
                ]
            )
        );
    }

    public function actionRemoveAll()
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = DynamicContent::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index']);
    }
}
