<?php

namespace app\modules\shop\models;

use app\behaviors\CleanRelations;
use app\behaviors\Tree;
use app\components\Helper;
use app\modules\image\models\Image;
use app\models\Object;
use app\modules\data\components\ImportableInterface;
use app\modules\data\components\ExportableInterface;
use app\modules\shop\data\FilterPagination;
use app\modules\shop\traits\HasAddonTrait;
use app\modules\shop\ShopModule;
use app\properties\HasProperties;
use app\properties\PropertiesHelper;
use app\traits\GetImages;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\data\Pagination;
use yii\web\ServerErrorHttpException;

/**
 * This is the model class for table "product".
 *
 * @property integer $id
 * @property integer $main_category_id
 * @property string $name
 * @property string $title
 * @property string $h1
 * @property string $meta_description
 * @property string $breadcrumbs_label
 * @property string $slug
 * @property string $slug_compiled
 * @property integer $slug_absolute
 * @property string $content
 * @property string $announce
 * @property integer $sort_order
 * @property integer $active
 * @property double $price
 * @property double $old_price
 * @property integer $parent_id
 * @property integer $currency_id
 * @property integer $measure_id
 * @property Product[] $relatedProducts
 * @property Measure $measure
 * @property string $sku
 * @property boolean $unlimited_count
 * @property string $date_added
 * @property string $date_modified
 * Relations:
 * @property Category $category
 * @property Product[] $children
 * @property Category $mainCategory
 */

class Product extends ActiveRecord implements ImportableInterface, ExportableInterface, \JsonSerializable
{
    use GetImages;
    use HasAddonTrait;

    private static $identity_map = [];
    private static $slug_to_id = [];
    private $category_ids = null;

    public $relatedProductsArray = [];

    /**
     * @var null|WarehouseProduct[] Stores warehouses state of product. Use Product::getWarehousesState() to retrieve
     */
    private $activeWarehousesState = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sku'], 'filter', 'filter' => ['\app\backend\components\Helper', 'toString']],
            [['main_category_id', 'name', 'slug'], 'required'],
            [
                [
                    'main_category_id',
                    'slug_absolute',
                    'sort_order',
                    'parent_id',
                    'currency_id',
                ],
                'integer'
            ],
            [
                [
                    'name',
                    'title',
                    'h1',
                    'meta_description',
                    'breadcrumbs_label',
                    'content',
                    'announce',
                    'option_generate',
                    'sku'
                ],
                'string'
            ],
            [
                [
                    'unlimited_count',
                    'active',
                    'slug_absolute',
                ],
                'boolean',
            ],
            [['price', 'old_price'], 'number'],
            [['slug'], 'string', 'max' => 80],
            [['slug_compiled'], 'string', 'max' => 180],
            [['old_price', 'price',], 'default', 'value' => 0,],
            [['active', 'unlimited_count'], 'default', 'value' => true],
            [['parent_id', 'slug_absolute', 'sort_order'], 'default', 'value' => 0],
            [['sku', 'name'], 'default', 'value' => ''],
            [['unlimited_count', 'currency_id', 'measure_id'], 'default', 'value' => 1],
            [['relatedProductsArray'], 'safe'],
            [['slug'], 'unique', 'targetAttribute' => ['slug', 'main_category_id']],
            [['date_added', 'date_modified'], 'safe'],

        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'main_category_id' => Yii::t('app', 'Main Category ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'name' => Yii::t('app', 'Name'),
            'title' => Yii::t('app', 'Title'),
            'h1' => Yii::t('app', 'H1'),
            'meta_description' => Yii::t('app', 'Meta Description'),
            'breadcrumbs_label' => Yii::t('app', 'Breadcrumbs Label'),
            'slug' => Yii::t('app', 'Slug'),
            'slug_compiled' => Yii::t('app', 'Slug Compiled'),
            'slug_absolute' => Yii::t('app', 'Slug Absolute'),
            'content' => Yii::t('app', 'Content'),
            'announce' => Yii::t('app', 'Announce'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'active' => Yii::t('app', 'Active'),
            'price' => Yii::t('app', 'Price'),
            'old_price' => Yii::t('app', 'Old Price'),
            'option_generate' => Yii::t('app', 'Option Generate'),
            'in_warehouse' => Yii::t('app', 'Items in warehouse'),
            'sku' => Yii::t('app', 'SKU'),
            'unlimited_count' => Yii::t('app', 'Unlimited items(don\'t count in warehouse)'),
            'reserved_count' => Yii::t('app', 'Items reserved'),
            'relatedProductsArray' => Yii::t('app', 'Related products'),
            'currency_id' => Yii::t('app', 'Currency'),
            'measure_id' => Yii::t('app', 'Measure'),
            'date_added' => Yii::t('app', 'Date Added'),
            'date_modified' => Yii::t('app', 'Date Modified'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => Tree::className(),
                'activeAttribute' => 'active',
                'sortOrder' => [
                    'sort_order' => SORT_ASC,
                    'id' => SORT_ASC
                ],
            ],
            [
                'class' => HasProperties::className(),
            ],
            [
                'class' => ActiveRecordHelper::className(),
            ],
            [
                'class' => CleanRelations::className(),
            ],
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'date_added',
                'updatedAtAttribute' => 'date_modified',
                'value' => new Expression('NOW()'),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['date_added'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['date_modified'],
                ],
            ],
        ];
    }

    /**
     * Search products
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find()->where(['parent_id' => 0])->with('images');
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'slug', $this->slug]);
        $query->andFilterWhere(['active' => $this->active]);
        $query->andFilterWhere(['price' => $this->price]);
        $query->andFilterWhere(['old_price' => $this->old_price]);
        $query->andFilterWhere(['like', 'sku', $this->sku]);
        return $dataProvider;
    }

    /**
     * Returns model instance by ID using per-request Identity Map and cache
     * @param $id
     * @param int $isActive Return only active
     * @return Product
     */
    public static function findById($id, $isActive = 1)
    {
        if (!is_numeric($id)) {
            return null;
        }
        if (!isset(static::$identity_map[$id])) {
            $cacheKey = static::tableName() . ":$id:$isActive";
            if (false === $model = Yii::$app->cache->get($cacheKey)) {
                $model = static::find()->where(['id' => $id])->with('images');
                if (null !== $isActive) {
                    $model->andWhere(['active' => $isActive]);
                }
                if (null !== $model = $model->one()) {
                    /**
                     * @var self $model
                     */
                    static::$slug_to_id[$model->slug] = $id;
                    Yii::$app->cache->set(
                        $cacheKey,
                        $model,
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    ActiveRecordHelper::getCommonTag(static::className())
                                ]
                            ]
                        )
                    );
                }
            }
            static::$identity_map[$id] = $model;
        }
        return static::$identity_map[$id];
    }

    /**
     * Find a product by slug
     * @param string $slug
     * @param int $inCategoryId
     * @param int $isActive
     * @return Product
     */
    public static function findBySlug($slug, $inCategoryId = null, $isActive = 1)
    {
        if (!isset(static::$slug_to_id[$slug])) {
            $cacheKey = static::tableName() . "$slug:$inCategoryId";
            if (false === $model = Yii::$app->cache->get($cacheKey)) {
                $tags = [];
                /** @var ActiveQuery $model */
                $query = static::find()->where(
                    [
                        'slug' => $slug,
                        'active' => $isActive,
                    ]
                )->with('images', 'relatedProducts');
                if (!is_null($inCategoryId)) {
                    $query->andWhere(['main_category_id' => $inCategoryId]);
                    $tags[] = ActiveRecordHelper::getObjectTag(Category::className(), $inCategoryId);
                }
                $model = $query->one();
                /**
                 * @var self|null $model
                 */
                if (is_null($model)) {
                    $tags[] = ActiveRecordHelper::getCommonTag(Product::className());
                } else {
                    $tags[] = ActiveRecordHelper::getObjectTag(Product::className(), $model->id);
                }
                Yii::$app->cache->set(
                    $cacheKey,
                    $model,
                    86400,
                    new TagDependency([
                        'tags' => $tags,
                    ])
                );
            }
            if (is_object($model)) {
                static::$identity_map[$model->id] = $model;
                static::$slug_to_id[$slug] = $model->id;
                return $model;
            }
            return null;
        } else {
            if (isset(static::$identity_map[static::$slug_to_id[$slug]])) {
                return static::$identity_map[static::$slug_to_id[$slug]];
            }
            return static::findById(static::$slug_to_id[$slug]);
        }
    }

    /**
     * @return Category|null
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'main_category_id']);
    }

    public function getOptions()
    {
        return $this->hasMany(static::className(), ['parent_id' => 'id']);
    }

    public function getCurrency()
    {
        return $this->hasOne(Currency::className(), ['id' => 'currency_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getRelatedProducts()
    {
        return $this->hasMany(Product::className(), ['id' => 'related_product_id'])
            ->viaTable(
                RelatedProduct::tableName(),
                ['product_id' => 'id'],
                function($relation) {
                    /** @var \yii\db\ActiveQuery $relation */
                    return $relation->orderBy('sort_order ASC');
                }
            )
            ->innerJoin('{{%related_product}}','related_product.product_id=:id AND related_product.related_product_id =product.id',[':id'=>$this->id])
            ->orderBy('related_product.sort_order ASC')
            ->andWhere(["active" => 1]);
    }

    public function getImage()
    {
        $result = $this->hasOne(Image::className(), ['object_model_id' => 'id']);
        $object = Object::getForClass($this->className());
        return $result->andWhere(['object_id' => $object->id]);
    }


    /**
     * Returns remains of this product in all active warehouses.
     * Note that if warehouse was added after product edit - it will not be shown here.
     * @return WarehouseProduct[]
     */
    public function getWarehousesState()
    {
        if ($this->activeWarehousesState === null) {
            $this->activeWarehousesState = WarehouseProduct::getDb()->cache(
                function($db) {
                    return WarehouseProduct::find()
                        ->where(['in', 'warehouse_id', Warehouse::activeWarehousesIds()])
                        ->andWhere('product_id=:product_id', [':product_id'=>$this->id])
                        ->with('warehouse')
                        ->all();
                },
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getObjectTag($this->className(), $this->id),
                        ]
                    ]
                )
            );
        }

        return $this->activeWarehousesState;
    }

    public function beforeSave($insert)
    {
        if (empty($this->breadcrumbs_label)) {
            $this->breadcrumbs_label = $this->name;
        }

        if (empty($this->h1)) {
            $this->h1 = $this->name;
        }

        if (empty($this->title)) {
            $this->title = $this->name;
        }
        $object = Object::getForClass(static::className());

        TagDependency::invalidate(
            Yii::$app->cache,
            [
                'Images:' . $object->id . ':' . $this->id
            ]
        );

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        /**
         * @todo Implement a good decision instead of this.
         * It resolves a problem with Import (We keep data but do not use it. It produce a memory leak).
         * Another decision is do not save data if it does not exist (if (isset(static::$identity_map[$this->id]) === true) {static::$identity_map[$this->id] = $this;}).
         * Both decisions is not good. We think about it all day and all night but have no result. You may offer a true way :)
         */
        unset(static::$identity_map[$this->id]);
    }

    /**
     * Preparation to delete product.
     * Deleting all inserted products.
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        foreach ($this->children as $child) {
            $child->delete();
        }
        return true;
    }

    public function saveCategoriesBindings(array $categories_ids)
    {
        $object = Object::getForClass(static::className());
        $catIds = $this->getCategoryIds();

        $remove = [];
        $add = [];

        foreach ($catIds as $catsKey => $value) {
            $key = array_search($value, $categories_ids);
            if ($key === false) {
                $remove[] = $value;
                unset($this->category_ids[$catsKey]);
            } else {
                unset($categories_ids[$key]);
            }
        }
        foreach ($categories_ids as $value) {
            $add[] = [
                $value,
                $this->id
            ];
            $this->category_ids[] = $value;
        }

        Yii::$app->db->createCommand()->delete(
            $object->categories_table_name,
            ['and', 'object_model_id = :id', ['in', 'category_id', $remove]],
            [':id' => $this->id]
        )->execute();
        if (!empty($add)) {
            Yii::$app->db->createCommand()->batchInsert(
                $object->categories_table_name,
                ['category_id', 'object_model_id'],
                $add
            )->execute();
        }


    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        if ($this->category_ids === null) {
            $object = Object::getForClass(static::className());
            $this->category_ids = (new Query())->select('category_id')->from([$object->categories_table_name])->where(
                'object_model_id = :id',
                [':id' => $this->id]
            )->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->column();
        }
        return $this->category_ids;
    }

    /**
     * Process fields before the actual model is saved(inserted or updated)
     * @param array $fields
     * @param $multipleValuesDelimiter
     * @param array $additionalFields
     */
    public function processImportBeforeSave(array $fields, $multipleValuesDelimiter, array $additionalFields)
    {
        $_attributes = $this->attributes();
        foreach ($fields as $key => $value) {
            if (in_array($key, $_attributes)) {
                $this->$key = $value;
            }
        }

        $categories = $this->unpackCategories($fields, $multipleValuesDelimiter, $additionalFields);
        if ($categories !== false && $this->main_category_id < 1) {
            if (count($categories) == 0) {
                $categories = [1];
            }
            $this->main_category_id = $categories[0];
        }

        if (empty($this->slug)) {
            $this->slug = Helper::createSlug($this->name);
        } elseif (mb_strlen($this->slug) > 80) {
            $this->slug = mb_substr($this->slug, 0, 80);
        }

        if (empty($this->name)) {
            $this->name = 'unnamed-product';
        }

        if (!is_numeric($this->price)) {
            $this->price = 0;
        }
        if (!is_numeric($this->old_price)) {
            $this->old_price = 0;
        }
    }

    /**
     * Process fields after the actual model is saved(inserted or updated)
     * @param array $fields
     * @param $multipleValuesDelimiter
     * @param array $additionalFields
     */
    public function processImportAfterSave(array $fields, $multipleValuesDelimiter, array $additionalFields)
    {
        $categories = $this->unpackCategories($fields, $multipleValuesDelimiter, $additionalFields);

        if ($categories === false) {
            $categories = [$this->main_category_id];
        }
        $this->saveCategoriesBindings($categories);


        $images = isset($fields['images']) ? $fields['images'] : (isset($fields['image']) ? $fields['image'] : false);
        if ($images !== false) {
            if (strpos($images, $multipleValuesDelimiter) > 0) {
                $images = explode($multipleValuesDelimiter, $images);
            } elseif (strpos($multipleValuesDelimiter, '/') === 0) {
                $images = preg_split($multipleValuesDelimiter, $images);
            } else {
                $images = [$images];
            }
            $input_array = [];
            foreach ($images as $image_src) {
                $input_array[] = [
                    'filename' => $image_src,
                ];
            }
            if (count($input_array) > 0) {
                Image::replaceForModel($this, $input_array);
            }
        }

        if (isset($additionalFields['relatedProducts'])) {
            if (isset($additionalFields['relatedProducts']['processValuesAs'],$fields['relatedProducts'])) {

                $ids = explode($multipleValuesDelimiter, $fields['relatedProducts']);
                $this->relatedProductsArray=$ids;
                $this->saveRelatedProducts();
            }
        }
    }

    /**
     * Makes an array of category ids from string
     *
     * @param array $fields
     * @param $multipleValuesDelimiter
     * @param array $additionalFields
     * @return array|bool
     */
    private function unpackCategories(array $fields, $multipleValuesDelimiter, array $additionalFields)
    {
        $categories = isset($fields['categories']) ? $fields['categories'] : (isset($fields['category']) ? $fields['category'] : false);
        if ($categories === false || empty($fields['categories'])) {
            return $this->getCategoryIds();

        }
        if ($categories !== false) {
            if (strpos($categories, $multipleValuesDelimiter) > 0) {
                $categories = explode($multipleValuesDelimiter, $categories);
            } elseif (strpos($multipleValuesDelimiter, '/') === 0) {
                $categories = preg_split($multipleValuesDelimiter, $categories);
            } else {
                $categories = [$categories];
            }
            $typecast = 'id';

            if (isset($additionalFields['categories'])) {
                if (isset($additionalFields['categories']['processValuesAs'])) {
                    $typecast = $additionalFields['categories']['processValuesAs'];
                }
            }
            if ($typecast === 'id') {
                $categories = array_map('intval', $categories);
            } elseif ($typecast === 'slug') {
                $categories = array_map('trim', $categories);
                $categoryIds = [];
                foreach ($categories as $part) {
                    $cat = Category::findBySlug($part, 1, -1);
                    if (is_object($cat)) {
                        $categoryIds[] = $cat->id;
                    }
                    unset($cat);
                }
                $categories = array_map('intval', $categoryIds);
            } elseif ($typecast === 'name') {
                $categories = array_map('trim', $categories);
                $categoryIds = [];
                foreach ($categories as $part) {
                    $cat = Category::findByName($part, 1, -1);
                    if (is_object($cat)) {
                        $categoryIds[] = $cat->id;
                    }
                    unset($cat);
                }
                $categories = array_map('intval', $categoryIds);
            } else {
                // that's unusual behavior
                $categories = false;
            }

            // post-process categories
            // find & add parent category
            // if we need to show products of child categories in products list
            /** @var ShopModule $module */
            $module = Yii::$app->getModule('shop');
            if (is_array($categories) && $module->showProductsOfChildCategories) {
                do {
                    $repeat = false;
                    foreach ($categories as $cat) {
                        $model = Category::findById($cat, null, null);
                        if ($model === null) {
                            echo "\n\nUnknown category with id ".intval($cat) ." for model with id:".$this->id."\n\n";
                            continue;
                        }
                        if (intval($model->parent_id) > 0 && in_array($model->parent_id, $categories) === false) {
                            $categories[] = $model->parent_id;
                            $repeat = true;
                        }

                        unset($model);
                    }
                } while ($repeat === true);
            }

        }
        return $categories;
    }

    /**
     * Additional fields with labels.
     * Translation should be implemented internally in this function.
     * For now will be rendered as checkbox list with label.
     * Note: properties should not be in the result - they are served other way.
     * Format:
     * [
     *      'field_key' => 'Your awesome translated field title',
     *      'another' => 'Another field label',
     * ]
     * @return array
     */
    public static function exportableAdditionalFields()
    {
        return [
            'categories' => [
                'label' => Yii::t('app', 'Categories'),
                'processValueAs' => [
                    'id' => Yii::t('app', 'ID'),
                    'name' => Yii::t('app', 'Name'),
                    'slug' => Yii::t('app', 'Slug'),
                ]
            ],
            'images' => [
                'label' => Yii::t('app', 'Images'),
                'processValueAs' => [
                    'filename' => Yii::t('app', 'Filename'),
                    'id' => Yii::t('app', 'ID'),
                ]
            ],
            'relatedProducts' => [
                'label' => Yii::t('app', 'Related products'),
                'processValueAs' => [
                    'id' => Yii::t('app', 'ID'),
                ],
            ],
        ];
    }

    /**
     * Returns additional fields data by field key.
     * If value of field is array it will be converted to string
     * using multipleValuesDelimiter specified in ImportModel
     * @param array $configuration
     * @return array
     */
    public function getAdditionalFields(array $configuration)
    {
        $result = [];

        if (isset($configuration['categories'], $configuration['categories']['processValuesAs'])
            && $configuration['categories']['enabled']
        ) {
            if ($configuration['categories']['processValuesAs'] === 'id') {
                $result['categories'] = $this->getCategoryIds();
            } else {
                $ids = $this->getCategoryIds();
                $result['categories'] = [];

                foreach ($ids as $id) {
                    $category = Category::findById($id, null, null);
                    if ($category) {
                        $result['categories'][] = $category->getAttribute(
                            $configuration['categories']['processValuesAs']
                        );
                    }
                    unset($category);
                }
            }
        }
        if (isset($configuration['images'], $configuration['images']['processValuesAs'])
            && $configuration['images']['enabled']
        ) {
            $object = Object::getForClass($this->className());
            $images = Image::getForModel($object->id, $this->id);
            $result['images'] = ArrayHelper::getColumn($images, $configuration['images']['processValuesAs']);
        }

        if (isset($configuration['relatedProducts'], $configuration['relatedProducts']['processValuesAs'])
            && $configuration['relatedProducts']['enabled']
        ) {
            $result['relatedProducts'] = ArrayHelper::getColumn($this->relatedProducts, $configuration['relatedProducts']['processValuesAs']);
        }


        return $result;
    }

    /**
     * Returns products for special filtration query
     * Used in ProductsWidget and ProductController
     *
     * @param $category_group_id
     * @param array $values_by_property_id
     * @param null|integer|string $selected_category_id
     * @param bool|string $force_sorting If false - use UserPreferences, if string - use supplied orderBy condition
     * @param null|integer $limit limit query results
     * @param bool $apply_filterquery Should we apply filter query(filters based on query params ie. price_min/max)
     * @param bool $force_limit False to use Pagination, true to use $limit and ignore pagination
     * @param array $additional_filters Array of callables that will apply additional filters to query
     * @return array
     * @throws ServerErrorHttpException
     * @throws \Exception
     */
    public static function filteredProducts(
        $category_group_id,
        array $values_by_property_id = [],
        $selected_category_id = null,
        $force_sorting = false,
        $limit = null,
        $apply_filterquery = true,
        $force_limit = false,
        array $additional_filters = []
    ) {
        Yii::beginProfile("FilteredProducts");
        /** @var \app\models\Object $object */
        if (null === $object = Object::getForClass(static::className())) {
            throw new ServerErrorHttpException('Object not found.');
        }

        /** @var \app\modules\shop\ShopModule $module */
        $module = Yii::$app->getModule('shop');


        $query = static::find()->with('images');
        if ($module->productsFilteringMode === ConfigConfigurationModel::FILTER_PARENTS_ONLY) {
            $query->andWhere([static::tableName() . '.parent_id' => 0]);
        } elseif ($module->productsFilteringMode === ConfigConfigurationModel::FILTER_CHILDREN_ONLY) {
            $query->andWhere(['!=', static::tableName() . '.parent_id', 0]);
        }
        $query->andWhere([static::tableName() . '.active' => 1]);


        if (null !== $selected_category_id) {
            $query->innerJoin(
                $object->categories_table_name . ' ocats',
                'ocats.category_id = :catid AND ocats.object_model_id = ' . static::tableName() . '.id',
                [':catid' => $selected_category_id]
            );
        } else {
            $query->innerJoin(
                $object->categories_table_name . ' ocats',
                'ocats.object_model_id = ' . static::tableName() . '.id'
            );
        }

        $query->innerJoin(
            Category::tableName() . ' ocatt',
            'ocatt.id = ocats.category_id AND ocatt.category_group_id = :gcatid AND ocatt.active = 1',
            [':gcatid' => $category_group_id]
        );
        $query->addGroupBy(static::tableName() . ".id");


        $userSelectedSortingId = UserPreferences::preferences()->getAttributes()['productListingSortId'];
        $allSorts = [];
        if ($force_sorting === false) {
            $allSorts = ProductListingSort::enabledSorts();
            if (isset($allSorts[$userSelectedSortingId])) {
                if ($allSorts[$userSelectedSortingId]['sort_field'] == 'product.price') {
                    $query->leftJoin(Currency::tableName() . ' currency_order ON currency_order.id = product.currency_id');
                    $query->addOrderBy(
                        'product.`price`*currency_order.`convert_rate`' . ' '
                        . $allSorts[$userSelectedSortingId]['asc_desc']
                    );
                } else {
                    $query->addOrderBy(
                        $allSorts[$userSelectedSortingId]['sort_field'] . ' '
                        . $allSorts[$userSelectedSortingId]['asc_desc']
                    );
                }
            } else {
                $query->addOrderBy(static::tableName() . '.sort_order');
            }
        } elseif (empty($force_sorting) === false || is_array($force_sorting) === true) {
            $query->addOrderBy($force_sorting);
        }

        $productsPerPage = $limit === null ? UserPreferences::preferences()->getAttributes(
        )['productsPerPage'] : $limit;

        PropertiesHelper::appendPropertiesFilters(
            $object,
            $query,
            $values_by_property_id,
            Yii::$app->request->get('p', []),
            $module->multiFilterMode
        );


        // apply additional filters
        $cacheKeyAppend = "";
        if ($apply_filterquery) {
            $query = Yii::$app->filterquery->filter($query, $cacheKeyAppend);
        }

        foreach ($additional_filters as $filter) {
            if (is_callable($filter)) {
                call_user_func_array(
                    $filter,
                    [
                        &$query,
                        &$cacheKeyAppend
                    ]
                );
            }
        }

        $cacheKey = 'ProductsCount:' . implode(
                '_',
                [
                    md5($query->createCommand()->getRawSql()),
                    $limit ? '1' : '0',
                    $force_limit ? '1' : '0',
                    $productsPerPage
                ]
            ) . $cacheKeyAppend;


        $pages = null;

        if ($force_limit === true) {
            $query->limit($limit);
        } else {
            $products_query = clone $query;
            $products_query->limit(null);

            if (false === $pages = Yii::$app->cache->get($cacheKey)) {
                $pages = new FilterPagination(
                    [
                        'defaultPageSize' => !is_null($query->limit) ? $query->limit : $productsPerPage,
                        'pageSizeLimit' => [],
                        'forcePageParam' => false,
                        'totalCount' => $products_query->count(),
                    ]
                );

                Yii::$app->cache->set(
                    $cacheKey,
                    $pages,
                    86400,
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(Category::className()),
                                ActiveRecordHelper::getCommonTag(static::className()),
                                ActiveRecordHelper::getCommonTag($module->className()),
                            ]
                        ]
                    )
                );
            }

            $query->offset($pages->offset)->limit($pages->limit);
        }

        $cacheKey .= '-' . Yii::$app->request->get('page', 1);

        if (false === $products = Yii::$app->cache->get($cacheKey)) {
            $products = $query->all();
            Yii::$app->cache->set(
                $cacheKey,
                $products,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(Category::className()),
                            ActiveRecordHelper::getCommonTag(static::className()),
                            ActiveRecordHelper::getCommonTag($module->className()),
                        ]
                    ]
                )
            );
        }

        Yii::endProfile("FilteredProducts");
        return [
            'products' => $products,
            'pages' => $pages,
            'allSorts' => $allSorts,
        ];

    }

    /**
     * Returns product main category model instance using per-request Identity Map
     * @return Category|null
     */
    public function getMainCategory()
    {
        return Category::findById($this->main_category_id, null);
    }

    public function loadRelatedProductsArray()
    {
        $this->relatedProductsArray = [];
        foreach ($this->relatedProducts as $product) {
            $this->relatedProductsArray[] = $product->id;
        }
    }

    public function saveRelatedProducts()
    {
        if (!is_array($this->relatedProductsArray)) {
            $this->relatedProductsArray = explode(',', $this->relatedProductsArray);
        }

        RelatedProduct::deleteAll(
            [
                'product_id' => $this->id,
            ]
        );

        foreach ($this->relatedProductsArray as $index => $relatedProductId) {
            $relation = new RelatedProduct;
            $relation->attributes = [
                'product_id' => $this->id,
                'related_product_id' => $relatedProductId,
                'sort_order' => $index,
            ];
            $relation->save();
        }
    }

    /**
     * Returns product price(old old_price) converted to $currency. If currency is not set(null) will be converted to main currency
     * @param null|Currency $currency Currency to use in conversion, null for main shop currency
     * @param bool $oldPrice True if you want to return Old Price instead of price
     * @return float
     */
    public function convertedPrice($currency = null, $oldPrice = false)
    {
        if ($currency === null) {
            $currency = Currency::getMainCurrency();
        }
        $price = $oldPrice === true ? $this->old_price : $this->price;

        if ($this->currency_id !== $currency->id) {
            // we need to convert!
            $foreignCurrency = Currency::findById($this->currency_id);
            return round($price / $foreignCurrency->convert_nominal * $foreignCurrency->convert_rate, 2);
        }
        return $price;
    }

    /**
     * Formats price to needed currency
     * @param null|Currency $currency Currency to use in conversion, null for main shop currency
     * @param boolean $oldPrice True if you want to return Old Price instead of price
     * @param boolean $schemaOrg Return schema.org http://schema.org/Offer markup with span's
     * @return string
     */
    public function formattedPrice($currency = null, $oldPrice = false, $schemaOrg = true)
    {
        if ($currency === null) {
            $currency = Currency::getMainCurrency();
        }

        $price = $this->convertedPrice($currency, $oldPrice);


        $formatted_string = $currency->format($price);
        if ($schemaOrg == true) {
            return strtr(
                '
                <span itemtype="http://schema.org/Offer" itemprop="offers" itemscope>
                    <meta itemprop="priceCurrency" content="%iso_code%">
                    <span itemprop="price" content="%price%">
                        %formatted_string%
                    </span>
                </span>
                ',
                [
                    '%iso_code%' => $currency->iso_code,
                    '%price%' => $price,
                    '%formatted_string%' => $formatted_string,
                ]
            );
        } else {
            return $formatted_string;
        }

    }

    /**
     * Formats price in product's currency
     * @param bool $oldPrice
     * @param bool $schemaOrg
     * @return string
     */
    public function nativeCurrencyPrice($oldPrice = false, $schemaOrg = true)
    {
        $currency = Currency::findById($this->currency_id);
        return $this->formattedPrice($currency, $oldPrice, $schemaOrg);
    }

    public function getMeasure()
    {
        $measure = Measure::findById($this->measure_id);
        if (is_null($measure)) {
            $measure = Measure::findById(Yii::$app->getModule('shop')->defaultMeasureId);
        }
        if (is_null($measure)) {
            throw new Exception('Measure not found');
        }
        return $measure;
    }

    /**
     * @param int|Category|null $category
     * @param bool $asMainCategory
     * @return bool
     * @throws \yii\db\Exception
     */
    public function linkToCategory($category = null, $asMainCategory = false)
    {
        if ($category instanceof Category) {
            $category = $category->id;
        } elseif (is_int($category) || is_string($category)) {
            $category = intval($category);
        } else {
            return false;
        }

        if ($asMainCategory) {
            $this->main_category_id = $category;
            return $this->save();
        } else {
            $tableName = $this->getObject()->categories_table_name;
            $query = new Query();
            $query = $query->select(['id'])
                ->from($tableName)
                ->where(['category_id' => $category, 'object_model_id' => $this->id])
                ->one();
            if (false === $query) {
                try {
                    $result = Yii::$app->db->createCommand()->insert($tableName, [
                        'category_id' => $category,
                        'object_model_id' => $this->id
                    ])->execute();
                } catch (\yii\db\Exception $e) {
                    $result = false;
                }
                return boolval($result);
            }
        }

        return false;
    }

    public function getCacheTags()
    {

        $tags = [
            ActiveRecordHelper::getObjectTag(self::className(), $this->id),
        ];
        $category = $this->getMainCategory();
        $tags [] = ActiveRecordHelper::getObjectTag(Category::className(), $category->id);
        $categoryParentsIds = $category->getParentIds();
        foreach ($categoryParentsIds as $id) {
            $tags [] = ActiveRecordHelper::getObjectTag(Category::className(), $id);
        };


        return $tags;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ($this->className() . ':' . $this->id);
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return ($this->className() . ':' . $this->id);
    }
}
