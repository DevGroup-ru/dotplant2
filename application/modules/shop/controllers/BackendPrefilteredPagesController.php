<?php

namespace app\modules\shop\controllers;

use app\backend\components\BackendController;
use app\models\Object;
use app\models\PrefilteredPages;
use app\modules\shop\models\Product;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Url;

class BackendPrefilteredPagesController extends BackendController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['shop manage'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new PrefilteredPages();
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
        $model = new PrefilteredPages;
        $model->loadDefaultValues();

        if ($id !== null) {
            $model = PrefilteredPages::findOne($id);
        }

        $static_values_properties = [];

        $property_groups_ids_for_object = (new Query)->select('id')->from(PropertyGroup::tableName())->where(
                [
                    'object_id' => Object::getForClass(Product::className())->id,
                ]
            )->column();

        $properties = Property::find()->andWhere(['in', 'property_group_id', $property_groups_ids_for_object])->all();
        foreach ($properties as $prop) {
            /** @var Property $prop */
            $static_values_properties[$prop->id] = [
                'property' => $prop,
                'static_values_select' => PropertyStaticValues::getSelectForPropertyId($prop->id),
                'has_static_values' => $prop->has_static_values === 1,

            ];
        }

        $post = \Yii::$app->request->post();
        if ($model->load($post) && $model->validate()) {
            $save_result = $model->save();
            if ($save_result) {
                Yii::$app->session->setFlash('info', Yii::t('app', 'Object saved'));

                $returnUrl = Yii::$app->request->get(
                    'returnUrl',
                    ['index', 'id' => $model->id]
                );
                switch (Yii::$app->request->post('action', 'save')) {
                    case 'next':
                        return $this->redirect(
                            [
                                'edit',
                                'returnUrl' => $returnUrl,
                            ]
                        );
                    case 'back':
                        return $this->redirect($returnUrl);
                    default:
                        return $this->redirect(
                            Url::toRoute(
                                [
                                    'edit',
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
            'prefiltered-page-form',
            [
                'model' => $model,
                'static_values_properties' => $static_values_properties,
            ]
        );
    }

    public function actionDelete($id)
    {
        /** @var PrefilteredPages $model */
        $model = PrefilteredPages::findOne($id);
        $model->delete();
        Yii::$app->session->setFlash('info', Yii::t('app', 'Object removed'));
        return $this->redirect(
            Url::to(
                [
                    'index',
                ]
            )
        );
    }

    public function actionRemoveAll()
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = PrefilteredPages::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index']);
    }
}
