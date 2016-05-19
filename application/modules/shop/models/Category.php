<?php

namespace app\modules\shop\models;

use app\behaviors\CleanRelations;
use app\behaviors\Tree;
use app\components\Helper;
use app\models\Object;
use app\modules\shop\models\FilterSets;
use app\properties\HasProperties;
use app\traits\GetImages;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property integer $category_group_id
 * @property integer $parent_id
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
 * @property boolean $active
 * @property string $date_added
 * @property string $date_modified
 * @property Category[] $children
 * @property Category $parent
 */
class Category extends ActiveRecord implements \JsonSerializable
{
    use GetImages;

    const DELETE_MODE_SINGLE_CATEGORY = 1;
    const DELETE_MODE_ALL = 2;
    const DELETE_MODE_MAIN_CATEGORY = 3;
    const CATEGORY_PARENT = 0;
    const CATEGORY_ACTIVE = 1;
    const CATEGORY_INACTIVE = 0;

    /**
     * Category identity map
     * [
     *      'category_id' => $category_model_instance,
     * ]
     *
     * Used by findById
     * @var array
     */
    public static $identity_map = [];

    /**
     * Special caching for findBySlug
     * Stores category->id for pair slug:category_group_id:parent_id
     * @var array
     */
    private static $id_by_slug_group_parent = [];
    private static $id_by_name_group_parent = [];

    public $deleteMode = 1;

    /**
     * @var null|string Url path caching variable
     */
    private $urlPath = null;

    /** @var null|array Array of parent categories ids including root category */
    private $parentIds = null;

    /** @var FilterSets[] */
    private $filterSets = null;

    public function behaviors()
    {
        return [
            [
                'class' => HasProperties::className(),
            ],
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
            [
                'class' => CleanRelations::className(),
            ],
            [
                'class' => Tree::className(),
                'activeAttribute' => 'active',
                'sortOrder' => [
                    'sort_order' => SORT_ASC,
                    'id' => SORT_ASC
                ],
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
     * Return delete modes list
     * @return array
     */
    public static function deleteModesList()
    {
        return [
            self::DELETE_MODE_MAIN_CATEGORY => Yii::t('app', 'Delete all products that relate to this category as main'),
            self::DELETE_MODE_SINGLE_CATEGORY => Yii::t('app', 'Delete only that products that exists ONLY in that category'),
            self::DELETE_MODE_ALL => Yii::t('app', 'Delete along with it no matter what'),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_group_id', 'parent_id', 'name', 'slug'], 'required'],
            [['category_group_id', 'parent_id', 'slug_absolute', 'sort_order', 'active'], 'integer'],
            [['name', 'title', 'h1', 'meta_description', 'breadcrumbs_label', 'content', 'announce'], 'string'],
            [['slug'], 'string', 'max' => 80],
            [['slug_compiled'], 'string', 'max' => 180],
            [['title_append'], 'string'],
            [['active'], 'default', 'value' => 1],
            [['date_added', 'date_modified'], 'safe'],
            ['sort_order', 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        if (empty($this->slug) && !empty($this->name)) {
            $this->slug = Helper::createSlug($this->name);
        }
        if (empty($this->title) && !empty($this->name)) {
            $this->title = $this->name;
        }
        if (empty($this->h1) && !empty($this->title)) {
            $this->h1 = $this->title;
        }
        if (empty($this->breadcrumbs_label) && !empty($this->name)) {
            $this->breadcrumbs_label = $this->name;
        }
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'category_group_id' => Yii::t('app', 'Category Group ID'),
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
            'title_append' => Yii::t('app', 'Title Append'),
            'date_added' => Yii::t('app', 'Date Added'),
            'date_modified' => Yii::t('app', 'Date Modified'),
        ];
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find()->where(['parent_id' => $this->parent_id])->with('images');
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
        $query->andFilterWhere(['like', 'slug_compiled', $this->slug_compiled]);
        $query->andFilterWhere(['like', 'slug_absolute', $this->slug_absolute]);
        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['like', 'h1', $this->h1]);
        $query->andFilterWhere(['like', 'meta_description', $this->meta_description]);
        $query->andFilterWhere(['like', 'breadcrumbs_label', $this->breadcrumbs_label]);
        $query->andFilterWhere(['active' => $this->active]);
        return $dataProvider;
    }

    /**
     * Returns model using indentity map and cache
     * @param int $id
     * @param int|null $isActive
     * @return Category|null
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
                    Yii::$app->cache->set(
                        $cacheKey,
                        $model,
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    ActiveRecordHelper::getObjectTag($model, $model->id)
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
     * Finds category by slug inside category_group_id
     * Uses cache and identity_map
     */
    public static function findBySlug($slug, $category_group_id, $parent_id = 0)
    {
        $params = [
            'slug' => $slug,
            'category_group_id' => $category_group_id,
        ];
        if ($parent_id >= 0) {
            $params['parent_id'] = $parent_id;
        }

        $identity_key = $slug . ':' . $category_group_id . ':' . $parent_id;
        if (isset(static::$id_by_slug_group_parent[$identity_key]) === true) {
            return static::findById(static::$id_by_slug_group_parent[$identity_key], null);
        }
        $category = Yii::$app->cache->get("Category:bySlug:" . $identity_key);
        if ($category === false) {
            $category = Category::find()->where($params)->one();
            if (is_object($category) === true) {
                static::$identity_map[$category->id] = $category;
                static::$id_by_slug_group_parent[$identity_key] = $category->id;
                Yii::$app->cache->set(
                    "Category:bySlug:" . $identity_key,
                    $category,
                    86400,
                    new TagDependency(
                        [
                            'tags' => ActiveRecordHelper::getObjectTag($category, $category->id)
                        ]
                    )
                );
                return $category;
            } else {
                Yii::$app->cache->set(
                    "Category:bySlug:" . $identity_key,
                    $category,
                    86400,
                    new TagDependency(
                        [
                            'tags' => ActiveRecordHelper::getCommonTag(Category::className())
                        ]
                    )
                );
                return null;
            }
        } else {
            return $category;
        }
    }

    /**
     * Ищет по name в рамках category_group_id
     * Заносит в identity_map
     */
    public static function findByName($name, $category_group_id, $parent_id = 0)
    {
        $params = [
            'name' => $name,
            'category_group_id' => $category_group_id,
        ];
        if ($parent_id >= 0) {
            $params['parent_id'] = $parent_id;
        }

        $identity_key = $name . ':' . $category_group_id . ':' . $parent_id;
        if (isset(static::$id_by_name_group_parent[$identity_key])) {
            return static::findById(static::$id_by_name_group_parent[$identity_key], null);
        }
        $category = Yii::$app->cache->get("Category:byName:" . $identity_key);
        if (!is_object($category)) {
            $category = Category::find()->where($params)->one();
            if (is_object($category)) {
                static::$identity_map[$category->id] = $category;
                static::$id_by_name_group_parent[$identity_key] = $category->id;
                Yii::$app->cache->set(
                    "Category:byName:" . $identity_key,
                    $category,
                    86400,
                    new TagDependency(
                        [
                            'tags' => ActiveRecordHelper::getObjectTag($category, $category->id)
                        ]
                    )
                );
                return $category;
            } else {
                return null;
            }
        } else {
            return $category;
        }
    }

    public function getUrlPath($include_parent_category = true)
    {
        if ($this->urlPath === null) {
            if ($this->parent_id == 0) {
                if ($include_parent_category) {
                    return $this->slug;
                } else {
                    return '';
                }
            }
            $slugs = [$this->slug];

            $this->parentIds = [];

            $parent_category = $this->parent_id > 0 ? $this->parent : 0;
            while ($parent_category !== null) {
                $this->parentIds[] = intval($parent_category->id);
                $slugs[] = $parent_category->slug;
                $parent_category = $parent_category->parent;
            }
            if ($include_parent_category === false) {
                array_pop($slugs);
            }
            $this->urlPath = implode("/", array_reverse($slugs));
        }
        return $this->urlPath;
    }

    /**
     * @return array Returns array of ids of parent categories(breadcrumbs ids)
     */
    public function getParentIds()
    {
        if ($this->parentIds === null) {
            $this->parentIds = [];
            $parent_category = $this->parent_id > 0 ? $this->parent : null;
            while ($parent_category !== null) {
                $this->parentIds[] = intval($parent_category->id);
                $parent_category = $parent_category->parent;
            }
        }
        return $this->parentIds;
    }

    /**
     * Ищет root для группы категорий
     * Использует identity_map
     * @return Category|null
     */
    public static function findRootForCategoryGroup($id = null)
    {
        if (null === $id) {
            return null;
        }

        return static::find()
            ->where([
                'category_group_id' => $id,
                'parent_id' => static::CATEGORY_PARENT,
                'active' => static::CATEGORY_ACTIVE,
            ])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->one();
    }

    /**
     * @deprecated
     * @param $category_group_id
     * @param int $level
     * @param int $is_active
     * @return Category[]
     */
    public static function getByLevel($category_group_id, $level = 0, $is_active = 1)
    {
        $cacheKey = "CategoriesByLevel:$category_group_id:$level";
        if (false === $models = Yii::$app->cache->get($cacheKey)) {
            $models = Category::find()->where(
                ['category_group_id' => $category_group_id, 'parent_id' => $level, 'active' => $is_active]
            )->orderBy('sort_order')->with('images')->all();

            if (null !== $models) {

                $cache_tags = [];
                foreach ($models as $model) {
                    $cache_tags [] = ActiveRecordHelper::getObjectTag($model, $model->id);
                }
                $cache_tags [] = ActiveRecordHelper::getObjectTag(static::className(), $level);


                Yii::$app->cache->set(
                    $cacheKey,
                    $models,
                    86400,
                    new TagDependency(
                        [
                            'tags' => $cache_tags
                        ]
                    )
                );
            }
        }
        foreach ($models as $model) {
            static::$identity_map[$model->id] = $model;
        }
        return $models;
    }

    /**
     * @param int $parentId
     * @param int|null $isActive
     * @return Category[]|array
     */
    public static function getByParentId($parentId = null, $isActive = 1)
    {
        if (null === $parentId) {
            return [];
        }
        $parentId = intval($parentId);
        $cacheKey = "CategoriesByParentId:$parentId" . ':' . (null === $isActive ? 'null' : $isActive);
        if (false !== $models = Yii::$app->cache->get($cacheKey)) {
            return $models;
        }
        $query = static::find()
            ->where(['parent_id' => $parentId]);
        if (null !== $isActive) {
            $query->andWhere(['active' => $isActive]);
        }
        $models = $query->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->with('images')
            ->all();

        if (empty($models)) {
            return [];
        }
        Yii::$app->cache->set(
            $cacheKey,
            $models,
            0,
            new TagDependency([
                'tags' => array_reduce($models,
                    function ($result, $item)
                    {
                        /** @var Category $item */
                        $result[] = ActiveRecordHelper::getObjectTag(static::className(), $item->id);
                        return $result;
                    },
                    [ActiveRecordHelper::getObjectTag(static::className(), $parentId)]
                )
            ])
        );
        return $models;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        static::$identity_map[$this->id] = $this;

        if (isset($changedAttributes['category_group_id'])) {
            foreach ($this->children as $child) {
                $child->category_group_id = $this->category_group_id;
                $child->save(true, ['category_group_id']);
            }
        }

        if (isset($changedAttributes['parent_id'])) {
            if (is_null($this->parent) === false) {
                $this->category_group_id = $this->parent->category_group_id;
                $this->save(true, ['category_group_id']);
            }
        }
    }

    /**
     * Preparation to delete category.
     * Deleting related products and inserted categories.
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        $productObject = Object::getForClass(Product::className());
        switch ($this->deleteMode) {
            case self::DELETE_MODE_ALL:
                $products =
                    !is_null($productObject)
                        ? Product::find()
                        ->join(
                            'INNER JOIN',
                            $productObject->categories_table_name . ' pc',
                            'pc.object_model_id = product.id'
                        )
                        ->where('pc.category_id = :id', [':id' => $this->id])
                        ->all()
                        : [];
                break;
            case self::DELETE_MODE_MAIN_CATEGORY:
                $products = Product::findAll(['main_category_id' => $this->id]);
                break;
            default:
                $products =
                    !is_null($productObject)
                        ? Product::find()
                        ->join(
                            'INNER JOIN',
                            $productObject->categories_table_name . ' pc',
                            'pc.object_model_id = product.id'
                        )
                        ->join(
                            'INNER JOIN',
                            $productObject->categories_table_name . ' pc2',
                            'pc2.object_model_id = product.id'
                        )
                        ->where('pc.category_id = :id', [':id' => $this->id])
                        ->groupBy('pc2.object_model_id')
                        ->having('COUNT(*) = 1')
                        ->all()
                        : [];
                break;
        }
        foreach ($products as $product) {
            $product->delete();
        }
        foreach ($this->children as $child) {
            $child->deleteMode = $this->deleteMode;
            $child->delete();
        }
        if (!is_null($productObject)) {
            Yii::$app->db
                ->createCommand()
                ->delete($productObject->categories_table_name, ['category_id' => $this->id])
                ->execute();
        }
        return true;
    }

    public function afterDelete()
    {
        FilterSets::deleteAll(['category_id' => $this->id]);
        parent::afterDelete();
    }


    /**
     * Get children menu items with selected depth
     * @param int $parentId
     * @param null|integer $depth
     * @return array
     */
    public static function getMenuItems($parentId = 0, $depth = null, $fetchModels = false)
    {
        if ($depth === 0) {
            return [];
        }
        $cacheKey = 'CategoryMenuItems:' . implode(':', [
            $parentId,
            null === $depth ? 'null' : intval($depth),
            intval($fetchModels)
        ]) ;
        $items = Yii::$app->cache->get($cacheKey);
        if ($items !== false) {
            return $items;
        }
        $items = [];
        $categories = static::find()->select(['id', 'name', 'category_group_id'])->where(
            ['parent_id' => $parentId, 'active' => 1]
        )->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])->with('images')->all();
        $cache_tags = [
            ActiveRecordHelper::getCommonTag(static::className()),
        ];

        /** @var Category $category */
        foreach ($categories as $category) {
            $items[] = [
                'label' => $category->name,
                'url' => Url::toRoute(
                    [
                        '@category',
                        'category_group_id' => $category->category_group_id,
                        'last_category_id' => $category->id,
                    ]
                ),
                'id' => $category->id,
                'model' => $fetchModels ? $category : null,
                'items' => static::getMenuItems($category->id, null === $depth ? null : $depth - 1),
            ];
            $cache_tags[] = ActiveRecordHelper::getObjectTag(static::className(), $category->id);
        }
        $cache_tags [] = ActiveRecordHelper::getObjectTag(static::className(), $parentId);
        Yii::$app->cache->set(
            $cacheKey,
            $items,
            86400,
            new TagDependency(
                [
                    'tags' => $cache_tags,
                ]
            )
        );
        return $items;
    }

    /**
     * @return FilterSets[]
     */
    public function filterSets()
    {
        if ($this->filterSets === null) {
            $this->filterSets = FilterSets::getForCategoryId($this->id);
        }
        return $this->filterSets;
    }

    /**
     * @param int $parentId
     * @param int|null $categoryGroupId
     * @param string $name
     * @param bool $dummyObject
     * @return Category|null
     */
    public static function createEmptyCategory($parentId = 0, $categoryGroupId = null, $name = 'Catalog', $dummyObject = false)
    {
        $categoryGroupId = null === $categoryGroupId ? CategoryGroup::getFirstModel()->id : intval($categoryGroupId);

        $model = new static();
        $model->loadDefaultValues();
        $model->parent_id = $parentId;
        $model->category_group_id = $categoryGroupId;
        $model->h1 = $model->title = $model->name = $name;
        $model->slug = Helper::createSlug($model->name);

        if (!$dummyObject) {
            if (!$model->save()) {
                return null;
            }
            $model->refresh();
        }

        return $model;
    }

    /**
     * @param int|Product|null $product
     * @param bool $asMainCategory
     * @return bool
     */
    public function linkProduct($product = null, $asMainCategory = false)
    {
        if ($product instanceof Product) {
            return $product->linkToCategory($this->id, $asMainCategory);
        } elseif (is_int($product) || is_string($product)) {
            $product = intval($product);
            if (null !== $product = Product::findById($product, null)) {
                return $product->linkToCategory($this->id, $asMainCategory);
            }
        }

        return false;
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
