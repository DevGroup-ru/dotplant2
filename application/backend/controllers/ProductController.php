<?php

namespace app\backend\controllers;

use app\models\Category;
use app\models\Image;
use app\models\Object;
use app\models\ObjectPropertyGroup;
use app\models\Product;
use app\models\PropertyStaticValues;
use app\models\ViewObject;
use app\properties\HasProperties;
use app\widgets\image\RemoveAction;
use app\widgets\image\SaveInfoAction;
use app\widgets\image\UploadAction;
use app\backend\actions\UpdateEditable;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

class ProductController extends Controller
{
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

    public function actions()
    {
        return [
            'getTree' => [
                'class' => 'app\backend\actions\JSTreeGetTrees',
                'modelName' => 'app\models\Category',
                'label_attribute' => 'name',
                'vary_by_type_attribute' => null,
            ],
            'getCatTree' => [
                'class' => 'app\backend\actions\JSSelectableTreeGetTree',
                'modelName' => 'app\models\Category',
                'label_attribute' => 'name',
                'vary_by_type_attribute' => null,
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
                ],
            ],
        ];
    }

    public function actionIndex()
    {

        $searchModel = new Product();
        $params = Yii::$app->request->get();
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


    public function actionEdit($id = null)
    {
        /*
         * todo: Продумать механизм сохранения изображений для нового продукта.
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
        } else {
            $model = Product::findById($id, null, null);
            if ((null !== $model) && ($model->parent_id > 0)) {
                $parent = Product::findById($model->parent_id, null, null);
            }
        }

        if (null === $model) {
            throw new NotFoundHttpException;
        }

        $model->loadRelatedProductsArray();

        $post = \Yii::$app->request->post();


        if ($model->load($post)) {
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

                    $this->runAction('save-info');
                    $model->invalidateTags();


                    $action = Yii::$app->request->post('action', 'save');
                    if (Yii::$app->request->post('AddPropetryGroup')) {
                        $action = 'save';
                    }
                    $returnUrl = Yii::$app->request->get('returnUrl', ['/backend/product/index']);
                    switch ($action) {
                        case 'next':
                            return $this->redirect(
                                [
                                    '/backend/product/edit',
                                    'returnUrl' => $returnUrl,
                                    'parent_id' => Yii::$app->request->get('parent_id', null)
                                ]
                            );
                        case 'back':
                            return $this->redirect($returnUrl);
                        default:
                            return $this->redirect(
                                Url::toRoute(
                                    [
                                        '/backend/product/edit',
                                        'id' => $model->id,
                                        'returnUrl' => $returnUrl,
                                        'parent_id' => $model->main_category_id
                                    ]
                                )
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

    public function actionGenerate($id)
    {
        $post = \Yii::$app->request->post();
        if (!isset($post['GeneratePropertyValue'])) {
            throw new NotFoundHttpException;
        }
        $parent = Product::findById($id, null, null);
        if ($parent === null) {
            throw new NotFoundHttpException;
        }

        $object = Object::getForClass(Product::className());
        $catIds = (new Query())->select('category_id')->from([$object->categories_table_name])->where(
            'object_model_id = :id',
            [':id' => $id]
        )->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->column();


        if (isset($post['GeneratePropertyValue'])) {
            $generateValues = $post['GeneratePropertyValue'];
            $post['AddPropetryGroup']['Product'] = $post['PropertyGroup']['id'];
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
                $query = new Query();
                $params = $query->select(
                    ['object_id', 'filename', 'image_src', 'thumbnail_src', 'image_description', 'sort_order']
                )->from(Image::tableName())->where(
                    [
                        'object_id' => $model->object->id,
                        'object_model_id' => $parent->id
                    ]
                )->all();
                if (!empty($params)) {
                    $rows = [];
                    foreach ($params as $param) {
                        $rows[] = [
                            $param['object_id'],
                            $model->id,
                            $param['filename'],
                            $param['image_src'],
                            $param['thumbnail_src'],
                            $param['image_description'],
                            $param['sort_order'],
                        ];
                    }
                    Yii::$app->db->createCommand()->batchInsert(
                        Image::tableName(),
                        [
                            'object_id',
                            'object_model_id',
                            'filename',
                            'image_src',
                            'thumbnail_src',
                            'image_description',
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
    public function actionClone($id)
    {
        /** @var Product|HasProperties $model */
        $model = Product::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException;
        }
        if (1 === $model->is_deleted) {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Unable to clone a remote object') . '!');
            $this->redirect(Url::toRoute('index'));
            return;
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
                ['object_id', 'filename', 'image_src', 'thumbnail_src', 'image_description', 'sort_order']
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
                        $param['image_src'],
                        $param['thumbnail_src'],
                        $param['image_description'],
                        $param['sort_order'],
                    ];
                }
                Yii::$app->db->createCommand()->batchInsert(
                    Image::tableName(),
                    [
                        'object_id',
                        'object_model_id',
                        'filename',
                        'image_src',
                        'thumbnail_src',
                        'image_description',
                        'sort_order',
                    ],
                    $rows
                )->execute();
            }
            foreach (array_keys($model->propertyGroups) as $key) {
                $opg = new ObjectPropertyGroup();
                $opg->attributes = [
                    'object_id' => $object->id,
                    'object_model_id' => $newModel->id,
                    'property_group_id' => $key,
                ];
                $opg->save();
            }
            $newModel->saveProperties(
                [
                    'Properties_Product_' . $newModel->id => $model->abstractModel->attributes,
                ]
            );
            Yii::$app->session->setFlash('success', Yii::t('shop', 'Product has been cloned successfully.'));
            $this->redirect(['/backend/product/edit', 'id' => $newModel->id]);
        }
    }

    public function actionDelete($id)
    {
        if (null === $model = Product::findOne($id)) {
            throw new NotFoundHttpException;
        }

        if ($model->parent_id == 0) {
            $redirect = Url::toRoute(['index']);
        } else {
            $redirect = Url::toRoute(['edit', 'id' => $model->parent_id]);
        }

        if (!$model->delete()) {
            Yii::$app->session->setFlash('info', Yii::t('shop', 'The object is placed in the cart'));
        } else {
            Yii::$app->session->setFlash('info', Yii::t('app', 'Object has been removed'));
        }

        return $this->redirect($redirect);
    }

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

    public static function sortModels($tableName, $ids, $field = 'sort_order')
    {
        $priorities = [];
        $start = 0;
        $ids_sorted = $ids;
        sort($ids_sorted);
        foreach ($ids as $id) {
            $priorities[$id] = $ids_sorted[$start ++];
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
            $count ++;
            $arResult = self::generateOptions($array, $result, $count);
        } else {
            if (isset($array[$count])) {
                $nextResult = [];
                foreach ($array[$count] as $value) {
                    foreach ($result as $resValue) {
                        $nextResult[] = array_merge($resValue, [$value]);
                    }
                }
                $count ++;
                $arResult = self::generateOptions($array, $nextResult, $count);
            } else {
                return $result;
            }
        }
        return $arResult;
    }

    private static function generateCase($priorities)
    {
        $result = 'CASE `id`';
        foreach ($priorities as $k => $v) {
            $result .= ' when "' . $k . '" then "' . $v . '"';
        }
        return $result . ' END';
    }

    public function actionRestore($id = null)
    {
        if (null === $id) {
            new NotFoundHttpException();
        }

        if (null === $model = Product::findOne(['id' => $id])) {
            new NotFoundHttpException();
        }

        $model->restoreFromTrash();

        Yii::$app->session->setFlash('success', Yii::t('app', 'Object successfully restored'));

        return $this->redirect(Url::toRoute(['edit', 'id' => $id]));
    }

    public function actionAjaxRelatedProduct($search = null, $id = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $result = [
            'more' => false,
            'results' => []
        ];

        if (!empty($search)) {
            $query = new \yii\db\Query();
            $query->select('id, name AS text')->from(Product::tableName())->andWhere(
                ['like', 'name', $search]
            )->andWhere(['is_deleted' => 0])->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC]);
            $command = $query->createCommand();
            $data = $command->queryAll();

            $result['results'] = array_values($data);
        } elseif ($id > 0) {
            if (null !== $model = Product::findById($id)) {
                foreach ($model->relatedProducts as $row) {
                    $result['results'][] = ['id' => $row->id, 'text' => $row->name];
                }
            } else {
                $result['results'] = ['id' => 0, 'text' => 'Ничего не найдено.'];
            }
        } else {
            $result['results'] = ['id' => 0, 'text' => 'Ничего не найдено.'];
        }

        return $result;
    }
}
