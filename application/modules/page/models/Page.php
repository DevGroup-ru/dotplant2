<?php

namespace app\modules\page\models;

use app\behaviors\CleanRelations;
use app\behaviors\Tree;
use app\modules\image\models\Image;
use app\properties\HasProperties;
use app\traits\GetImages;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "page".
 * @property integer $id
 * @property integer $parent_id
 * @property string $slug
 * @property string $slug_compiled
 * @property boolean $slug_absolute
 * @property boolean $published
 * @property boolean $searchable
 * @property integer $robots
 * @property string $title
 * @property string $h1
 * @property string $meta_description
 * @property string $breadcrumbs_label
 * @property string $content
 * @property string $announce
 * @property integer $sort_order
 * @property string $date_added
 * @property string $date_modified
 * @property string $show_type
 * @property string $name
 * @property Page[] $children children of current page
 * @property Page $parent parent of current page
 * @property string|false $subdomain subdomain's name or false if it's not set
 * @property Image $images
 */
class Page extends ActiveRecord implements \JsonSerializable
{
    use GetImages;
    private static $identity_map = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%page}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slug', 'title'], 'required'],
            [['robots', 'parent_id', 'sort_order'], 'integer'],
            [['slug_absolute', 'published', 'searchable'], 'boolean'],
            [
                [
                    'content',
                    'title',
                    'h1',
                    'meta_description',
                    'breadcrumbs_label',
                    'announce',
                    'slug_compiled',
                    'name'
                ],
                'string'
            ],
            [['date_added', 'date_modified'], 'safe'],
            [['slug'], 'string', 'max' => 80],
            [['slug_compiled'], 'string', 'max' => 180],
            [['show_type', 'subdomain'], 'string'],
            ['sort_order', 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'slug' => Yii::t('app', 'Slug'),
            'slug_compiled' => Yii::t('app', 'Slug Compiled'),
            'slug_absolute' => Yii::t('app', 'Slug Absolute'),
            'content' => Yii::t('app', 'Content'),
            'published' => Yii::t('app', 'Published'),
            'searchable' => Yii::t('app', 'Searchable'),
            'show_type' => Yii::t('app', 'Show Type'),
            'robots' => Yii::t('app', 'Robots'),
            'title' => Yii::t('app', 'Title'),
            'h1' => Yii::t('app', 'H1'),
            'meta_description' => Yii::t('app', 'Meta Description'),
            'breadcrumbs_label' => Yii::t('app', 'Breadcrumbs Label'),
            'announce' => Yii::t('app', 'Announce'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'date_added' => Yii::t('app', 'Date Added'),
            'date_modified' => Yii::t('app', 'Date Modified'),
            'subdomain' => Yii::t('app', 'Subdomain'),
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
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
                'class' => Tree::className(),
                'activeAttribute' => 'published',
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
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /** @var $query \yii\db\ActiveQuery */
        $query = self::find()->with('images');
        if (null != $this->parent_id) {
            $query->andWhere(['parent_id' => $this->parent_id]);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'slug', $this->slug]);
        $query->andFilterWhere(['like', 'slug_compiled', $this->slug_compiled]);
        $query->andFilterWhere(['like', 'slug_absolute', $this->slug_absolute]);
        $query->andFilterWhere(['published' => $this->published]);
        $query->andFilterWhere(['like', 'title', $this->title]);
        $query->andFilterWhere(['like', 'h1', $this->h1]);
        $query->andFilterWhere(['like', 'meta_description', $this->meta_description]);
        $query->andFilterWhere(['like', 'breadcrumbs_label', $this->breadcrumbs_label]);
        return $dataProvider;
    }

    /**
     * @param int $id
     * @param int $is_published
     * @return mixed
     */
    public static function findById($id, $is_published = 1)
    {
        if (!is_numeric($id)) {
            return null;
        }
        if (!isset(static::$identity_map[$id])) {

            $cacheKey = "Page:$id:$is_published";
            static::$identity_map[$id] = Yii::$app->cache->get($cacheKey);
            if (!is_object(static::$identity_map[$id])) {
                static::$identity_map[$id] = Page::find()->where(['id' => $id, 'published' => $is_published])->with('images')->one();

                if (is_object(static::$identity_map[$id])) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        static::$identity_map[$id],
                        86400,
                        new TagDependency([
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(static::className())
                            ]
                        ])
                    );
                }
            }
        }
        return static::$identity_map[$id];
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if (!$insert) {
            // reset a cache tag to get a new parent model below
            TagDependency::invalidate(Yii::$app->cache, [ActiveRecordHelper::getCommonTag(self::className())]);
        }
        $this->slug_compiled = $this->compileSlug();
        TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag($this->className()),
                'Page:' . $this->slug_compiled
            ]
        );
        TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag($this->className()),
                'Page:' . $this->id . ':0'
            ]
        );
        TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag($this->className()),
                'Page:' . $this->id . ':1'
            ]
        );
        if (empty($this->breadcrumbs_label)) {
            $this->breadcrumbs_label = $this->title;
        }
        if (empty($this->h1)) {
            $this->h1 = $this->title;
        }
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        static::$identity_map[$this->id] = $this;

    }

    /**
     * Compiles url for subdomain's links like http://subdomain.example.com
     *
     * Respects schema, relies on Yii::$app->getModule("core")->serverName as main domain name
     * and Yii::$app->getModule("core")->serverPort
     *
     * @param string $sub_domain
     * @param string $slug
     * @return string compiled url
     */
    private function compileUrl($sub_domain, $slug = "")
    {
        $schema = Yii::$app->request->isSecureConnection ? "https://" : "http://";
        $main_domain = str_replace('www.', '', Yii::$app->getModule("core")->serverName);

        return "{$schema}{$sub_domain}.{$main_domain}/{$slug}";
    }

    /**
     * Compiles slug based on parent compiled slug, slug absoluteness and subdomain property
     * @return string
     */
    public function compileSlug()
    {
        // accessing parent's model via Tree behaviour
        $parent_model = $this->parent;

        if (intval($this->slug_absolute) === 1) {
            $slug = ($this->slug === ":mainpage:" || $this->slug === ":subdomainmainpage:") ? "" : $this->slug;
            $subdomain = null;
            if (empty($this->subdomain) === false) {
                $subdomain = $this->subdomain;
            } elseif ($parent_model !== null) {
                if (empty($parent_model->subdomain) === false) {
                    // subdomain in parent is set - here is not
                    $subdomain = $parent_model->subdomain;
                }
            }

            if ($subdomain === null) {
                // subdomain is empty
                // no root
                return $this->slug;
            } else {
                return $this->compileUrl($subdomain, $slug);
            }
        } else {
            // not-absolute slug
            if ($parent_model !== null) {
                // should prepend parent's slug
                // can't be another domain then parent
                if ($parent_model->slug === ':mainpage:') {
                    return $this->slug;
                } else {
                    return $parent_model->slug_compiled . '/' . $this->slug;
                }
            } else {
                return ':mainpage:'; // it's main page
            }
        }
    }

    /**
     * @param string $path PATH из запроса ( $request->getPathInfo() )
     * @return mixed|null
     */
    public static function getByUrlPath($path = null)
    {
        if ((null === $path) || !is_string($path)) {
            return null;
        }
        $cacheKey = "Page:$path";
        $page = Yii::$app->cache->get($cacheKey);
        if ($page === false) {
            $page = static::find()->where(['slug_compiled' => $path, 'published' => 1])->asArray()->one();
            $duration = 86400;
            if (!is_array($page)) {
                $duration = 3600;
            }
            Yii::$app->cache->set(
                $cacheKey,
                $page,
                $duration,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(static::className())
                    ]
                ])
            );
        }
        return $page;
    }

    /**
     * Preparation to delete page.
     * Deleting all inserted pages.
     * @return bool
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }
        foreach ($this->children as $child) {
            /** @var Page $child */
            $child->delete();
        }
        return true;
    }

    public function getChildren()
    {
        return $this->hasMany(Page::className(), ['parent_id' => 'id'])->orderBy(['sort_order'=>SORT_ASC]);
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
