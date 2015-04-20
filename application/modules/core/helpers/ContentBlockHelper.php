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
    private static $props = [];
    const EQUAL_REPLACER = '*&^fawheg76^4e5WcEE';

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
            foreach ($matches[1] as $k => $rawChunk) {
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
        $key = substr($rawChunk, (strpos($rawChunk, '$')+1), (strpos($rawChunk, ' ')-1));
        $rawParams = substr($rawChunk, (strpos($rawChunk, $key)+strlen($key)));
        $chunk['key'] = $key;
        $matches = [];
        $rawParams = preg_replace('%[\s]+%u', ' ', $rawParams);
        preg_match_all('%\'[^\'\|]+\'%ui', $rawParams, $matches);
        foreach ($matches[0] as $k => $v) {
            $str = str_replace('=', self::EQUAL_REPLACER, $v);
            $rawParams = str_replace($v, $str, $rawParams);
        }
        $params = static::getParam($rawParams);
        foreach ($params as $param) {
            if (empty($param)) continue;
            if (false !== strpos($param, '|')) {
                list($paramValues, $paramDefaultValue) = explode('|', $param);
                if (false === strpos($paramValues, '=')) {
                    $paramName = $paramValues;
                    $paramValue = '';
                } else {
                    list($paramName, $paramValue) = explode('=', $paramValues);
                }
                if (!empty($paramValue) && static::defineValueType($paramValue) !== '') {
                    $chunk[$paramName] = static::defineValueType($paramValue);
                }
                $chunk[$paramName.'-default'] = static::defineValueType($paramDefaultValue);
            } else {
                if (false === strpos($param, '=')) continue;
                list($paramName, $paramValue) = explode('=', $param);
                $chunk[$paramName] = static::defineValueType($paramValue);
            }
        }
        return $chunk;
    }

    /**
     * process syntax analysis of given string.
     * Extracts possibly param definitions and returns params array
     * @param $rawParams
     * @var $equalpos
     * @var $nextequal
     * @return array
     */
    private static function getParam($rawParams) {
        $equalpos = mb_stripos($rawParams, '=');
        if (false === $equalpos) {
            return [];
        }
        $nextequal = mb_stripos($rawParams, '=', $equalpos+1);
        if (false === $nextequal) {
            $st = mb_strrpos($rawParams, ' ', -strlen(mb_substr($rawParams, $equalpos)));
        } else {
            $st = mb_strrpos($rawParams, ' ', -strlen(mb_substr($rawParams, $nextequal)));
        }
        if (false === $st) {
            static::$props[] = trim($rawParams);
        } else {
            $param = mb_substr($rawParams, 0, $st);
            static::$props[] = trim($param);
            $rawParams = trim(mb_substr($rawParams, mb_strlen($param)));
            if (!empty($rawParams)) {
                static::getParam($rawParams);
            }
        }
        return static::$props;
    }

    /**
     * @param $value {string} value string extracted from chunk call
     * if value vas defined with ' - its string, otherwise - float.
     * @return float|string
     */
    private static function defineValueType($value) {
        if (preg_match('%[\\\']%ui', $value)) {
            $value = trim($value, "\\ '");
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
        $matches = [];
        $formatParams = null;
        preg_match_all('%['.static::$prefix.']{2}([\+\*\%])([^\]\[]+)['.static::$suffix.']{2}%ui', $chunk, $matches);
        foreach ($matches[2] as $k => $rawParam) {
            $token = $matches[1][$k];
            if (false === strpos($rawParam, ':')) {
                $paramName = $rawParam; 
            } else {
                list($paramName, $formatParams) = explode(':', $rawParam);
            }
            switch ($token) {
                case '+':
                    if (array_key_exists($paramName, $arguments)) {
                        $argumentValue = str_replace(self::EQUAL_REPLACER, '=', $arguments[$paramName]);
                        $replacement = static::applyFormatter($argumentValue, $formatParams);
                        $chunk = str_replace($matches[0][$k], $replacement, $chunk);
                    } else if(array_key_exists($paramName.'-default', $arguments)) {
                        $argumentValue = str_replace(self::EQUAL_REPLACER, '=', $arguments[$paramName.'-default']);
                        $replacement = static::applyFormatter($argumentValue, $formatParams);
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