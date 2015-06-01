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

    /** @var array ThemePart model as array where we are rendering */
    public $partRow = null;

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
        Yii::beginProfile('Widget run: '.get_class($this));
        $result = $this->widgetRun();
        Yii::endProfile('Widget run: '.get_class($this));
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
        $guestVary = Yii::$app->user->isGuest ? '1' : '0';
        $sessionVary = $this->themeWidgetModel->cache_vary_by_session ? ':' . Yii::$app->session->id . ':' . $guestVary : '';
        return "WidgetCache:".$this->themeWidgetModel->id.$sessionVary;
    }

    /**
     * @return string[] Array of cache tags
     */
    public function getCacheTags()
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
        $params['theme'] = $this->getThemeModule();
        $params['partRow'] = $this->partRow;
        $params['isInSidebar'] = $this->getIsInSidebar();
        return parent::render($view, $params);
    }

    /**
     * @return null|\app\extensions\DefaultTheme\Module
     */
    protected function getThemeModule()
    {
        return Yii::$app->getModule('DefaultTheme');
    }

    /**
     * @return bool if we are rendering in sidebar
     */
    protected function getIsInSidebar()
    {
        return mb_strpos($this->partRow['key'], 'sidebar') !== false;
    }
}