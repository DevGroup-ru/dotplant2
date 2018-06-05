<?php

namespace app\modules\shop\backend;

use app\backend\components\BackendController;
use app\models\BaseObject;
use app\modules\shop\models\Addon;
use app\modules\shop\models\AddonBindings;
use app\modules\shop\models\Currency;
use app\modules\shop\widgets\AddonsListWidget;
use app\traits\LoadModel;
use app\backend\traits\BackendRedirect;
use app\backend\actions\MultipleDelete;
use app\backend\actions\DeleteOne;
use app\modules\shop\models\AddonCategory;
use Yii;
use yii\caching\TagDependency;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Backend controller for managing addons, their categories and bindings
 * @package app\modules\shop\backend\
 */
class AddonsController extends BackendController
{
    use BackendRedirect;
    use LoadModel;
    /**
     * @inheritdoc
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
                        'roles' => ['product manage'],
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
            'remove-all-categories' => [
                'class' => MultipleDelete::className(),
                'modelName' => AddonCategory::className(),
            ],
            'delete-category' => [
                'class' => DeleteOne::className(),
                'modelName' => AddonCategory::className(),
            ],
            'remove-all-addons' => [
                'class' => MultipleDelete::className(),
                'modelName' => Addon::className(),
            ],
            'delete-addon' => [
                'class' => DeleteOne::className(),
                'modelName' => Addon::className(),
            ],
        ];
    }

    /**
     * Renders AddonCategory grid
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AddonCategory();
        $params = Yii::$app->request->get();

        $dataProvider = $searchModel->search($params);

        return $this->render(
            'index',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]
        );
    }

    /**
     * Add or edit existing AddonCategory model
     * @param null|string $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditCategory($id=null)
    {
        $model = $this->loadModel(AddonCategory::className(), $id, true);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                return $this->redirectUser($model->id, true, 'index', 'edit-category');
            }
        }
        return $this->render(
            'edit-category',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Shows list of Addons binded to specified AddonCategory
     * @param $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionViewCategory($id)
    {
        /** @var AddonCategory $addonCategory */
        $addonCategory = $this->loadModel(AddonCategory::className(), $id);
        $searchModel = new Addon();
        $params = Yii::$app->request->get();

        $dataProvider = $searchModel->search($params, $addonCategory->id);

        return $this->render(
            'addons-list',
            [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'addonCategory' => $addonCategory,
            ]
        );
    }

    /**
     * Add or edit existing Addon model
     * @param string|int $addon_category_id
     * @param null|string $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionEditAddon($addon_category_id=null, $id=null)
    {
        if ($addon_category_id === null && $id===null) {
            if (isset($_POST['addon_category_id'])) {
                $addon_category_id = intval($_POST['addon_category_id']);
            }
        }

        $addonCategory = $this->loadModel(AddonCategory::className(), $addon_category_id);
        /** @var Addon $model */
        $model = $this->loadModel(Addon::className(), $id, true);
        if ($id === null) {
            $model->loadDefaultValues();
            $model->addon_category_id = $addon_category_id;
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->save()) {
                return $this->redirectUser($model->id, true, 'view-category', 'edit-addon',['addon_category_id'=>$addon_category_id]);
            }
        }
        return $this->render(
            'edit-addon',
            [
                'model' => $model,
                'addonCategory' => $addonCategory,
            ]
        );
    }

    public function actionAjaxSearchAddons()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $result = [
            'more' => false,
            'results' => []
        ];
        $search = Yii::$app->request->get('search');
        if (!empty($search['term'])) {
            $query = new \yii\db\Query();
            $query->select('id, name as text, price, currency_id')->from(Addon::tableName())->andWhere(
                ['like', 'name', $search['term']]
            )->orderBy(['name' => SORT_ASC]);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $data = array_map(function($item) {
                $currency = Currency::findById($item['currency_id']);
                $price = $currency->format($item['price']);
                $item['text'] .= ' - ' . $price;
                return $item;
            }, $data);

            $result['results'] = array_values($data);
        }

        return $result;
    }

    public function actionReorder()
    {
        if (Yii::$app->request->isAjax === false) {
            throw new BadRequestHttpException;
        }
        Yii::$app->response->format = Response::FORMAT_JSON;


        $object_id = Yii::$app->request->get('object_id', null);
        $object_model_id = Yii::$app->request->get('object_model_id', null);
        $addons = (array) Yii::$app->request->post('addons', []);

        if ($object_id === null || $object_model_id === null || empty($addons)){
            throw new BadRequestHttpException;
        }

        array_walk($addons, function($item){
            return intval($item);
        });
        // magic begins
        $priorities=[];
        $start=0;
        $ids_sorted = $addons;
        sort($ids_sorted);
        foreach ($addons as $id) {
            $priorities[$id] = $start++;
        }
        $result = 'CASE addons.`id`';
        foreach ($priorities as $k => $v) {
            $result .= ' when "' . $k . '" then "' . $v . '"';
        }
        $case = $result . ' END';

        $query = <<< SQL
UPDATE {{%addon_bindings}} ab
INNER JOIN {{%addon}} addons ON addons.id = ab.addon_id
SET ab.`sort_order` = $case
SQL;
        $query .= ' where addons.id IN ('.implode(', ', $addons).')';

        Yii::$app->db->createCommand($query)->execute();

        TagDependency::invalidate(Yii::$app->cache, [Addon::className()]);

        $object = BaseObject::findById($object_id);
        if ($object === null) {
            throw new NotFoundHttpException;
        }
        $modelClassName = $object->object_class;
        $model = $this->loadModel($modelClassName, $object_model_id);

        return [
            'query' => $query,
            'data' => AddonsListWidget::widget([
                'object_id' => $object->id,
                'object_model_id' => $model->id,
                'bindedAddons' => $model->bindedAddons,
            ]),
            'error' => false,
        ];
    }

    public function actionAddAddonBinding($remove='0')
    {
        if (Yii::$app->request->isAjax === false) {
            throw new BadRequestHttpException;
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $addon_id = Yii::$app->request->post('addon_id', null);
        $object_id = Yii::$app->request->get('object_id', null);
        $object_model_id = Yii::$app->request->get('object_model_id', null);
        if ($addon_id === null || $object_id === null || $object_model_id === null){
            throw new BadRequestHttpException;
        }


        $addon = Addon::findById($addon_id);
        $object = BaseObject::findById($object_id);
        if ($addon === null || $object === null) {
            throw new NotFoundHttpException;
        }
        $modelClassName = $object->object_class;
        $model = $this->loadModel($modelClassName, $object_model_id);


        // ok, now all's ok, addon and model exist!
        try {

            if ($remove==='1') {
                $model->unlink('bindedAddons', $addon, true);
            } else {
                $model->link(
                    'bindedAddons',
                    $addon,
                    [
                        'sort_order' => count($model->bindedAddons),
                        'appliance_object_id' => $object->id,
                    ]
                );
            }

        } catch (\Exception $e) {
            if ( intval($e->getCode())  == 23000) {
                return [
                    'data' =>
                        Html::tag('div', Yii::t('app', 'Addon is already added'), ['class' => 'alert alert-info']) .
                        AddonsListWidget::widget([
                            'object_id' => $object->id,
                            'object_model_id' => $model->id,
                            'bindedAddons' => $model->bindedAddons,
                        ]),
                    'error' => false,
                ];
            } else {
                return [
                    'data' => Html::tag('div', $e->getMessage(), ['class' => 'alert alert-danger']),
                    'error' => $e->getMessage()
                ];
            }
        }
        TagDependency::invalidate(Yii::$app->cache, [Addon::className()]);
        return [
            'data' => AddonsListWidget::widget([
                'object_id' => $object->id,
                'object_model_id' => $model->id,
                'bindedAddons' => $model->bindedAddons,
            ]),
            'error' => false,
        ];
    }
}
