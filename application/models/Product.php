<?php

namespace app\models;

use app\behaviors\CleanRelations;
use app\behaviors\Tree;
use app\properties\HasProperties;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\VarDumper;

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
class Product extends ActiveRecord
{
    private static $identity_map = [];
    private static $slug_to_id = [];

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
                'class' => \app\behaviors\TagDependency::className(),
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
                                \app\behaviors\TagDependency::getCommonTag(static::className())
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
                                \app\behaviors\TagDependency::getCommonTag(static::className())
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
}
