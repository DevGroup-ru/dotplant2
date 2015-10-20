<?php

namespace app\backend\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\caching\TagDependency;
use yii\db\ActiveRecord;
use app;
use app\behaviors\Tree;

/**
 * This is the model class for table "backend_menu".
 * BackendMenu stores tree of navigation in backend
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name Name of item - will be translated against .translation_category attribute
 * @property string $route Route or absolute URL to item
 * @property string $icon Icon for menu item
 * @property string $added_by_ext Identifier of extension that added this menu item
 * @property string $css_class CSS class attribute for menu item
 * @property string $translation_category Translation category for Yii::t()
 * @property integer $sort_order
 * @property string $rbac_check
 */
class BackendMenu extends ActiveRecord
{
    private static $identity_map = [];

    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
            [
                'class' => Tree::className(),
                'activeAttribute' => false,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%backend_menu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'sort_order'], 'integer'],
            [['name'], 'required'],
            [['rbac_check'], 'string', 'max' => 64],
            [['translation_category'], 'default', 'value' => 'app'],
            [['added_by_ext'], 'default', 'value' => 'core'],
            [['name', 'route', 'icon', 'added_by_ext', 'css_class', 'translation_category'], 'string', 'max' => 255],
            ['sort_order', 'default', 'value' => 0],
        ];
    }

    /**
     * Scenarios
     * @return array
     */
    public function scenarios()
    {
        return [
            'default' => [
                'parent_id',
                'name',
                'route',
                'icon',
                'rbac_check',
                'added_by_ext',
                'css_class',
                'sort_order',
                'translation_category',
            ],
            'search' => [
                'id',
                'parent_id',
                'name',
                'route',
                'icon',
                'added_by_ext'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'name' => Yii::t('app', 'Name'),
            'route' => Yii::t('app', 'Route'),
            'icon' => Yii::t('app', 'Icon'),
            'added_by_ext' => Yii::t('app', 'Added by extension'),
            'sort_order' => Yii::t('app', 'Sort order'),
            'rbac_check' => Yii::t('app', 'Rbac role'),
            'translation_category' => Yii::t('app', 'Translation Category'),
        ];
    }

    /**
     * Search support for GridView and etc.
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
        $query->andFilterWhere(['like', 'route', $this->route]);
        $query->andFilterWhere(['like', 'icon', $this->icon]);
        $query->andFilterWhere(['like', 'added_by_ext', $this->added_by_ext]);
        return $dataProvider;
    }

    /**
     * Returns model instance by ID(primary key) with cache support
     * @param  integer $id ID of record
     * @return BackendMenu BackendMenu instance
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            $cacheKey = static::tableName().":$id";
            if (false === $model = Yii::$app->cache->get($cacheKey)) {
                $model = static::find()->where(['id' => $id]);
                
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
            static::$identity_map[$id] = $model;
        }

        return static::$identity_map[$id];
    }

    /**
     * Returns all available to logged user BackendMenu items in yii\widgets\Menu acceptable format
     * @return BackendMenu[] Tree representation of items
     */
    public static function getAllMenu()
    {
        $rows = Yii::$app->cache->get("BackendMenu:all");
        if (false === is_array($rows)) {
            $rows = static::find()
                ->orderBy('parent_id ASC, sort_order ASC')
                ->asArray()
                ->all();
            Yii::$app->cache->set(
                "BackendMenu:all",
                $rows,
                86400,
                new TagDependency([
                    'tags' => [
                        \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className())
                    ]
                ])
            );
        }
        // rebuild rows to tree $all_menu_items
        $all_menu_items = app\behaviors\Tree::rowsArrayToMenuTree($rows, 1, 1, false);
        return $all_menu_items;
    }
}
