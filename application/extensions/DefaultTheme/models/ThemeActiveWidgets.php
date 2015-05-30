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
 */
class ThemeActiveWidgets extends \yii\db\ActiveRecord
{
    use IdentityMap;
    use SortModels;
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
            [['part_id', 'widget_id', 'variation_id', 'sort_order'], 'integer']
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
     * Returns active widgets for current request
     * @return ThemeActiveWidgets[]
     */
    public static function getActiveWidgets()
    {
        $variationIds = ArrayHelper::getColumn(
            ThemeVariation::getMatchedVariations(),
            'id'
        );
        if (count($variationIds) === 0) {
            Yii::trace("Warning! No active widgets because of no matched variations! Check your variations configuration.");
            return [];
        }

        $cacheKey = 'ActiveWidgets:'.implode(',', $variationIds);

        $models = Yii::$app->cache->get($cacheKey);
        if ($models === false) {
            $models = ThemeActiveWidgets::find()
                ->where(['in', 'variation_id', $variationIds])
                ->with('widget')
                ->orderBy(['part_id' => SORT_ASC, 'sort_order' => SORT_ASC])
                ->all();

            Yii::$app->cache->set(
                $cacheKey,
                $models,
                86400,
                new TagDependency([
                    'tags' => [
                        ActiveRecordHelper::getCommonTag(ThemeActiveWidgets::className()),
                        ActiveRecordHelper::getCommonTag(ThemeWidgets::className()),
                    ]
                ])
            );
        }
        return $models;
    }
}
