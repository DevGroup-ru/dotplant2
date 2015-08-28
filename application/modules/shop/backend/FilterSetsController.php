<?php

namespace app\modules\shop\backend;

use app\backend\components\BackendController;
use app\modules\shop\models\Category;
use app\models\Object;
use app\modules\shop\models\Product;
use app\models\Property;
use app\models\PropertyGroup;
use app\models\PropertyStaticValues;
use app\modules\shop\models\FilterSets;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\debug\components\search\Filter;
use yii\filters\AccessControl;
use devgroup\JsTreeWidget\AdjacencyFullTreeDataAction;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FilterSetsController extends BackendController
{
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
                        'roles' => ['category manage'],
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
            'getTree' => [
                'class' => AdjacencyFullTreeDataAction::className(),
                'class_name' => Category::className(),
                'model_label_attribute' => 'name',
            ],
        ];
    }

    public function actionIndex($category_id=null)
    {
        $searchModel = new Category();
        $searchModel->active = 1;

        $params = Yii::$app->request->get();

        $dataProvider = $searchModel->search($params);

        $selectedCategory = null;

        if ($category_id !== null) {
            $selectedCategory = Category::findById($category_id);


        }
        if ($selectedCategory !== null) {
            if (Yii::$app->request->isPost === true) {

                $newProperty = isset($_GET['add_property_id']) ? Property::findById($_GET['add_property_id']) : null;
                if ($newProperty !== null) {

                    $filterSet = new FilterSets();
                    $filterSet->category_id = $selectedCategory->id;
                    $filterSet->property_id = $newProperty->id;
                    $filterSet->sort_order = 65535;
                    $filterSet->save();
                }
            }
        }

        $groups = PropertyGroup::getForObjectId(Object::getForClass(Product::className())->id, false);
        $propertiesDropdownItems = [];
        foreach ($groups as $group) {
            $item = [
                'label' => $group->name,
                'url' => '#',
                'items' => [],
            ];
            $properties = Property::getForGroupId($group->id);
            foreach ($properties as $prop) {
                $item['items'][] = [
                    'label' => $prop->name,
                    'url' => '?category_id=' . $category_id . '&add_property_id='.$prop->id,
                    'linkOptions' => [
                        'class' => 'add-property-to-filter-set',
                        'data-property-id' => $prop->id,
                        'data-action' => 'post',
                    ],
                ];
            }

            $propertiesDropdownItems[] = $item;
        }

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
                'selectedCategory' => $selectedCategory,
                'propertiesDropdownItems' => $propertiesDropdownItems,
            ]
        );
    }

    public function actionModifyFilterSet()
    {
        if (Yii::$app->request->isPost === false || Yii::$app->request->isAjax === false || !isset($_POST['id'])) {
            throw new BadRequestHttpException;
        }

        $filterSet = $this->loadModel($_POST['id']);
        $filterSet->load(Yii::$app->request->post());
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $filterSet->save();
    }

    public function actionDeleteFilterSet($id, $category_id)
    {
        if (Yii::$app->request->isPost === false) {
            throw new BadRequestHttpException;
        }
        $filterSet = $this->loadModel($id);
        $filterSet->delete();
        return $this->redirect(['index', 'category_id' => $category_id]);
    }

    public function actionModifyPsv()
    {
        if (Yii::$app->request->isPost === false || !isset($_POST['id'], $_POST['value'], $_POST['key'])) {
            throw new BadRequestHttpException;
        }

        if (in_array($_POST['key'], ['dont_filter', 'slug']) === false) {
            throw new BadRequestHttpException;
        }

        /** @var PropertyStaticValues|null $psv */
        $psv = PropertyStaticValues::findOne($_POST['id']);
        if ($psv === null) {
            throw new NotFoundHttpException;
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $psv->setAttribute($_POST['key'], $_POST['value']);

        return $psv->save();
    }

    private function loadModel($id)
    {
        $model = FilterSets::find()
            ->where(['id'=>$id])
            ->one();
        if ($model === null) {
            throw new NotFoundHttpException;
        };
        return $model;
    }

    public function actionSaveSorted()
    {
        if (Yii::$app->request->isPost === false || !isset($_POST['ids'], $_POST['filterSets'])) {
            throw new BadRequestHttpException;
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        $ids = (array) $_POST['ids'];
        if ($_POST['filterSets'] === '1') {
            $result = FilterSets::sortModels($ids);
            $this->invalidateTags(FilterSets::className(), $ids);
        } else {
            $result = PropertyStaticValues::sortModels($ids);
            $this->invalidateTags(PropertyStaticValues::className(), $ids);
            $this->invalidateTags(FilterSets::className(), []);
        }

        return $result;
    }

    private function invalidateTags($className, $ids)
    {
        $tags = [
            ActiveRecordHelper::getCommonTag($className),
        ];
        foreach ($ids as $id) {
            $tags[] = ActiveRecordHelper::getObjectTag($className, $id);
        }
        \yii\caching\TagDependency::invalidate(
            Yii::$app->cache,
            $tags
        );
    }
}