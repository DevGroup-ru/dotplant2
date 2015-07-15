<?php

namespace app\extensions\DefaultTheme\models;

use app\components\ViewElementsGathener;
use app\extensions\DefaultTheme\components\BaseWidget;
use app\traits\IdentityMap;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%theme_parts}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $key
 * @property integer $global_visibility
 * @property integer $multiple_widgets
 * @property integer $is_cacheable
 * @property integer $cache_lifetime
 * @property string $cache_tags
 * @property integer $cache_vary_by_session
 */
class ThemeParts extends \yii\db\ActiveRecord
{
    use IdentityMap;
    public static $allParts = null;
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
        return '{{%theme_parts}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['global_visibility', 'multiple_widgets', 'is_cacheable', 'cache_lifetime', 'cache_vary_by_session'], 'integer'],
            [['cache_tags'], 'string'],
            [['name', 'key'], 'string', 'max' => 255]
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
            'key' => Yii::t('app', 'Key'),
            'global_visibility' => Yii::t('app', 'Global Visibility'),
            'multiple_widgets' => Yii::t('app', 'Multiple Widgets'),
            'is_cacheable' => Yii::t('app', 'Is Cacheable'),
            'cache_lifetime' => Yii::t('app', 'Cache Lifetime'),
            'cache_tags' => Yii::t('app', 'Cache Tags'),
            'cache_vary_by_session' => Yii::t('app', 'Cache Vary By Session'),
        ];
    }

    /**
     * Returns all db-stored theme parts in array representation
     *
     * @param bool $force True if you want to refresh static-variable cache
     * @return array
     */
    public static function getAllParts($force = false)
    {
        if (static::$allParts=== null || $force === true) {
            $cacheKey = 'AllThemeParts';

            Yii::beginProfile('Get all theme parts');

            static::$allParts= Yii::$app->cache->get($cacheKey);
            if (static::$allParts=== false) {
                static::$allParts= ThemeParts::find()
                    ->where(['global_visibility'=>1])
                    ->asArray()
                    ->all();
                Yii::$app->cache->set(
                    $cacheKey,
                    static::$allParts,
                    86400,
                    new TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(ThemeVariation::className()),
                        ]
                    ])
                );
            }
            Yii::endProfile('Get all theme parts');
        }
        return static::$allParts;
    }

    /**
     * Renders specified theme part with all it's widget corresponding current theme variation
     * @param string $key Theme part key(ie. header or pre-footer)
     * @param array $params
     * @return string
     * @throws InvalidConfigException
     */
    public static function renderPart($key, $params=[])
    {
        $parts = static::getAllParts();
        Yii::beginProfile('Render theme part:' . $key);
        Yii::trace('Render theme part:' . $key);
        /** @var array $model */
        $model = null;
        foreach ($parts as $part) {
            if ($part['key'] === $key) {
                $model = $part;
                break;
            }
        }
        if ($model === null) {
            throw new InvalidConfigException("Can't find part with key $key");
        }

        /** @var ViewElementsGathener $viewElementsGathener */
        $viewElementsGathener = Yii::$app->viewElementsGathener;


        if (static::shouldCache($model)) {
            $result = Yii::$app->cache->get(static::getCacheKey($model));
            if ($result !== false) {
                Yii::endProfile('Render theme part:' . $key);
                $cachedData = $viewElementsGathener->getCachedData('ThemePart:'.$key);
                if (is_array($cachedData)) {
                    $viewElementsGathener->repeatGatheredData(Yii::$app->view, $cachedData);
                }
                return $result;
            }
            $viewElementsGathener->startGathering(
                'ThemePart:'.$key,
                static::getCacheDependency($model)
            );
        }

        $model['id'] = intval($model['id']);

        $widgets = array_reduce(
            ThemeActiveWidgets::getActiveWidgets(),
            function ($carry, $item) use($model) {
                if ($item['part_id'] === $model['id']) {
                    $carry[]=$item;
                }
                return $carry;
            },
            []
        );
        ArrayHelper::multisort($widgets, 'sort_order');

        $result = array_reduce(
            $widgets,
            function($carry, ThemeActiveWidgets $activeWidget) use ($model, $params) {
                /** @var ThemeWidgets $widgetModel */
                $widgetModel = $activeWidget->widget;
                /** @var BaseWidget $widgetClassName */
                $widgetClassName =  $widgetModel->widget;
                $widgetConfiguration = Json::decode($widgetModel->configuration_json, true);
                if (!is_array($widgetConfiguration)) {
                    $widgetConfiguration = [];
                }
                $activeWidgetConfiguration = Json::decode($activeWidget->configuration_json, true);
                if (!is_array($activeWidgetConfiguration)) {
                    $activeWidgetConfiguration  = [];
                }
                $merged = ArrayHelper::merge($widgetConfiguration, $activeWidgetConfiguration);
                $config = ArrayHelper::merge($merged, $params);
                $config['themeWidgetModel'] = $widgetModel;
                $config['partRow'] = $model;
                $config['activeWidget'] = $activeWidget;

                $carry .= $widgetClassName::widget($config);
                return $carry;
            },
            ''
        );

        if (static::shouldCache($model)) {
            Yii::$app->cache->set(
                static::getCacheKey($model),
                $result,
                $model['cache_lifetime'],
                static::getCacheDependency($model)
            );
            $viewElementsGathener->endGathering();
        }
        Yii::endProfile('Render theme part:' . $key);
        return $result;
    }

    /**
     * @param array $attributesRow
     * @return bool True if we should cache this widget
     */
    public static function shouldCache($attributesRow)
    {
        return intval($attributesRow['is_cacheable'])=== 1 && $attributesRow['cache_lifetime']> 0;
    }

    /**
     * @param array $attributesRow
     * @return string Cache key for this widget
     */
    public static function getCacheKey($attributesRow)
    {
        $guestVary = Yii::$app->user->isGuest ? '1' : '0';
        $sessionVary = $attributesRow['cache_vary_by_session'] ? ':' . Yii::$app->session->id . ':' . $guestVary : '';
        $cacheKey = "ThemePartCache:".$attributesRow['id'] . $sessionVary;
        return $cacheKey;
    }

    /**
     * @param array $attributesRow
     * @return string[] Array of cache tags
     */
    public static function getCacheTags($attributesRow)
    {
        $tags = explode("\n", $attributesRow['cache_tags']);
        $tags[] = ActiveRecordHelper::getObjectTag(ThemeParts::className(), $attributesRow['id']);
        $tags[] = ActiveRecordHelper::getCommonTag(ThemeActiveWidgets::className());
        return $tags;
    }

    /**
     * @param array $attributesRow
     * @return TagDependency TagDependency for cache storing
     */
    public static function getCacheDependency($attributesRow)
    {
        return new TagDependency([
            'tags' => static::getCacheTags($attributesRow),
        ]);
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
                    'pageParam' => 'part-page',
                    'pageSizeParam' => 'part-per-page',
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['global_visibility' => $this->global_visibility]);
        $query->andFilterWhere(['is_cacheable' => $this->is_cacheable]);
        $query->andFilterWhere(['cache_vary_by_session' => $this->cache_vary_by_session]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'key', $this->key]);

        return $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        $activeParts = ThemeWidgetApplying::find()->where(['part_id'=>$this->id])->all();
        foreach ($activeParts as $part) {
            $part->delete();
        }

        $activeWidgets = ThemeActiveWidgets::find()->where(['widget_id'=>$this->id])->all();
        foreach ($activeWidgets as $widget) {
            $widget->delete();
        }

        return true;
    }
}
