<?php

namespace app\extensions\DefaultTheme\components;

use app\extensions\DefaultTheme\models\ThemeWidgets;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\caching\TagDependency;
use yii\helpers\Json;

/**
 * Class BaseWidget is the base class for all theme widgets implementations.
 *
 * @package app\extensions\DefaultTheme\components
 */
abstract class BaseWidget extends Widget
{
    /** @var null|integer */
    public $themeWidgetModelId = null;

    /** @var ThemeWidgets model */
    public $themeWidgetModel = null;

    /** @var bool Use font-awesome or icon-* icons*/
    public $useFontAwesome = true;

    /**
     * Initializes widget with configuration stored in corresponding ThemeWidgets record.
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (isset($this->themeWidgetModel) === false && isset($this->themeWidgetModelId) === false) {
            throw new InvalidConfigException("Either themeWidgetModel or themeWidgetModelId must be set.");
        }

        if (isset($this->themeWidgetModelId) === true) {
            $this->themeWidgetModel = ThemeWidgets::findById($this->themeWidgetModelId);
            if ($this->themeWidgetModel === null) {
                throw new InvalidConfigException("Supplied themeWidgetModelId not found.");
            }
        }

        $configuration = Json::decode($this->themeWidgetModel->configuration_json, true);
        if (count($configuration) > 0) {
            Yii::configure($this, $configuration);
        }

    }

    /**
     * Actual run function for all widget classes extending BaseWidget
     *
     * @return mixed
     */
    abstract public function widgetRun();

    /**
     * Runs widget, caches result if needed
     * @return string
     */
    public function run()
    {
        if ($this->shouldCache()) {
            $cachedResult = Yii::$app->cache->get($this->getCacheKey());
            if ($cachedResult !== false) {
                return $cachedResult;
            }
        }
        $result = $this->widgetRun();
        if ($this->shouldCache()) {
            Yii::$app->cache->set(
                $this->getCacheKey(),
                $result,
                $this->themeWidgetModel->cache_lifetime,
                $this->getCacheDependency()
            );
        }
        return $result;
    }

    /**
     * @return bool True if we should cache this widget
     */
    private function shouldCache()
    {
        return $this->themeWidgetModel->is_cacheable === 1 && $this->themeWidgetModel->cache_lifetime > 0;
    }

    /**
     * @return string Cache key for this widget
     */
    protected function getCacheKey()
    {
        return "WidgetCache:".$this->themeWidgetModel->id;
    }

    /**
     * @return string[] Array of cache tags
     */
    protected function getCacheTags()
    {
        $tags = explode("\n", $this->themeWidgetModel->cache_tags);
        $tags[] = ActiveRecordHelper::getObjectTag(ThemeWidgets::className(), $this->themeWidgetModel->id);
        return $tags;
    }

    /**
     * @return TagDependency TagDependency for cache storing
     */
    protected function getCacheDependency()
    {
        return new TagDependency([
            'tags' => $this->getCacheTags(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function render($view, $params = [])
    {
        $params['useFontAwesome'] = $this->useFontAwesome;
        return parent::render($view, $params);
    }
}