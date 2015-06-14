<?php

namespace app\components;

use Yii;
use yii\web\View;

class WebView extends View
{
    /** @var null|ViewElementsGathener|string */
    public $viewElementsGathener = null;

    public function init()
    {
        parent::init();
        if (is_string($this->viewElementsGathener)) {
            $this->viewElementsGathener = Yii::$app->get('viewElementsGathener');
        }
    }

    /**
     * @inheritdoc
     */
    public function beginCache($id, $properties = [])
    {
        $properties['id'] = $id;
        $properties['view'] = $this;
        $properties['viewElementsGathener'] = $this->viewElementsGathener;

        /* @var $cache AdvancedFragmentCache*/
        $cache = AdvancedFragmentCache::begin($properties);
        if ($cache->getCachedContent() !== false) {
            $this->endCache();
            return false;
        } else {
            $cache->startGathering();
            return true;
        }
    }

    /**
     * Ends fragment caching.
     */
    public function endCache()
    {
        AdvancedFragmentCache::end();
    }

    /**
     * @inheritdoc
     */
    public function registerJsFile($url, $options = [], $key = null)
    {
        $this->viewElementsGathener->gather(__FUNCTION__, func_get_args());
        return parent::registerJsFile($url, $options, $key);
    }

    /**
     * @inheritdoc
     */
    public function registerJs($js, $position = self::POS_READY, $key = null)
    {
        $this->viewElementsGathener->gather(__FUNCTION__, func_get_args());
        return parent::registerJs($js, $position, $key);
    }

    /**
     * @inheritdoc
     */
    public function registerCssFile($url, $options = [], $key = null)
    {
        $this->viewElementsGathener->gather(__FUNCTION__, func_get_args());
        return parent::registerCssFile($url, $options, $key);
    }

    /**
     * @inheritdoc
     */
    public function registerCss($css, $options = [], $key = null)
    {
        $this->viewElementsGathener->gather(__FUNCTION__, func_get_args());
        return parent::registerCss($css, $options, $key);
    }

    /**
     * @inheritdoc
     */
    public function registerLinkTag($options, $key = null)
    {
        $this->viewElementsGathener->gather(__FUNCTION__, func_get_args());
        return parent::registerLinkTag($options, $key);
    }

    /**
     * @inheritdoc
     */
    public function registerMetaTag($options, $key = null)
    {
        $this->viewElementsGathener->gather(__FUNCTION__, func_get_args());
        return parent::registerMetaTag($options, $key);
    }

    /**
     * @inheritdoc
     */
    public function registerAssetBundle($name, $position = null)
    {
        $this->viewElementsGathener->gather(__FUNCTION__, func_get_args());
        return parent::registerAssetBundle($name, $position);
    }
}
