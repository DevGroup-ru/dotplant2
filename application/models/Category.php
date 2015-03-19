<?php

namespace app\models;

use app\behaviors\CleanRelations;
use app\behaviors\Tree;
use app\properties\HasProperties;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
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
 * @property integer $is_deleted
 * @property boolean $active
 * @property Category[] $children
 * @property Category $parent
 */
class Category extends ActiveRecord
{
    /**
     * Category identity map
     * [
     *      'category_id' => $category_model_instance,
     * ]
     *
     * Used by findById
     * @var array
     */
    private static $identity_map = [];

    /**
     * Special caching for findBySlug
     * Stores category->id for pair slug:category_group_id:parent_id
     * @var array
     */
    private static $id_by_slug_group_parent = [];
    private static $id_by_name_group_parent = [];

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
            ],
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
            [['category_group_id', 'parent_id', 'slug_absolute', 'sort_order', 'is_deleted', 'active'], 'integer'],
            [['name', 'title', 'h1', 'meta_description', 'breadcrumbs_label', 'content', 'announce'], 'string'],
            [['slug'], 'string', 'max' => 80],
            [['slug_compiled'], 'string', 'max' => 180],
            [['title_append'], 'string'],
            [['active'], 'default', 'value' => 1],
        ];
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
            'is_deleted' => Yii::t('app', 'Is Deleted'),
            'active' => Yii::t('app', 'Active'),
            'title_append' => Yii::t('app', 'Title Append'),
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
        $query = self::find()
            ->where(['parent_id'=>$this->parent_id]);
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
     * @param string $id
     * @param int|null $is_active
     * @param int|null $is_deleted
     * @return Category|null
     */
    public static function findById($id, $is_active = 1, $is_deleted = 0)
    {
        if (!isset(static::$identity_map[$id])) {
            $cacheKey = static::tableName().":$id";
            if (false === $model = Yii::$app->cache->get($cacheKey)) {
                $model = static::find()->where(['id' => $id]);
                if (null !== $is_active) {
                    $model->andWhere(['active' => $is_active]);
                }
                if (null !== $is_deleted) {
                    $model->andWhere(['is_deleted' => $is_deleted]);
                }
                if (null !== $model = $model->one()) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        $model,
                        86400,
                        new TagDependency([
                            'tags' => [
                                ActiveRecordHelper::getObjectTag($model, $model->id)
                            ]
                        ])
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
            'slug'=>$slug,
            'category_group_id' => $category_group_id,
        ];
        if ($parent_id >= 0) {
            $params['parent_id'] = $parent_id;
        }

        $identity_key = $slug . ':' . $category_group_id . ':' . $parent_id;
        if (isset(static::$id_by_slug_group_parent[$identity_key]) === true) {
            return static::findById(static::$id_by_slug_group_parent[$identity_key], null, null);
        }
        $category = Yii::$app->cache->get("Category:bySlug:".$identity_key);
        if ($category === false) {
            $category = Category::find()->where($params)->one();
            if (is_object($category) === true) {
                static::$identity_map[$category->id] = $category;
                static::$id_by_slug_group_parent[$identity_key] = $category->id;
                Yii::$app->cache->set(
                    "Category:bySlug:".$identity_key,
                    $category,
                    86400,
                    new TagDependency([
                        'tags' => ActiveRecordHelper::getObjectTag($category, $category->id)
                    ])
                );
                return $category;
            } else {
                Yii::$app->cache->set(
                    "Category:bySlug:".$identity_key,
                    $category,
                    86400,
                    new TagDependency([
                        'tags' => ActiveRecordHelper::getCommonTag(Category::className())
                    ])
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
            'name'=>$name,
            'category_group_id' => $category_group_id,
        ];
        if ($parent_id >= 0) {
            $params['parent_id'] = $parent_id;
        }

        $identity_key = $name . ':' . $category_group_id . ':' . $parent_id;
        if (isset(static::$id_by_name_group_parent[$identity_key])) {
            return static::findById(static::$id_by_name_group_parent[$identity_key], null, null);
        }
        $category = Yii::$app->cache->get("Category:byName:".$identity_key);
        if (!is_object($category)) {
            $category = Category::find()->where($params)->one();
            if (is_object($category)) {
                static::$identity_map[$category->id] = $category;
                static::$id_by_name_group_parent[$identity_key] = $category->id;
                Yii::$app->cache->set(
                    "Category:byName:".$identity_key,
                    $category,
                    86400,
                    new TagDependency([
                        'tags' => ActiveRecordHelper::getObjectTag($category, $category->id)
                    ])
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
        if ($this->parent_id == 0) {
            if ($include_parent_category) {
                return $this->slug;
            } else {
                return '';
            }
        }
        $slugs = [$this->slug];
        $parent_category = $this->parent_id > 0 ? $this->parent : 0;
        while ($parent_category !== null) {
            $slugs[] = $parent_category->slug;
            $parent_category = $parent_category->parent;
        }
        if ($include_parent_category === false) {
            array_pop($slugs);
        }
        return implode("/", array_reverse($slugs));
    }

    public function getIdPath($include_parent_category = true)
    {
        if ($this->parent_id == 0) {
            if ($include_parent_category) {
                return $this->id;
            } else {
                return '';
            }
        }
        $ids = [$this->id];
        $parent_category = $this->parent;
        while ($parent_category !== null) {
            $ids[] = $parent_category->id;
            $parent_category = $parent_category->parent;
        }
        if ($include_parent_category === false) {
            array_pop($ids);
        }
        return array_reverse($ids);
    }

    /**
     * Ищет root для группы категорий
     * Использует identity_map
     */
    public static function findRootForCategoryGroup($category_group_id)
    {
        $models = static::getByLevel($category_group_id, 0);

        if (is_array($models)) {
            return array_shift($models);
        }
        return null;
    }

    public static function getByLevel($category_group_id, $level = 0, $is_active = 1)
    {
        $cacheKey = "CategoriesByLevel:$category_group_id:$level";
        if (false === $models = Yii::$app->cache->get($cacheKey)) {
            $models = Category::find()
                ->where(['category_group_id' => $category_group_id, 'parent_id' => $level, 'active' => $is_active])
                ->orderBy('sort_order')
                ->all();

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
                    new TagDependency([
                        'tags' => $cache_tags
                    ])
                );
            }
        }
        foreach ($models as $model) {
            static::$identity_map[$model->id] = $model;
        }
        return $models;
    }

    public static function getByParentId($parent_id, $is_active = 1)
    {
        $cacheKey = "CategoriesByParentId:$parent_id";
        if (false === $models = Yii::$app->cache->get($cacheKey)) {
            $models = static::find()
                ->where(['parent_id' => $parent_id, 'active' => $is_active])
                ->orderBy('sort_order')
                ->all();
            if (null !== $models) {
                $cache_tags = [];
                foreach ($models as $model) {
                    $cache_tags [] = ActiveRecordHelper::getObjectTag($model, $model->id);
                }
                $cache_tags [] = ActiveRecordHelper::getObjectTag(static::className(), $parent_id);

                Yii::$app->cache->set(
                    $cacheKey,
                    $models,
                    86400,
                    new TagDependency([
                        'tags' => $cache_tags
                    ])
                );
            }
        }
        foreach ($models as $model) {
            static::$identity_map[$model->id] = $model;
        }
        return $models;
    }

    public function beforeSave($insert)
    {
        if (1 === $this->is_deleted) {
            $this->active = 0;
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (isset($changedAttributes['category_group_id'])) {
            foreach ($this->children as $child) {
                $child->category_group_id = $this->category_group_id;
                $child->save(true, ['category_group_id']);
            }
        }
    }

    /**
     * "Мягкое" удаление категории, включая все вложенные категории и продукты
     * Повторный вызов удаляет из БД
     * @return bool
     */
    public function beforeDelete()
    {
        if (null !== $children = static::find()->where(['parent_id' => $this->id])->all()) {
            foreach ($children as $child) {
                $child->delete();
            }
        }
        if (null !== $products = Product::find()->where(['main_category_id' => $this->id])->all()) {
            foreach ($products as $product) {
                $product->delete();
            }
        }
        $result = parent::beforeDelete();
        if (0 === intval($this->is_deleted)) {
            $this->is_deleted = 1;
            $this->save();

            return false;
        }
        return $result;
    }

    /**
     * Отменяет "мягкое" удаление; так-же откатывает все вложенные категории и продукты
     */
    public function restoreFromTrash()
    {
        $this->is_deleted = 0;
        $this->save();
        if (null !== $children = static::find()->where(['parent_id' => $this->id])->all()) {
            foreach ($children as $child) {
                $child->restoreFromTrash();
            }
        }
        if (null !== $products = Product::find()->where(['main_category_id' => $this->id])->all()) {
            foreach ($products as $product) {
                $product->restoreFromTrash();
            }
        }

    }

    /**
     * Get children menu items with selected depth
     * @param int $parentId
     * @param null|integer $depth
     * @return array
     */
    public static function getMenuItems($parentId = 0, $depth = null)
    {
        if ($depth === 0) {
            return [];
        }
        $cacheKey = 'CategoryMenuItems:' . $parentId . ':' . $depth;
        $items = Yii::$app->cache->get($cacheKey);
        if ($items !== false) {
            return $items;
        }
        $items = [];
        $categories = static::find()
            ->select(['id', 'name', 'category_group_id'])
            ->where(['parent_id' => $parentId, 'active' => 1])
            ->orderBy('sort_order')
            ->all();
        $cache_tags = [];

        foreach ($categories as $category) {
            $items[] = [
                'label' => $category->name,
                'url' => Url::to(
                    [
                        'product/list',
                        'category_group_id' => $category->category_group_id,
                        'last_category_id' => $category->id,
                    ]
                ),
                'items' => static::getMenuItems($category->id, is_null($depth) ? null : $depth - 1),
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
}
