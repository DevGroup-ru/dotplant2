<?php
namespace app\models;

use app\modules\shop\models\FilterSets;
use app\properties\HasProperties;
use app\properties\PropertyHandlers;
use app\traits\GetImages;
use Yii;
use yii\behaviors\AttributeBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;


/**
 * This is the model class for table "property".
 * @property integer $id
 * @property integer $property_group_id
 * @property string $name
 * @property string $key
 * @property string $value_type
 * @property integer $property_handler_id
 * @property integer $has_static_values
 * @property integer $has_slugs_in_values
 * @property integer $is_eav
 * @property integer $is_column_type_stored
 * @property integer $multiple
 * @property integer $sort_order
 * @property string $handler_additional_params
 * @property integer $required
 * @property integer $interpret_as
 * @property integer $captcha
 * @property string $mask
 * @property integer $alias
 * @property PropertyGroup $group
 */
class Property extends ActiveRecord
{
    use GetImages;

    public static $identity_map = [];
    public static $group_id_to_property_ids = [];
    private $handlerAdditionalParams = [];
    public $required;
    public $interpret_as;
    public $captcha;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'sort_order',
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
        return '{{%property}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['property_group_id', 'name', 'property_handler_id', 'handler_additional_params'], 'required'],
            [
                [
                    'property_group_id',
                    'property_handler_id',
                    'has_static_values',
                    'has_slugs_in_values',
                    'is_eav',
                    'is_column_type_stored',
                    'multiple',
                    'sort_order',
                    'alias',
                ],
                'integer'
            ],
            [
                [
                    'display_only_on_depended_property_selected',
                    'depends_on_property_id',
                    'depends_on_category_group_id',
                    'hide_other_values_if_selected'
                ],
                'integer'
            ],
            [['interpret_as'], 'string'],
            [['name', 'handler_additional_params', 'depended_property_values', 'value_type', 'mask'], 'string'],
            [['key'], 'string', 'max' => 20],
            [['key'], 'match', 'pattern' => '#^[\w]+$#'],
            [['depends_on_property_id', 'depends_on_category_group_id'], 'default', 'value' => 0],
            [['required', 'captcha'], 'integer', 'min' => 0, 'max' => 1],
            [['dont_filter'], 'safe'],
            [['key'], 'unique', 'targetAttribute' => ['key', 'property_group_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'property_group_id' => Yii::t('app', 'Property Group ID'),
            'name' => Yii::t('app', 'Name'),
            'key' => Yii::t('app', 'Key'),
            'value_type' => Yii::t('app', 'Value Type'),
            'property_handler_id' => Yii::t('app', 'Property Handler ID'),
            'has_static_values' => Yii::t('app', 'Has Static Values'),
            'has_slugs_in_values' => Yii::t('app', 'Has Slugs In Values'),
            'is_eav' => Yii::t('app', 'Is Eav'),
            'is_column_type_stored' => Yii::t('app', 'Is Column Type Stored'),
            'multiple' => Yii::t('app', 'Multiple'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'handler_additional_params' => Yii::t('app', 'Handler Additional Params'),
            'required' => Yii::t('app', 'Required'),
            'interpret_as' => Yii::t('app', 'Interpret Field As'),
            'captcha' => Yii::t('app', 'Captcha'),
            'dont_filter' => Yii::t('app', 'Don\'t use in filtration'),
            'hide_other_values_if_selected' => Yii::t('app', 'Hide Other Values If Selected'),
            'display_only_on_depended_property_selected' => Yii::t('app', 'Display Only On Depended Property Selected'),
            'depends_on_property_id' => Yii::t('app', 'Depends On Property Id'),
            'depended_property_values' => Yii::t('app', 'Depended Property Values'),
            'depends_on_category_group_id' => Yii::t('app', 'Depends On Category Group Id'),
            'mask' => Yii::t('app', 'Mask'),
            'alias' => Yii::t('app', 'Alias'),
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
        $query = static::find()->where(['property_group_id' => $this->property_group_id]);
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
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'key', $this->key]);
        $query->andFilterWhere(['property_handler_id' => $this->property_handler_id]);
        $query->andFilterWhere(['has_static_values' => $this->has_static_values]);
        $query->andFilterWhere(['has_slugs_in_values' => $this->has_slugs_in_values]);
        $query->andFilterWhere(['is_eav' => $this->is_eav]);
        $query->andFilterWhere(['is_column_type_stored' => $this->is_column_type_stored]);
        $query->andFilterWhere(['multiple' => $this->multiple]);
        return $dataProvider;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getGroup()
    {
        return $this->hasOne(PropertyGroup::className(), ['id' => 'property_group_id']);
    }

    /**
     * @return PropertyHandler
     */
    public function getHandler()
    {
//        return $this->hasOne(PropertyHandler::className(), ['id' => 'property_handler_id']);
        return PropertyHandler::findById($this->property_handler_id);
    }

    /**
     * Возвращает модель по ID с использованием IdentityMap
     * @param int $id
     * @return null|Property
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            $cacheKey = "Property:$id";
            if (false === $prop = Yii::$app->cache->get($cacheKey)) {
                if (null === $prop = static::findOne($id)) {
                    return null;
                }
                Yii::$app->cache->set(
                    $cacheKey,
                    $prop,
                    0,
                    new TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getObjectTag($prop, $id)
                        ],
                    ])
                );
            }
            static::$identity_map[$id] = $prop;
        }
        return static::$identity_map[$id];
    }

    /**
     * @param $group_id
     * @return null|array<Property>
     */
    public static function getForGroupId($group_id)
    {
        if (!isset(static::$group_id_to_property_ids[$group_id])) {
            $cacheKey = "PropsForGroup:$group_id";
            if (false === $props = Yii::$app->cache->get($cacheKey)) {
                if (null !== $props = static::find()->where(['property_group_id' => $group_id])->orderBy(
                        'sort_order'
                    )->all()
                ) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        $props,
                        0,
                        new TagDependency([
                            'tags' => [
                                ActiveRecordHelper::getObjectTag(
                                    PropertyGroup::className(),
                                    $group_id
                                )
                            ],
                        ])
                    );
                }
            }
            static::$group_id_to_property_ids[$group_id] = [];
            foreach ($props as $property) {
                static::$identity_map[$property->id] = $property;
                static::$group_id_to_property_ids[$group_id][] = $property->id;
            }
            return $props;
        }
        $properties = [];
        foreach (static::$group_id_to_property_ids[$group_id] as $property_id) {
            $properties[] = static::findById($property_id);
        }
        return $properties;
    }

    /**
     * @param $form
     * @param $model
     * @param $values
     * @param string $renderType
     * @return string
     */
    public function handler($form, $model, $values, $renderType = 'frontend_render_view')
    {
        $handler = $this->handler;
        if (null === $handler) {
            return '';
        }
        $handler = PropertyHandlers::createHandler($handler);
        if (null === $handler) {
            return '';
        }
        return $handler->render($this, $model, $values, $form, $renderType);
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->handlerAdditionalParams = Json::decode($this->handler_additional_params);
        $this->required = isset($this->handlerAdditionalParams['rules']) && is_array(
                $this->handlerAdditionalParams['rules']
            ) && in_array('required', $this->handlerAdditionalParams['rules']);
        $this->interpret_as = isset($this->handlerAdditionalParams['interpret_as']) ? $this->handlerAdditionalParams['interpret_as'] : 0;
        if (isset($this->handlerAdditionalParams['rules']) && is_array($this->handlerAdditionalParams['rules'])) {
            foreach ($this->handlerAdditionalParams['rules'] as $rule) {
                if (is_array($rule)) {
                    if (in_array('captcha', $rule, true)) {
                        $this->captcha = true;
                    }
                } else {
                    switch ($rule) {
                        case 'required':
                            $this->required = true;
                            break;
                    }
                }
            }
        }
    }

    /**
     * @inheritdoc
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $handlerAdditionalParams = $this->isNewRecord ? [] : Json::decode($this->handler_additional_params);
        $handlerRules = [];
        if (1 === intval($this->required)) {
            $handlerRules[] = 'required';
        }
        if (PropertyHandler::findByName('File') === intval($this->property_handler_id)) {
            if (1 === intval($this->multiple)) {
                $handlerRules[] = ['file', 'maxFiles' => 0];
            } else {
                $handlerRules[] = ['file', 'maxFiles' => 1];
            }
        }
        if (1 === intval($this->captcha)) {
            $handlerRules[] = ['captcha', 'captchaAction' => '/default/captcha'];
        }
        $handlerAdditionalParams['interpret_as'] = $this->interpret_as;
        $handlerAdditionalParams['rules'] = $handlerRules;
        $this->handlerAdditionalParams = $handlerAdditionalParams;
        $this->handler_additional_params = Json::encode($handlerAdditionalParams);
        return true;
    }

    /**
     *
     */
    public function invalidateModelCache()
    {
        TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getObjectTag(
                    PropertyGroup::className(),
                    $this->property_group_id
                ),
                ActiveRecordHelper::getObjectTag(Property::className(), $this->id)
            ]
        );
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        // @todo clear table schema
        $this->invalidateModelCache();
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return bool
     */
    public function beforeDelete()
    {
        $this->invalidateModelCache();
        return parent::beforeDelete();
    }

    public function afterDelete()
    {
        $object = Object::findById($this->group->object_id);
        $staticValues = PropertyStaticValues::find()->where(['property_id' => $this->id])->all();
        foreach ($staticValues as $psv) {
            $psv->delete();
        }
        if (null !== $object) {
            if ($this->is_eav) {
                Yii::$app->db->createCommand()->delete(
                    $object->eav_table_name,
                    ['key' => $this->key, 'property_group_id' => $this->group->id]
                )->execute();
            }
            if ($this->is_column_type_stored) {
                Yii::$app->db->createCommand()->dropColumn($object->column_properties_table_name, $this->key)->execute();
                //                if ($object->object_class == Form::className()) {
                //                    $submissionObject = Object::getForClass(Submission::className());
                //                    Yii::$app->db->createCommand()
                //                        ->dropColumn($submissionObject->column_properties_table_name, $this->key)
                //                        ->execute();
                //                }
            }
        }
        FilterSets::deleteAll(['property_id' => $this->id]);
        parent::afterDelete();
    }

    /**
     * @param $name
     * @return null|mixed
     */
    public function getAdditionalParam($name)
    {
        if (isset($this->handlerAdditionalParams[$name])) {
            return $this->handlerAdditionalParams[$name];
        }
        return null;
    }

    /**
     * @return array
     */
    public static function getAliases()
    {
        return [
            0 => Yii::t('app', 'Not selected'),
            1 => 'date',
            2 => 'ip',
            3 => 'url',
            4 => 'email',
        ];
    }
}