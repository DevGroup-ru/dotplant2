<?php

namespace app\models;

use app\modules\shop\models\ConfigConfigurationModel;
use app\modules\shop\models\Product;
use app\properties\HasProperties;
use app\traits\GetImages;
use app\traits\SortModels;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use app\modules\shop\models\Currency;

/**
 * This is the model class for table "property_static_values".
 * @property integer $id
 * @property integer $property_id
 * @property string $name
 * @property string $value
 * @property string $slug
 * @property integer $sort_order
 * @property Property $property
 * @property integer $dont_filter
 */
class PropertyStaticValues extends ActiveRecord
{
    use GetImages;

    public static $identity_map_by_property_id = [];
    private static $identity_map = [];

    use SortModels;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => 'sort_order',
                ],
                'value' => 0,
            ],
            [
                'class' => ActiveRecordHelper::className(),
            ],
            [
                'class' => HasProperties::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%property_static_values}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['property_id', 'name', 'value'], 'required'],
            [['property_id', 'sort_order', 'dont_filter'], 'integer'],
            [['slug'], 'unique'],
            [['title_prepend'], 'boolean'],
            [['name', 'value', 'slug', 'title_append'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'property_id' => Yii::t('app', 'Property ID'),
            'name' => Yii::t('app', 'Name'),
            'value' => Yii::t('app', 'Value'),
            'slug' => Yii::t('app', 'Slug'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'title_append' => Yii::t('app', 'Title append'),
            'title_prepend' => Yii::t('app', 'Prepend title?'),
            'dont_filter' => Yii::t('app', 'Don\'t filter (for FilterWidget only)'),
        ];
    }

    /**
     * Search tasks
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = static::find()->where(['property_id' => $this->property_id]);
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
        $query->andFilterWhere(['like', 'value', $this->value]);
        $query->andFilterWhere(['like', 'slug', $this->slug]);
        return $dataProvider;
    }

    public function getProperty()
    {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }

    /**
     * Возвращает Массив! по ID с использованием IdentityMap
     * @param int $id
     * @return null|PropertyStaticValues
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            $cacheKey = "PropertyStaticValue:$id";

            if (false === $property = Yii::$app->cache->get($cacheKey)) {
                if (null !== $property = static::find()->where(['id' => $id])->asArray()->one()) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        $property,
                        0,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(
                                        static::className(),
                                        $id
                                    ),
                                ],
                            ]
                        )
                    );
                }
            }
            static::$identity_map[$id] = $property;
        }
        return static::$identity_map[$id];
    }

    /**
     * Возвращает массив возможных значений свойств по property_id
     * Внимание! Это массивы, а не объекты!
     * Это сделано для экономии памяти.
     * Используется identity_map
     *
     * @return array
     */
    public static function getValuesForPropertyId($property_id)
    {
        if (!isset(static::$identity_map_by_property_id[$property_id])) {
            static::$identity_map_by_property_id[$property_id] = static::arrayOfValuesForPropertyId($property_id);
            foreach (static::$identity_map_by_property_id[$property_id] as $psv) {
                static::$identity_map[$psv['id']] = $psv;
            }
        }
        return static::$identity_map_by_property_id[$property_id];
    }

    public static function getSelectForPropertyId($property_id)
    {
        $values = PropertyStaticValues::getValuesForPropertyId($property_id);
        $result = [];
        foreach ($values as $row) {
            $result[$row['id']] = $row['name'];
        }
        return $result;
    }

    /**
     * @param $property_id
     * @param $category_id
     * @param $properties
     * @return PropertyStaticValues[]
     */
    public static function getValuesForFilter($property_id, $category_id, $properties, $multiple = false, $productsFilteringMode)
    {
        $priceMin = Yii::$app->request->get('price_min');
        $priceMax = Yii::$app->request->get('price_max');
        $cacheKey = "getValuesForFilter:" . json_encode([$property_id, $category_id, $properties, $priceMin, $priceMax]);
        if (false === $allSelections = Yii::$app->cache->get($cacheKey)) {

            switch($productsFilteringMode) {
                case ConfigConfigurationModel::FILTER_PARENTS_ONLY:
                    $joinCondition =  'p.id = {{%product_category}}.object_model_id AND p.active = 1 AND p.parent_id = 0';
                    break;
                case ConfigConfigurationModel::FILTER_CHILDREN_ONLY:
                    $joinCondition =  'p.id = {{%product_category}}.object_model_id AND p.active = 1 AND p.parent_id != 0';
                    break;
                default:
                    $joinCondition =  'p.id = {{%product_category}}.object_model_id AND p.active = 1';
                    break;
            }

            $objectModel = Object::getForClass(Product::className());
            $objectId = $objectModel !== null ? $objectModel->id : 0;
            $allSelections = static::find()
                ->asArray(true)
                ->select([self::tableName() . '.id', self::tableName() . '.name', 'value', self::tableName() . '.slug'])
                ->innerJoin(
                    ObjectStaticValues::tableName(),
                    ObjectStaticValues::tableName() . '.property_static_value_id=' . self::tableName() . '.id'
                )
                ->innerJoin(
                    '{{%product_category}}',
                    '{{%product_category}}.object_model_id = ' . ObjectStaticValues::tableName() . '.object_model_id'
                )
                ->innerJoin(
                    Product::tableName() . ' p',
                    $joinCondition
                )
                ->where(
                    [
                        self::tableName() . '.property_id' => $property_id,
                        self::tableName() . '.dont_filter' => 0,
                        '{{%product_category}}.category_id' => $category_id,
                    ]
                )
                ->orderBy(
                    [
                        self::tableName() . '.sort_order' => SORT_ASC,
                        self::tableName() . '.name' => SORT_ASC,
                    ]
                )
                ->all();
            /** @var ActiveQuery $query */
            $query = ObjectStaticValues::find()
                ->distinct(true)
                ->select(ObjectStaticValues::tableName() . '.object_model_id')
                ->where(['object_id' => $objectId]);
            if (false === empty($properties)) {
                foreach ($properties as $propertyId => $propertyStaticValues) {
                    $subQuery = self::initSubQuery($category_id, $joinCondition);
                    $subQuery->andWhere(['property_static_value_id' => $propertyStaticValues,]);
                    $subQueryOptimisation = Yii::$app->db->cache(function($db) use ($subQuery) {
                        $ids = implode(', ', $subQuery->createCommand($db)->queryColumn());
                        return empty($ids) === true ? '(-1)' : "($ids)";
                    }, 86400, new TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(ObjectStaticValues::className()),
                        ]
                    ]));
                    $query->andWhere(new Expression('`object_model_id` IN ' . $subQueryOptimisation));
                }
            }
            if (false === empty($priceMin) && false === empty($priceMax)) {
                $subQuery = self::initSubQuery($category_id, $joinCondition);
                $subQuery
                    ->andWhere('p.price >= (:min_price * currency.convert_nominal / currency.convert_rate)',
                        [':min_price' => $priceMin])
                    ->andWhere('p.price <= (:max_price * currency.convert_nominal / currency.convert_rate)',
                        [':max_price' => $priceMax])
                    ->leftJoin(Currency::tableName() . ' ON currency.id = p.currency_id');
                $subQueryOptimisation = Yii::$app->db->cache(function($db) use ($subQuery) {
                    $ids = implode(', ', $subQuery->createCommand($db)->queryColumn());
                    return empty($ids) === true ? '(-1)' : "($ids)";
                }, 86400, new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(ObjectStaticValues::className()),
                    ]
                ]));
                $query->andWhere(new Expression('`object_model_id` IN ' . $subQueryOptimisation));
            }
            $selectedQuery = static::find()
                ->select(static::tableName() . '.id')
                ->asArray(true)
                ->innerJoin(
                    ObjectStaticValues::tableName(),
                    ObjectStaticValues::tableName() . '.property_static_value_id = ' . static::tableName() . '.id'
                )
                ->where([
                    'property_id' => $property_id,
                ])
                ->andWhere(
                    new Expression(
                        ObjectStaticValues::tableName() . '.object_model_id IN (' . $query->createCommand()->getRawSql() . ')'
                    )
                );
            if (false == $multiple) {
                if (isset($properties[$property_id])) {
                    $selectedQuery->andWhere([self::tableName() . '.id' => $properties[$property_id]]);
                }
            } else {
                unset($properties[$property_id]);
            }
            $selected = $selectedQuery->column();
            foreach ($allSelections as $index => $selection) {
                $allSelections[$index]['active'] = in_array($selection['id'], $selected);
            }
            if (null !== $allSelections) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $allSelections,
                    0,
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(PropertyStaticValues::className()),
                                ActiveRecordHelper::getCommonTag(Property::className()),
                            ]

                        ]
                    )
                );
            }
        }
        return $allSelections;
    }

    private static function initSubQuery($category_id, $joinCondition)
    {
        $subQuery = ObjectStaticValues::find();
        return $subQuery
            ->select(ObjectStaticValues::tableName() . '.object_model_id')
            ->innerJoin(
                '{{%product_category}}',
                '{{%product_category}}.object_model_id = ' . ObjectStaticValues::tableName() . '.object_model_id'
            )->innerJoin(
                Product::tableName() . ' p',
                $joinCondition
            )->where(
                [
                    'category_id' => $category_id,
                ]
            );
    }
    /**
     * Аналогично getValuesForPropertyId
     * Но identity_map не используется
     * @param int $property_id
     * @return array|mixed|\yii\db\ActiveRecord[]
     */
    public static function arrayOfValuesForPropertyId($property_id)
    {
        $cacheKey = "ValuesForProperty:$property_id";

        if (false === $values = Yii::$app->cache->get($cacheKey)) {
            $values = static::find()->where(['property_id' => $property_id])->orderBy(
                [
                    'sort_order' => SORT_ASC,
                    'name' => SORT_ASC
                ]
            )->asArray()->all();
            if (null !== $values) {
                Yii::$app->cache->set(
                    $cacheKey,
                    $values,
                    0,
                    new TagDependency(
                        [
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(PropertyStaticValues::className()),
                                ActiveRecordHelper::getCommonTag(Property::className()),
                            ]

                        ]
                    )
                );
            }
        }
        return $values;
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (null !== $parent = Property::findById($this->property_id)) {
            $parent->invalidateModelCache();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {
        if (null !== $parent = Property::findById($this->property_id)) {
            $parent->invalidateModelCache();
        }
        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        ObjectStaticValues::deleteAll(['property_static_value_id' => $this->id]);
        parent::afterDelete();
    }
}
