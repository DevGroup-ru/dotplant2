<?php

namespace app\widgets\navigation\models;

use app\backgroundtasks\traits\SearchModelTrait;
use app\behaviors\Tree;
use app\properties\HasProperties;
use app\traits\FindById;
use app\traits\GetImages;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "navigation".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 * @property string $url
 * @property string $route
 * @property string $route_params
 * @property string $advanced_css_class
 * @property integer $sort_order
 * @property boolean $active
 * @property Navigation[] $children
 * @property Navigation $parent
 */
class Navigation extends \yii\db\ActiveRecord
{
    use GetImages;
    use SearchModelTrait;
    use FindById;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%navigation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'name'], 'required', 'except' => ['search']],
            [['parent_id', 'sort_order'], 'integer'],
            [['url', 'route', 'route_params', 'advanced_css_class'], 'string'],
            [['name'], 'string', 'max' => 80],
            [['route', 'url'], 'required', 'when' => function($model) {
                return empty($model->route) && empty($model->url);
            }, 'message' => Yii::t('app', 'Either URL or Route should be set.'), 'whenClient' => "
            function(attribute, value) {
                if (attribute.id === 'navigation-url') {
                    return \$('#navigation-route').val() === '' && value === '';
                } else {
                    return \$('#navigation-url').val() === '' && value === '';
                }
            }"],
            ['sort_order', 'default', 'value' => 0],
            ['active', 'boolean']
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
            'url' => Yii::t('app', 'Url'),
            'route' => Yii::t('app', 'Route'),
            'route_params' => Yii::t('app', 'Route Params'),
            'advanced_css_class' => Yii::t('app', 'Advanced Css Class'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'active' => Yii::t('app', 'Active')
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
            [
                'class' => Tree::className(),
                'sortOrder' => ['sort_order' => SORT_ASC],
                'activeAttribute' => false,
                'cascadeDeleting' => true,
            ],
            [
                'class' => HasProperties::className(),
            ],
        ];
    }

    public function scenarios()
    {
        return [
            'default' => ['parent_id', 'name', 'url', 'route', 'route_params', 'advanced_css_class', 'sort_order', 'active'],
            'search' => ['parent_id', 'name', 'url', 'route', 'route_params', 'advanced_css_class', 'sort_order', 'active'],
        ];
    }

    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
            ]
        );
        $query->andWhere(['parent_id' => $this->parent_id]);
        /* @var \yii\db\ActiveRecord|SearchModelTrait $this */
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $this->addCondition($query, Navigation::tableName(), 'name', true);
        $this->addCondition($query, Navigation::tableName(), 'url', true);
        $this->addCondition($query, Navigation::tableName(), 'route', true);
        $this->addCondition($query, Navigation::tableName(), 'route_params', true);
        $this->addCondition($query, Navigation::tableName(), 'active', true);
        return $dataProvider;
    }

    public function getChildren()
    {
        return $this->hasMany(Navigation::className(), ['parent_id'=>'id'])
            ->where(["active" => 1])
            ->orderBy(['sort_order'=>SORT_ASC]);
    }
}
