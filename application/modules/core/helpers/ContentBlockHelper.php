<?php
namespace app\modules\core\helpers;

use app\modules\core\models\ContentBlock;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii;
use app;

class ContentBlockHelper
{
    public static $prefix = '[';
    public static $suffix = ']';
    private static $chunksByKey = [];
    /**
     * Compiles content string by injecting chunks into content
     * @param  {string} $content     Original content with chunk calls
     * @param  {string} $content_key Key for caching compiled content version
     * @param  {yii\caching\Dependency} $dependency  Cache dependency
     * @return {string}              Compiled content with injected chunks
     */
    public static function compileContentString($content, $content_key, $dependency) {
        self::preloadChunks();
        $matches = [];
        preg_match_all('%['.static::$prefix.']{2}([^\]\[]+)['.static::$suffix.']{2}%ui', $content, $matches);
        if (!empty($matches)) {
            foreach ($matches[0] as $k => $rawChunk) {
                $chunkData = static::sanitizeChunk($rawChunk);
                $cacheChunkKey = $chunkData['key'].$content_key;
                $replacement = Yii::$app->cache->get($cacheChunkKey);
                if ($replacement === false) {
                    $chunk = self::fetchChunkByKey($chunkData['key']);
                    $replacement = static::compileChunk($chunk, $chunkData);
                    if (null !== $chunk ) {
                        Yii::$app->cache->set(
                            $cacheChunkKey,
                            $replacement,
                            84600,
                            $dependency
                        );
                    }
                }
                $content = str_replace($matches[0][$k], $replacement, $content);
            }
        }
        return $content;
    }

    /**
     * Extracting chunk data from chunk call
     * @param $rawChunk
     * @return array
     */
    private static function sanitizeChunk($rawChunk) {
        $chunk = [];
        preg_match('%\$([^\s\]]+)[\s\]]%', $rawChunk, $keyMatches);
        $chunk['key'] = $keyMatches[1];
        $expression = "#\s*(?P<paramName>[\\w\\d]*)=(('(?P<escapedValue>.*[^\\\\])')|(?P<unescapedValue>.*))(\\|(('(?P<escapedDefault>.*[^\\\\])')|(?P<unescapedDefault>.*)))?[\\]\\s]#uUi";
        preg_match_all($expression, $rawChunk, $matches);
        foreach ($matches['paramName'] as $key => $paramName) {
            if (!empty($matches['escapedValue'][$key])) {
                $chunk[$paramName] = strval($matches['escapedValue'][$key]);
            }
            if (!empty($matches['unescapedValue'][$key])) {
                $chunk[$paramName] = floatval($matches['unescapedValue'][$key]);
            }
            if (!empty($matches['escapedDefault'][$key])) {
                $chunk[$paramName.'-default'] = strval($matches['escapedDefault'][$key]);
            }
            if (!empty($matches['unescapedDefault'][$key])) {
                $chunk[$paramName.'-default'] = floatval($matches['unescapedDefault'][$key]);
            }
        }
        return $chunk;
    }

    /**
     * Compiles single chunk
     * @param  {ContentBlock} $chunk     ContentBlock instance
     * @param  {array} $arguments Arguments for this chunk from original content
     * @return {string}            Result string ready for replacing
     */
    public static function compileChunk($chunk, $arguments) {
        $matches = [];
        $formatParams = null;
        preg_match_all('%['.static::$prefix.']{2}([\+\*\%])([^\]\[]+)['.static::$suffix.']{2}%ui', $chunk, $matches);
        foreach ($matches[2] as $k => $rawParam) {
            $token = $matches[1][$k];
            if (false === mb_strpos($rawParam, ':')) {
                $paramName = $rawParam; 
            } else {
                list($paramName, $formatParams) = explode(':', $rawParam);
            }
            switch ($token) {
                case '+':
                    if (array_key_exists($paramName, $arguments)) {
                        $replacement = static::applyFormatter($arguments[$paramName], $formatParams);
                        $chunk = str_replace($matches[0][$k], $replacement, $chunk);
                    } else if(array_key_exists($paramName.'-default', $arguments)) {
                        $replacement = static::applyFormatter($arguments[$paramName.'-default'], $formatParams);
                        $chunk = str_replace($matches[0][$k], $replacement, $chunk);
                    } else {
                        $chunk = str_replace($matches[0][$k], '', $chunk);
                    }
                    break;
                default:
                    $chunk = str_replace($matches[0][$k], '', $chunk);
            }
        }
        return $chunk;
    }

    /**
     * Find formatter declarations in chunk placeholders. if find trying to apply
     * yii\i18n\Formatter formats see yii\i18n\Formatter for details
     * @param {string} $rawParam single placeholder declaration from chunk
     * @param $rawFormat {string}
     * @return array
     */
    private static function applyFormatter($value, $rawFormat)
    {
        if (null === $rawFormat) {
            return $value;
        }
        $params = explode(',', $rawFormat);
        $method = array_shift($params);
        $formattedValue = call_user_func_array([Yii::$app->formatter, $method], [$value, $params]);
        return $formattedValue;
    }

    /**
     * Fetches single chunk by key from static var
     * if is no there - get it from db and push to static array
     * @param $key {string} Chunk key field
     * @return {string} Chunk value field
     */
    public static function fetchChunkByKey($key) {
        if (!array_key_exists($key, static::$chunksByKey)) {
            $chunkCacheKey = 'chunkCaheKey'.$key.ContentBlock::className();
            static::$chunksByKey[$key] = Yii::$app->cache->get($chunkCacheKey);
            if (false === static::$chunksByKey[$key]) {
                $chunk = ContentBlock::find()
                    ->where(['key' => $key])
                    ->asArray()
                    ->one();
                static::$chunksByKey[$key] = $chunk['value'];
                if (!empty(static::$chunksByKey[$key])) {
                    Yii::$app->cache->set(
                        $chunkCacheKey,
                        static::$chunksByKey[$key],
                        86400,
                        new TagDependency([
                            'tags' => [
                                ActiveRecordHelper::getCommonTag(ContentBlock::className()),
                            ]
                        ])
                    );
                }
            }
        }
        return static::$chunksByKey[$key];
    }

    /**
     * preloading chunks with preload option set to 1
     * and push it to static array
     * @return array|void
     */
    public static function preloadChunks() {
        if (!empty(static::$chunksByKey)) {
            return;
        }
        $cacheKey = 'chunksByKey'.ContentBlock::className();
        static::$chunksByKey = Yii::$app->cache->get($cacheKey);
        if (false === static::$chunksByKey) {
            $chunks = ContentBlock::find()
                ->where(['preload' => 1])
                ->asArray()
                ->all();
            static::$chunksByKey = ArrayHelper::map($chunks, 'key', 'value');
            if (!is_null(static::$chunksByKey)) {
                Yii::$app->cache->set(
                    $cacheKey,
                    static::$chunksByKey,
                    86400,
                    new TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(ContentBlock::className()),
                        ]
                    ])
                );
            }
        }
        return static::$chunksByKey;
    }
}