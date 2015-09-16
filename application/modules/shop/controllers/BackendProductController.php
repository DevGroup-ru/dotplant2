<?php

namespace app\modules\shop\controllers;

use app\backend\actions\PropertyHandler;
use app\backend\components\BackendController;
use app\backend\events\BackendEntityEditEvent;
use app\modules\image\widgets\views\AddImageAction;
use app\modules\shop\models\Category;
use app\modules\image\models\Image;
use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\modules\shop\models\Product;
use app\models\Property;
use app\models\PropertyStaticValues;
use app\models\ViewObject;
use app\properties\HasProperties;
use app\modules\image\widgets\RemoveAction;
use app\modules\image\widgets\SaveInfoAction;
use app\modules\image\widgets\UploadAction;
use app\backend\actions\UpdateEditable;
use devgroup\JsTreeWidget\AdjacencyFullTreeDataAction;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class BackendProductController extends BackendController
{
    const EVENT_BACKEND_PRODUCT_EDIT = 'backend-product-edit';
    const EVENT_BACKEND_PRODUCT_EDIT_SAVE = 'backend-product-edit-save';
    const EVENT_BACKEND_PRODUCT_EDIT_FORM = 'backend-product-edit-form';

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
     * @return array
     */
    public function actions()
    {
        return [
            'getTree' => [
                'class' => AdjacencyFullTreeDataAction::className(),
                'class_name' => Category::className(),
                'model_label_attribute' => 'name',
            ],
            'getCatTree' => [
                'class' => 'app\backend\actions\JSSelectableTreeGetTree',
                'modelName' => 'app\modules\shop\models\Category',
                'label_attribute' => 'name',
                'vary_by_type_attribute' => null,
            ],
            'addImage' => [
                'class' => AddImageAction::className(),
            ],
            'upload' => [
                'class' => UploadAction::className(),
                'upload' => 'theme/resources/product-images',
            ],
            'remove' => [
                'class' => RemoveAction::className(),
                'uploadDir' => 'theme/resources/product-images',
            ],
            'save-info' => [
                'class' => SaveInfoAction::className(),
            ],
            'update-editable' => [
                'class' => UpdateEditable::className(),
                'modelName' => Product::className(),
                'allowedAttributes' => [
                    'currency_id' => function (Product $model, $attribute) {
                        if ($model === null || $model->currency === null || $model->currency_id === 0) {
                            return null;
                        }
                        return \yii\helpers\Html::tag(
                            'div',
                            $model->currency->name,
                            ['class' => $model->currency->name]
                        );
                    },
                    'price',
                    'old_price',
                    'active' => function (Product $model) {
                        if ($model === null || $model->active === null) {
                            return null;
                        }
                        if ($model->active === 1) {
                            $label_class = 'label-success';
                            $value = 'Active';
                        } else {
                            $value = 'Inactive';
                            $label_class = 'label-default';
                        }
                        return \yii\helpers\Html::tag(
                            'span',
                            Yii::t('app', $value),
                            ['class' => "label $label_class"]
                        );
                    },
                ],
            ],
            'property-handler' => [
                'class' => PropertyHandler::className(),
                'modelName' => Product::className()
            ]
        ];
    }

    /**
     * @return string
     * @throws ServerErrorHttpException
     */
    public function actionIndex()
    {
        $searchModel = new Product();
        $params = Yii::$app->request->get();
        /** @var ActiveDataProvider $dataProvider */
        $dataProvider = $searchModel->search($params);
        if (null !== $catId = Yii::$app->request->get('parent_id')) {
            $dataProvider->query->leftJoin(
                Object::getForClass(Product::className())->categories_table_name . ' cp',
                'cp.object_model_id=product.id'
            )->andWhere('product.parent_id=0 AND cp.category_id=:cur', [':cur' => $catId]);
        }
        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]
        );
    }

    /**
     * @param null $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws \Exception
     * @throws \yii\base\InvalidRouteException
     */
    public function actionEdit($id = null)
    {
        /*
         * @todo Продумать механизм сохранения изображений для нового продукта.
         * Сейчас для нового продукта скрывается форма добавления изображений.
         */
        if (null === $object = Object::getForClass(Product::className())) {
            throw new ServerErrorHttpException;
        }

        /** @var null|Product|HasProperties|\devgroup\TagDependencyHelper\ActiveRecordHelper $model */
        $model = null;
        $parent = null;
        if (null === $id) {
            $model = new Product();
            $model->loadDefaultValues();
            $parent_id = Yii::$app->request->get('owner_id', 0);
            if (0 !== intval($parent_id) && null !== Product::findById($parent_id)) {
                $model->parent_id = $parent_id;
            }
            $model->measure_id = $this->module->defaultMeasureId;
        } else {
            $model = Product::findById($id, null);
            if ((null !== $model) && ($model->parent_id > 0)) {
                $parent = Product::findById($model->parent_id, null);
            }
        }

        if (null === $model) {
            throw new NotFoundHttpException();
        }

        $model->loadRelatedProductsArray();

        $event = new BackendEntityEditEvent($model);
        $this->trigger(self::EVENT_BACKEND_PRODUCT_EDIT, $event);

        $post = \Yii::$app->request->post();

        if ($event->isValid && $model->load($post)) {
            $saveStateEvent = new BackendEntityEditEvent($model);
            $this->trigger(self::EVENT_BACKEND_PRODUCT_EDIT_SAVE, $saveStateEvent);

            if ($model->validate()) {
                if (isset($post['GeneratePropertyValue'])) {
                    $generateValues = $post['GeneratePropertyValue'];
                } else {
                    $generateValues = [];
                }
                if (isset($post['PropertyGroup'])) {
                    $model->option_generate = Json::encode(
                        [
                            'group' => $post['PropertyGroup']['id'],
                            'values' => $generateValues
                        ]
                    );
                } else {
                    $model->option_generate = '';
                }

                $save_result = $model->save();
                $model->saveProperties($post);
                $model->saveRelatedProducts();

                if (null !== $view_object = ViewObject::getByModel($model, true)) {
                    if ($view_object->load($post, 'ViewObject')) {
                        if ($view_object->view_id <= 0) {
                            $view_object->delete();
                        } else {
                            $view_object->save();
                        }
                    }
                }

                if ($save_result) {
                    $categories = isset($post['Product']['categories']) ? $post['Product']['categories'] : [];

                    $model->saveCategoriesBindings($categories);

                    $this->runAction('save-info', ['model_id'=>$model->id]);
                    $model->invalidateTags();


                    $action = Yii::$app->request->post('action', 'save');
                    if (Yii::$app->request->post('AddPropertyGroup') || Yii::$app->request->post('RemovePropertyGroup')) {
                        $action = 'save';
                    }
                    $returnUrl = Yii::$app->request->get('returnUrl', ['index']);
                    switch ($action) {
                        case 'next':
                            return $this->redirect(
                                [
                                    'edit',
                                    'returnUrl' => $returnUrl,
                                    'parent_id' => Yii::$app->request->get('parent_id', null)
                                ]
                            );
                        case 'back':
                            return $this->redirect($returnUrl);
                        default:
                            return $this->redirect(
                                Url::toRoute([
                                    'edit',
                                    'id' => $model->id,
                                    'returnUrl' => $returnUrl,
                                    'parent_id' => $model->main_category_id
                                ])
                            );
                    }
                } else {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
                }
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Cannot save data'));
            }
        }

        $items = ArrayHelper::map(
            Category::find()->all(),
            'id',
            'name'
        );


        return $this->render(
            'product-form',
            [
                'object' => $object,
                'model' => $model,
                'items' => $items,
                'selected' => $model->getCategoryIds(),
                'parent' => $parent,
            ]
        );
    }

    /**
     * @param $id
     * @throws NotFoundHttpException
     * @throws \yii\db\Exception
     */
    public function actionGenerate($id)
    {
        $post = \Yii::$app->request->post();
        if (!isset($post['GeneratePropertyValue'])) {
            throw new NotFoundHttpException();
        }
        $parent = Product::findById($id, null);
        if ($parent === null) {
            throw new NotFoundHttpException();
        }

        $object = Object::getForClass(Product::className());
        $catIds = (new Query())->select('category_id')->from([$object->categories_table_name])->where(
            'object_model_id = :id',
            [':id' => $id]
        )->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->column();


        if (isset($post['GeneratePropertyValue'])) {
            $generateValues = $post['GeneratePropertyValue'];
            $post['AddPropertyGroup']['Product'] = $post['PropertyGroup']['id'];
        } else {
            $generateValues = [];
        }
        $parent->option_generate = Json::encode(
            [
                'group' => $post['PropertyGroup']['id'],
                'values' => $generateValues
            ]
        );
        $parent->save();

        $postProperty = [];
        foreach ($post['GeneratePropertyValue'] as $key_property => $values) {
            $inner = [];
            foreach ($values as $key_value => $trash) {
                $inner[] = [$key_property => $key_value];
            }
            $postProperty[] = $inner;
        }

        $optionProperty = self::generateOptions($postProperty);

        foreach ($optionProperty as $option) {
            /** @var Product|HasProperties $model */
            $model = new Product;
            $model->load($post);

            $model->parent_id = $parent->id;
            $nameAppend = [];
            $slugAppend = [];
            $tempPost = [];

            // @todo something
            foreach ($option as $optionValue) {
                foreach ($optionValue as $propertyKey => $propertyValue) {
                    if (!isset($valueModels[$propertyKey])) {
                        $propertyStaticValues = PropertyStaticValues::findOne($propertyValue);
                        $propertyValue = PropertyStaticValues::findById($propertyValue);
                        $key = $propertyStaticValues->property->key;
                        $tempPost[$key] = $propertyValue;
                    }
                    $nameAppend[] = $propertyValue['name'];
                    $slugAppend[] = $propertyValue['id'];
                }
            }

            $model->measure_id = $parent->measure_id;
            $model->name = $parent->name . ' (' . implode(', ', $nameAppend) . ')';
            $model->slug = $parent->slug . '-' . implode('-', $slugAppend);
            $save_model = $model->save();
            $postPropertyKey = 'Properties_Product_' . $model->id;
            $post[$postPropertyKey] = $tempPost;
            if ($save_model) {
                foreach (array_keys($parent->propertyGroups) as $key) {
                    $opg = new ObjectPropertyGroup();
                    $opg->attributes = [
                        'object_id' => $parent->object->id,
                        'object_model_id' => $model->id,
                        'property_group_id' => $key,
                    ];
                    $opg->save();
                }
                $model->saveProperties(
                    [
                        'Properties_Product_' . $model->id => $parent->abstractModel->attributes,
                    ]
                );

                $model->saveProperties($post);

                unset($post[$postPropertyKey]);

                $add = [];

                foreach ($catIds as $value) {
                    $add[] = [
                        $value,
                        $model->id
                    ];
                }

                if (!empty($add)) {
                    Yii::$app->db->createCommand()->batchInsert(
                        $object->categories_table_name,
                        ['category_id', 'object_model_id'],
                        $add
                    )->execute();
                }

                $params = $parent->images;
                if (!empty($params)) {
                    $rows = [];
                    foreach ($params as $param) {
                        $rows[] = [
                            $param['object_id'],
                            $model->id,
                            $param['filename'],
                            $param['image_title'],
                            $param['image_alt'],
                            $param['sort_order'],
                        ];
                    }

                    Yii::$app->db->createCommand()->batchInsert(
                        Image::tableName(),
                        [
                            'object_id',
                            'object_model_id',
                            'filename',
                            'image_title',
                            'image_alt',
                            'sort_order',
                        ],
                        $rows
                    )->execute();

                }
            }


        }
    }

    /**
     * Clone product action.
     * @param integer $id
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionClone($id, $returnUrl = ['index'])
    {
        /** @var Product|HasProperties $model */
        $model = Product::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }

        /** @var Product|HasProperties $newModel */
        $newModel = new Product;
        $newModel->setAttributes($model->attributes, false);
        $time = time();
        $newModel->name .= ' (copy ' . date('Y-m-d h:i:s', $time) . ')';
        $newModel->slug .= '-copy-' . date('Ymdhis', $time);
        $newModel->id = null;
        if ($newModel->save()) {
            $object = Object::getForClass(get_class($newModel));
            // save categories
            $categoriesTableName = Object::getForClass(Product::className())->categories_table_name;
            $query = new Query();
            $params = $query->select(['category_id', 'sort_order'])->from($categoriesTableName)->where(
                ['object_model_id' => $model->id]
            )->all();
            if (!empty($params)) {
                $rows = [];
                foreach ($params as $param) {
                    $rows[] = [
                        $param['category_id'],
                        $newModel->id,
                        $param['sort_order'],
                    ];
                }
                Yii::$app->db->createCommand()->batchInsert(
                    $categoriesTableName,
                    [
                        'category_id',
                        'object_model_id',
                        'sort_order'
                    ],
                    $rows
                )->execute();
            }

            // save images bindings
            $params = $query->select(
                ['object_id', 'filename', 'image_title', 'image_alt', 'sort_order']
            )->from(Image::tableName())->where(
                [
                    'object_id' => $object->id,
                    'object_model_id' => $model->id
                ]
            )->all();
            if (!empty($params)) {
                $rows = [];
                foreach ($params as $param) {
                    $rows[] = [
                        $param['object_id'],
                        $newModel->id,
                        $param['filename'],
                        $param['image_title'],
                        $param['image_alt'],
                        $param['sort_order'],
                    ];
                }
                Yii::$app->db->createCommand()->batchInsert(
                    Image::tableName(),
                    [
                        'object_id',
                        'object_model_id',
                        'filename',
                        'image_title',
                        'image_alt',
                        'sort_order',
                    ],
                    $rows
                )->execute();
            }
            $newModelProps = [];
            foreach (array_keys($model->propertyGroups) as $key) {
                $opg = new ObjectPropertyGroup();
                $opg->attributes = [
                    'object_id' => $object->id,
                    'object_model_id' => $newModel->id,
                    'property_group_id' => $key,
                ];
                $opg->save();
                $props = Property::getForGroupId($key);
                foreach ($props as $prop) {
                    $propValues = $model->getPropertyValuesByPropertyId($prop->id);
                    if ($propValues !== null) {
                        foreach ($propValues->values as $val) {
                            $valueToSave = ArrayHelper::getValue($val, 'psv_id', $val['value']);
                            $newModelProps[$prop->key][] = $valueToSave;
                        }
                    }
                }
            }
            $newModel->saveProperties(
                [
                    'Properties_Product_' . $newModel->id => $newModelProps,
                ]
            );
            Yii::$app->session->setFlash('success', Yii::t('app', 'Product has been cloned successfully.'));
            $this->redirect(['edit', 'id' => $newModel->id, 'returnUrl' => $returnUrl]);
        }
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        /** @var Product $model */
        if (null === $model = Product::findOne($id)) {
            throw new NotFoundHttpException;
        }

        if (Yii::$app->request->get('returnUrl') !== null) {
            $redirect = Yii::$app->request->get('returnUrl');
        } elseif ($model->parent_id == 0) {
            $redirect = Url::toRoute(['index']);
        } else {
            $redirect = Url::toRoute(['edit', 'id' => $model->parent_id]);
        }

        if (!$model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('app', 'The object is placed in the cart'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object has been removed'));
        }

        return $this->redirect($redirect);
    }

    /**
     * @param $parent_id
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionRemoveAll($parent_id)
    {
        $items = Yii::$app->request->post('items', []);
        if (!empty($items)) {
            $items = Product::find()->where(['in', 'id', $items])->all();
            foreach ($items as $item) {
                $item->delete();
            }
        }

        return $this->redirect(['index', 'parent_id' => $parent_id]);
    }

    /**
     * @param $tableName
     * @param $ids
     * @param string $field
     * @return bool
     * @throws \yii\db\Exception
     */
    public static function sortModels($tableName, $ids, $field = 'sort_order')
    {
        $priorities = [];
        $start = 0;
        $ids_sorted = $ids;
        sort($ids_sorted);
        foreach ($ids as $id) {
            $priorities[$id] = $ids_sorted[$start++];
        }
        $sql = "UPDATE " . $tableName . " SET $field = " . self::generateCase($priorities) . " WHERE id IN(" . implode(
                ', ',
                $ids
            ) . ")";

        return Yii::$app->db->createCommand(
            $sql
        )->execute() > 0;

    }

    /**
     * Рекурсивный генератор свойств для создания комплектаций.
     * @param array $array Трехмерный массив вида:
     * [[['{property1}' => '{value1}']], [['{property1}' => '{value2}']], [['{property2}' => '{value1}']], [['{property2}' => '{value1}']]]
     * @param array $result Используется для передачи результатов внутри рекурсии
     * @param integer $count Счетчик внутри рекурсии
     * @return array
     */
    public static function generateOptions($array, $result = [], $count = 0)
    {
        if (empty($result)) {
            foreach ($array[$count] as $value) {
                $result[] = [$value];
            }
            $count++;
            $arResult = self::generateOptions($array, $result, $count);
        } else {
            if (isset($array[$count])) {
                $nextResult = [];
                foreach ($array[$count] as $value) {
                    foreach ($result as $resValue) {
                        $nextResult[] = array_merge($resValue, [$value]);
                    }
                }
                $count++;
                $arResult = self::generateOptions($array, $nextResult, $count);
            } else {
                return $result;
            }
        }
        return $arResult;
    }

    /**
     * @param $priorities
     * @return string
     */
    private static function generateCase($priorities)
    {
        $result = 'CASE `id`';
        foreach ($priorities as $k => $v) {
            $result .= ' when "' . $k . '" then "' . $v . '"';
        }
        return $result . ' END';
    }

    /**
     * @return array
     */
    public function actionAjaxRelatedProduct()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $result = [
            'more' => false,
            'results' => []
        ];
        $search = Yii::$app->request->get('search');
        if (!empty($search['term'])) {
            $query = new \yii\db\Query();
            $query->select('id, name AS text')->from(Product::tableName())->andWhere(
                ['like', 'name', $search['term']]
            )->andWhere(['active' => 1])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
            $command = $query->createCommand();
            $data = $command->queryAll();

            $result['results'] = array_values($data);
        }

        return $result;
    }
}