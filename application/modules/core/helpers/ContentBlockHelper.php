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
    private static $chunksByKey = [];
    /**
     * Compiles content string by injecting chunks into content
     * @param  {string} $content     Original content with chunk calls
     * @param  {string} $content_key Key for caching compiled content version
     * @param  {yii\caching\Dependency} $dependency  Cache dependency
     * @return {string}              Compiled content with injected chunks
     */
    public static function compileContentString($content, $content_key, $dependency) {

        /*
            1. Find cached version, if exists return it
            2. Extract chunk calls from $content
            3. Fetch not-loaded chunks
            4. Replace chunk calls with result
            5. Put result to cache, adding all chunks as additional tags for tag dependency
         */

        $chunkBeginTag = '\[\[';
        $chunkEndTag = '\]\]';
        $matches = [];
        preg_match_all('%'.$chunkBeginTag.'([^\]\[]+)'.$chunkEndTag.'%ui', $content, $matches);
        if (!empty($matches)) {
            foreach ($matches[1] as $k => $rawChunk) {
                $chunkData = static::sanitizeChunk($rawChunk);
                $chunk = ContentBlock::findOne(['key' => $chunkData['key']]);
                if (null !== $chunk ) {
                    $replacement = static::compileChunk($chunk, $chunkData);
                    $content = str_replace($matches[0][$k], $replacement, $content);
                }
            }
        }
        return $content;
    }

    private static function sanitizeChunk($rawChunk) {
        $chunk = [];
        $key = substr($rawChunk, (strpos($rawChunk, '$')+1), (strpos($rawChunk, ' ')-1));
        $rawParams = substr($rawChunk, (strpos($rawChunk, $key)+strlen($key)));
        $rawParams = trim(str_replace('&amp;', '&', $rawParams));
        $rawParams = preg_replace('%[\s]+%', '', $rawParams);
        $chunk['key'] = $key;
        $params = explode('&', $rawParams);
        foreach ($params as $param) {
            if (empty($param)) continue;
            if (strpos($param, '|')) {
                list($paramName, $paramValues) = explode('=', $param);
                list($paramValue, $paramDefaultValue) = explode('|', $paramValues);
                    if (static::defineValueType($paramValue) !== '') {
                        $chunk[$paramName] = static::defineValueType($paramValue);
                    }
                    $chunk[$paramName.'-default'] = static::defineValueType($paramDefaultValue);
            } else {
                if(!strpos($param, '=')) continue;
                list($paramName, $paramValue) = explode('=', $param);
                $chunk[$paramName] = static::defineValueType($paramValue);
            }
        }
        return $chunk;
    }

    private static function defineValueType($value) {
        if (preg_match('%[\\\']%ui', $value)) {
            $value = preg_replace('%[^\w]%ui', '', $value);
        } else {
            $value = floatval($value);
        }
        return $value;
    }
    /**
     * Compiles single chunk
     * @param  {ContentBlock} $chunk     ContentBlock instance
     * @param  {array} $arguments Arguments for this chunk from original content
     * @return {string}            Result string ready for replacing
     */
    public static function compileChunk($chunk, $arguments) {
        $varBeginTag = '\[\[\+';
        $varndTag = '\]\]';
        $matches = [];
        preg_match_all('%'.$varBeginTag.'([^\]\[]+)'.$varndTag.'%i', $chunk->value, $matches);
        foreach ($matches[1] as $replace) {
            if (array_key_exists($replace, $arguments)) {
                $chunk->value = str_replace('[[+'.$replace.']]', $arguments[$replace], $chunk->value);
            } else if(array_key_exists($replace.'-default', $arguments)) {
                $chunk->value = str_replace('[[+'.$replace.']]', $arguments[$replace.'-default'], $chunk->value);
            } else {
                $chunk->value = str_replace('[[+'.$replace.']]', '', $chunk->value);
            }
        }
        return $chunk->value;
    }

    public static function fetchChunkByKey($key) {
        /*
            1. Find cached in static variable
            2. Find in app cache
            3. Fetch from db(asArray) if not cached
            4. Put to cache and static variable
         */
        if (!array_key_exists($key, static::$chunksByKey)) {
            $chunkCacheKey = 'chunkCaheKey'.$key.ContentBlock::className();
            static::$chunksByKey[$key] = Yii::$app->cache->get($chunkCacheKey);
            if (static::$chunksByKey[$key] === false ) {
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

    public static function preloadChunks() {
        /*
            Fetches chunk definitions from db(asArray) with preload flag into static array
            Called in compileContentString
            Doing nothing if static::$chunkByKey is not empty array
         */
        $cacheKey = 'chunksByKey'.ContentBlock::className();
        static::$chunksByKey = Yii::$app->cache->get($cacheKey);
        if ( static::$chunksByKey === false ) {
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