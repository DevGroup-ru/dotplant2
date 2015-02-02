<?php

namespace app\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * This is the model class for table "property".
 *
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
 * @property integer $as_yml_field
 * @property integer $captcha
 * @property PropertyGroup $group
 */
class Property extends ActiveRecord
{
    private static $identity_map = [];
    private static $group_id_to_property_ids = [];
    private $handlerAdditionalParams = [];
    public $required;
    public $interpret_as;
    public $as_yml_field;
    public $captcha;

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
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
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
                ['property_group_id', 'property_handler_id', 'has_static_values', 'has_slugs_in_values',
                    'is_eav', 'is_column_type_stored', 'multiple', 'sort_order'],
                'integer'
            ],
            [
                ['display_only_on_depended_property_selected', 'depends_on_property_id',
                    'depends_on_category_group_id', 'hide_other_values_if_selected'],
                'integer'
            ],
            [['interpret_as'], 'integer'],
            [['name', 'handler_additional_params', 'depended_property_values', 'value_type'], 'string'],
            [['key'], 'string', 'max' => 20],
            [['key'], 'match', 'pattern' => '#^[\w]+$#'],
            [['depends_on_property_id', 'depends_on_category_group_id'], 'default', 'value' => 0],
            [['required', 'captcha', 'as_yml_field'], 'integer', 'min' => 0, 'max' => 1],
            [['dont_filter'], 'safe'],
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
            'as_yml_field' => Yii::t('app', 'Interpret Field As Field Of YML'),
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
        $query = static::find()
            ->where(['property_group_id'=>$this->property_group_id]);
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
        $query->andFilterWhere(['like', 'key', $this->key]);
        $query->andFilterWhere(['property_handler_id' => $this->property_handler_id]);
        $query->andFilterWhere(['has_static_values' => $this->has_static_values]);
        $query->andFilterWhere(['has_slugs_in_values' => $this->has_slugs_in_values]);
        $query->andFilterWhere(['is_eav' => $this->is_eav]);
        $query->andFilterWhere(['is_column_type_stored' => $this->is_column_type_stored]);
        $query->andFilterWhere(['multiple' => $this->multiple]);

        return $dataProvider;
    }

    public function getGroup()
    {
        return $this->hasOne(PropertyGroup::className(), ['id' => 'property_group_id']);
    }

    /**
     * Возвращает модель по ID с использованием IdentityMap
     *
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
                    new TagDependency(
                        [
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag($prop, $id)
                            ],
                        ]
                    )
                );
            }
            static::$identity_map[$id] = $prop;
        }
        return static::$identity_map[$id];
    }

    /**
     * @param $group_id
     * @return null|Property[]
     */
    public static function getForGroupId($group_id)
    {
        if (!isset(static::$group_id_to_property_ids[$group_id])) {
            $cacheKey = "PropsForGroup:$group_id";
            if (false === $props = Yii::$app->cache->get($cacheKey)) {
                if (null !== $props = static::find()->where(['property_group_id' => $group_id])->orderBy('sort_order')->all()) {
                    Yii::$app->cache->set(
                        $cacheKey,
                        $props,
                        0,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(PropertyGroup::className(), $group_id)
                                ],
                            ]
                        )
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

    public function handler($form, $model, $values, $render_type = 'frontend_render_view')
    {
        $propertyHandler = PropertyHandler::findById($this->property_handler_id);
        $className = $propertyHandler->handler_class_name;
        return $className::widget(
            [
                'values' => $values,
                'form' => $form,
                'render_type' => $render_type,
                'label' => $this->name,
                'model' => $model,
                'property_key' => $this->key,
                'property_id' => $this->id,
                'frontend_render_view' => $propertyHandler->frontend_render_view,
                'frontend_edit_view' => $propertyHandler->frontend_edit_view,
                'backend_render_view' => $propertyHandler->backend_render_view,
                'backend_edit_view' => $propertyHandler->backend_edit_view,
                'multiple' => $this->multiple,
            ]
        );
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->handlerAdditionalParams = Json::decode($this->handler_additional_params);
        $this->required = isset($this->handlerAdditionalParams['rules'])
            && is_array($this->handlerAdditionalParams['rules'])
            && in_array('required', $this->handlerAdditionalParams['rules']);
        $this->interpret_as = isset($this->handlerAdditionalParams['interpret_as']) ?
            $this->handlerAdditionalParams['interpret_as'] :
            0;
        $this->as_yml_field = isset($this->handlerAdditionalParams['as_yml_field']) && $this->handlerAdditionalParams['as_yml_field'];
        if (isset($this->handlerAdditionalParams['rules'])
            && is_array($this->handlerAdditionalParams['rules'])) {
            foreach ($this->handlerAdditionalParams['rules'] as $rule) {
                if (is_array($rule)) {
                    if (in_array('captcha', $rule)) {
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

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        $handlerAdditionalParams = [];
        if ($this->required == 1) {
            $handlerAdditionalParams = ArrayHelper::merge(
                $handlerAdditionalParams,
                [
                    'rules' => [
                        'required',
                    ],
                ]
            );
        }
        $fileHandler = PropertyHandler::find()->where(['name' => 'File'])->one();
        if(is_object($fileHandler)){
            if($this->property_handler_id == $fileHandler->id){
                $handlerAdditionalParams = ArrayHelper::merge(
                    $handlerAdditionalParams,
                    [
                        'rules' => [
                            'file',
                        ],
                    ]
                );
            }
        }
        $handlerAdditionalParams = ArrayHelper::merge(
            $handlerAdditionalParams,
            [
                'interpret_as' => $this->interpret_as,
                'as_yml_field' => $this->as_yml_field,
            ]
        );
        if ($this->captcha == 1) {
            $handlerAdditionalParams = ArrayHelper::merge(
                $handlerAdditionalParams,
                [
                    'rules' => [
                        [
                            'captcha',
                            'captchaAction' => '/default/captcha',
                        ],
                    ],
                ]
            );
        }
        $this->handlerAdditionalParams = $handlerAdditionalParams;
        $this->handler_additional_params = Json::encode($handlerAdditionalParams);
        return true;
    }

    public function invalidateModelCache()
    {
        TagDependency::invalidate(
            Yii::$app->cache,
            [
                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(PropertyGroup::className(), $this->property_group_id),
                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(Property::className(), $this->id)
            ]
        );
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->invalidateModelCache();
        parent::afterSave($insert, $changedAttributes);
    }

    public function beforeDelete()
    {
        $this->invalidateModelCache();
        return parent::beforeDelete();
    }
}
