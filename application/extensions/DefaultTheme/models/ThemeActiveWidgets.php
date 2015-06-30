<?php

namespace app\extensions\DefaultTheme\models;

use app\traits\IdentityMap;
use app\traits\SortModels;
use Yii;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%theme_active_widgets}}".
 *
 * @property integer $id
 * @property integer $part_id
 * @property integer $widget_id
 * @property integer $variation_id
 * @property integer $sort_order
 * @property string $configuration_json
 * @property ThemeWidgets $widget
 * @property ThemeParts $part
 */
class ThemeActiveWidgets extends \yii\db\ActiveRecord
{
    use IdentityMap;
    use SortModels;

    public static $activeWidgets = null;
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
        return '{{%theme_active_widgets}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['part_id', 'widget_id', 'variation_id', 'sort_order'], 'integer'],
            [['configuration_json'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'part_id' => Yii::t('app', 'Part ID'),
            'widget_id' => Yii::t('app', 'Widget ID'),
            'variation_id' => Yii::t('app', 'Variation ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'configuration_json' => Yii::t('app', 'Configuration JSON'),
        ];
    }

    /**
     * Relation to ThemeWidgets
     * @return ThemeWidgets
     */
    public function getWidget()
    {
        return $this->hasOne(ThemeWidgets::className(), ['id' => 'widget_id']);
    }

    /**
     * Relation to ThemeWidgets
     * @return ThemeWidgets
     */
    public function getPart()
    {
        return $this->hasOne(ThemeParts::className(), ['id' => 'part_id']);
    }

    /**
     * Returns active widgets for current request
     * @return ThemeActiveWidgets[]
     */
    public static function getActiveWidgets()
    {
        if (static::$activeWidgets === null) {
            Yii::beginProfile('Get active widgets');
            $variationIds = ArrayHelper::getColumn(
                ThemeVariation::getMatchedVariations(),
                'id'
            );
            if (count($variationIds) === 0) {
                Yii::trace("Warning! No active widgets because of no matched variations! Check your variations configuration.");
                Yii::endProfile('Get active widgets');
                return [];
            }

            $cacheKey = 'ActiveWidgets:' . implode(',', $variationIds);

            static::$activeWidgets = Yii::$app->cache->get($cacheKey);
            if (static::$activeWidgets === false) {
                static::$activeWidgets = ThemeActiveWidgets::find()
                    ->where(['in', 'variation_id', $variationIds])
                    ->with('widget')
                    ->orderBy(['part_id' => SORT_ASC, 'sort_order' => SORT_ASC])
                    ->all();

                Yii::$app->cache->set(
                    $cacheKey,
                    static::$activeWidgets,
                    86400,
                    new TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(ThemeActiveWidgets::className()),
                            ActiveRecordHelper::getCommonTag(ThemeWidgets::className()),
                        ]
                    ])
                );
            }
            Yii::endProfile('Get active widgets');
        }
        return static::$activeWidgets;
    }
}
