<?php

namespace app\extensions\DefaultTheme\components;

use app\extensions\DefaultTheme\models\ThemeVariation;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\caching\TagDependency;

abstract class VariationMatcher
{
    protected $variationAttributes = null;

    public $cacheable = true;

    public $cacheLifetime = 86400;

    /**
     * @param array $variationAttributes
     */
    public function __construct(array $variationAttributes)
    {
        $this->variationAttributes = $variationAttributes;
    }

    /**
     * @return bool
     */
    public function run()
    {
        if ($this->cacheable === true) {
            $result = Yii::$app->cache->get($this->getCacheKey());
            if ($result !== false) {
                // hey, there's a trick
                // match function retuns boolean
                // but we can't determine what false is (no cache entry or false match)
                // so result is an array of one item - our boolean var lol :-)
                return $result[0];
            }
        }
        $result = $this->match();
        if ($this->cacheable === true) {
            Yii::$app->cache->set(
                $this->getCacheKey(),
                [$result],
                $this->cacheLifetime,
                $this->getCacheDependency()
            );
        }
        return $result;
    }

    /**
     * @return string Cache key used for variation matching
     */
    public function getCacheKey()
    {
        return get_class($this) . ':' . Yii::$app->request->absoluteUrl;
    }

    /**
     * @return array Array of tags(strings)
     */
    public function getCacheTags()
    {
        return [
            ActiveRecordHelper::getCommonTag(ThemeVariation::className())
        ];
    }

    /**
     * @return TagDependency
     */
    public function getCacheDependency()
    {
        return new TagDependency([
            $this->getCacheTags()
        ]);
    }

    /**
     * Actual match run - all child classes should implement this function
     * @return bool
     */
    abstract public function match();
}
