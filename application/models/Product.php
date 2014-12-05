<?php

namespace app\models;

use app\behaviors\CleanRelations;
use app\behaviors\Tree;
use app\data\components\ImportableInterface;
use app\data\components\ExportableInterface;
use app\properties\HasProperties;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;

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
 * @property integer $is_deleted
 * @property integer $parent_id
 */
class Product extends ActiveRecord implements ImportableInterface, ExportableInterface
{
    private static $identity_map = [];
    private static $slug_to_id = [];
    private $category_ids = null;

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
            [['main_category_id', 'name', 'slug'], 'required'],
            [['main_category_id', 'slug_absolute', 'sort_order', 'active', 'parent_id', 'is_deleted'], 'integer'],
            [
                [
                    'name',
                    'title',
                    'h1',
                    'meta_description',
                    'breadcrumbs_label',
                    'content',
                    'announce',
                    'option_generate'
                ],
                'string'
            ],
            [['price', 'old_price'], 'number'],
            [['slug'], 'string', 'max' => 80],
            [['slug_compiled'], 'string', 'max' => 180],
            [['old_price', 'price'], 'default', 'value' => 0,],
            [['active'], 'default', 'value'=>1],

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
            'is_deleted' => Yii::t('app', 'Is Deleted'),
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => Tree::className(),
            ],
            [
                'class' => HasProperties::className(),
            ],
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
            [
                'class' => CleanRelations::className(),
            ],
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
            ->where(['parent_id' => 0]);
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
        $query->andFilterWhere(['is_deleted' => $this->is_deleted]);
        return $dataProvider;
    }

    public static function findById($id, $is_active = 1, $is_deleted = 0)
    {
        if (!isset(static::$identity_map[$id])) {
            $cacheKey = static::tableName() . ":$id";
            if (false === $model = Yii::$app->cache->get($cacheKey)) {
                $model = static::find()->where(['id' => $id]);
                if (null !== $is_active) {
                    $model->andWhere(['active' => $is_active]);
                }
                if (null !== $is_deleted) {
                    $model->andWhere(['is_deleted' => $is_deleted]);
                }
                if (null !== $model = $model->one()) {
                    static::$slug_to_id[$model->slug] = $id;
                    Yii::$app->cache->set(
                        $cacheKey,
                        $model,
                        86400,
                        new TagDependency([
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                            ]
                        ])
                    );
                }
            }
            static::$identity_map[$id] = $model;
        }
        return static::$identity_map[$id];
    }

    public static function findBySlug($slug, $in_category_id = null, $is_active = 1)
    {
        if (!isset(static::$slug_to_id[$slug])) {
            $cacheKey = static::tableName() . "$slug:$in_category_id";
            if (false === $model = Yii::$app->cache->get($cacheKey)) {
                $model = static::find()->where([
                        static::tableName() . '.slug' => $slug,
                        static::tableName() . '.active' => $is_active
                    ]);
                if ($in_category_id !== null) {
                    $model = $model->innerJoin(
                        Object::getForClass(Product::className())->categories_table_name . ' ocats',
                        'ocats.category_id = ' . Yii::$app->db->quoteValue($in_category_id) .
                        ' AND ocats.object_model_id = ' . static::tableName() . '.id'
                    );
                }
                if (null !== $model = $model->one()) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        $model,
                        86400,
                        new TagDependency([
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                            ]
                        ])
                    );
                }
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

    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'main_category_id']);
    }

    public function getOptions()
    {
        return $this->hasMany(Product::className(), ['parent_id' => 'id']);
    }

    public function beforeSave($insert)
    {
        if (1 === $this->is_deleted) {
            $this->active = 0;
        }

        if (empty($this->breadcrumbs_label)) {
            $this->breadcrumbs_label = $this->name;
        }

        if (empty($this->h1)) {
            $this->h1 = $this->name;
        }

        if (empty($this->title)) {
            $this->title = $this->name;
        }
        
        return parent::beforeSave($insert);
    }

    /**
     * Первое удаление в корзину, второе из БД
     *
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        $children = $this->children;
        if (!empty($children)) {
            foreach ($children as $child) {
                $child->delete();
            }
        }
        if ($this->is_deleted == 0) {
            $this->is_deleted = 1;
            $this->save(true, ['is_deleted']);
            return false;
        }
        return true;
    }

    /**
     * Отмена удаления объекта
     *
     * @return bool Restore result
     */
    public function restoreFromTrash()
    {
        $this->is_deleted = 0;
        return $this->save();
    }

    public function saveCategoriesBindings(array $categories_ids)
    {
        $object = Object::getForClass(Product::className());

        $catIds = $this->getCategoryIds();


        $remove = [];
        $add = [];

        foreach ($catIds as $value) {
            $key = array_search($value, $categories_ids);
            if ($key === false) {
                $remove[] = $value;
            } else {
                unset($categories_ids[$key]);
            }
        }
        foreach ($categories_ids as $value) {
            $add[] = [
                $value,
                $this->id
            ];
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

    public function getCategoryIds()
    {
        if ($this->category_ids === null) {
            $object = Object::getForClass(Product::className());
            $this->category_ids = (new Query())->select('category_id')
                ->from([$object->categories_table_name])
                ->where('object_model_id = :id', [':id' => $this->id])
                ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
                ->column();
        }
        return $this->category_ids;
    }

    /**
     * Process fields before the actual model is saved(inserted or updated)
     * @param array $fields
     * @return void
     */
    public function processImportBeforeSave(array $fields, $multipleValuesDelimiter)
    {

        $categories = $this->unpackCategories($fields, $multipleValuesDelimiter);
        if ($categories !== false && $this->main_category_id < 1) {
            $this->main_category_id = $categories[0];
        }

        if (empty($this->slug)) {
            $this->slug = 'unslugged-product';
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
     * @return void
     */
    public function processImportAfterSave(array $fields, $multipleValuesDelimiter)
    {
        $categories = $this->unpackCategories($fields, $multipleValuesDelimiter);

        if ($categories !== false) {

            $this->saveCategoriesBindings($categories);
        }
    }

    private function unpackCategories(array $fields, $multipleValuesDelimiter)
    {
        $categories =
            isset($fields['categories']) ? $fields['categories'] :
                (isset($fields['category']) ? $fields['category'] :
                    false);
        if ($categories !== false) {
            if (strpos($categories, $multipleValuesDelimiter) > 0) {
                $categories = explode($multipleValuesDelimiter, $categories);
            } elseif (strpos($multipleValuesDelimiter, '/') === 0) {
                $categories = preg_split($multipleValuesDelimiter, $categories);
            } else {
                $categories = [$categories];
            }

            $categories = array_map('intval', $categories);
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
                ]
            ],
            'images' => [
                'label' => Yii::t('app', 'Images'),
                'processValueAs' => [
                    'id' => Yii::t('app', 'ID'),
                    'image_src' => Yii::t('app', 'Filename'),
                ]
            ],
        ];
    }

    /**
     * Returns additional fields data by field key.
     * If value of field is array it will be converted to string
     * using multipleValuesDelimiter specified in ImportModel
     * @return array
     */
    public function getAdditionalFields(array $configuration)
    {
        $result = [];

        if (isset($configuration['categories'], $configuration['categories']['processValuesAs']) && $configuration['categories']['enabled']) {
            if ($configuration['categories']['processValuesAs'] === 'id') {
                $result['categories'] = $this->getCategoryIds();
            } else {
                $ids = $this->getCategoryIds();
                $result['categories'] = [];

                foreach ($ids as $id) {
                    $category = Category::findById($id);
                    if ($category) {
                        $result['categories'][] = $category->getAttribute($configuration['categories']['processValuesAs']);
                    }
                    unset($category);
                }
            }
        }
        if (isset($configuration['images'], $configuration['images']['processValuesAs']) && $configuration['images']['enabled']) {
            $object = Object::getForClass($this->className());
            $images = Image::getForModel($object->id, $this->id);
            $result['images'] = ArrayHelper::getColumn($images, $configuration['images']['processValuesAs']);
        }


        return $result;
    }
}
