<?php

namespace app\extensions\DefaultTheme\models;

use app\traits\IdentityMap;
use Yii;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;

/**
 * This is the model class for table "{{%theme_widgets}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $widget
 * @property string $preview_image
 * @property string $configuration_model
 * @property string $configuration_view
 * @property string $configuration_json
 * @property integer $is_cacheable
 * @property integer $cache_lifetime
 * @property string $cache_tags
 * @property integer $cache_vary_by_session
 * @property ThemeWidgetApplying $applying
 * @property ThemeParts $applicableParts
 */
class ThemeWidgets extends \yii\db\ActiveRecord
{
    use IdentityMap;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%theme_widgets}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['configuration_json', 'cache_tags'], 'string'],
            [['is_cacheable', 'cache_lifetime', 'cache_vary_by_session'], 'integer'],
            [['name', 'widget', 'preview_image', 'configuration_model', 'configuration_view'], 'string', 'max' => 255],
            [['configuration_json'], 'default', 'value' => '{}'],
            [['name', 'widget'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'widget' => Yii::t('app', 'Widget'),
            'preview_image' => Yii::t('app', 'Preview image'),
            'configuration_model' => Yii::t('app', 'Configuration model'),
            'configuration_view' => Yii::t('app', 'Configuration view'),
            'configuration_json' => Yii::t('app', 'Configuration JSON'),
            'is_cacheable' => Yii::t('app', 'Is cacheable'),
            'cache_lifetime' => Yii::t('app', 'Cache lifetime'),
            'cache_tags' => Yii::t('app', 'Cache tags'),
            'cache_vary_by_session' => Yii::t('app', 'Cache vary by session'),
        ];
    }

    /**
     * Relation to ThemeWidgetApplying
     * @return \yii\db\ActiveQuery
     */
    public function getApplying()
    {
        return $this->hasMany(ThemeWidgetApplying::className(), ['widget_id' => 'id']);
    }

    /**
     * Relation to ThemeParts that we can apply to
     * @return \yii\db\ActiveQuery
     */
    public function getApplicableParts()
    {
        return $this->hasMany(ThemeParts::className(), ['id' => 'part_id'])
            ->via('applying');
    }

    /**
     * Search
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = self::find();
        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageParam' => 'widget-page',
                    'pageSizeParam' => 'widget-per-page',
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['is_cacheable' => $this->is_cacheable]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'widget', $this->widget]);
        $query->andFilterWhere(['like', 'cache_tags', $this->cache_tags]);
        $query->andFilterWhere(['cache_vary_by_session', $this->cache_vary_by_session]);
        $query->andFilterWhere(['cache_lifetime', $this->cache_lifetime]);

        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        TagDependency::invalidate(
            Yii::$app->cache,
            [
                ActiveRecordHelper::getCommonTag(ThemeActiveWidgets::className())
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        $parts = ThemeWidgetApplying::find()->where(['widget_id'=>$this->id])->all();
        foreach ($parts as $part) {
            $part->delete();
        }

        $activeWidgets = ThemeActiveWidgets::find()->where(['widget_id'=>$this->id])->all();
        foreach ($activeWidgets as $widget) {
            $widget->delete();
        }

        return true;
    }
}
