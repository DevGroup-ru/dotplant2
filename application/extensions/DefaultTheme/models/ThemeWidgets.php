<?php

namespace app\extensions\DefaultTheme\models;

use app\traits\IdentityMap;
use Yii;
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
            'preview_image' => Yii::t('app', 'Preview Image'),
            'configuration_model' => Yii::t('app', 'Configuration Model'),
            'configuration_view' => Yii::t('app', 'Configuration View'),
            'configuration_json' => Yii::t('app', 'Configuration Json'),
            'is_cacheable' => Yii::t('app', 'Is Cacheable'),
            'cache_lifetime' => Yii::t('app', 'Cache Lifetime'),
            'cache_tags' => Yii::t('app', 'Cache Tags'),
            'cache_vary_by_session' => Yii::t('app', 'Cache Vary By Session'),
        ];
    }

    public function getApplying()
    {
        return $this->hasMany(ThemeWidgetApplying::className(), ['widget_id' => 'id']);
    }
}
