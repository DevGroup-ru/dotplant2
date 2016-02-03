<?php
namespace app\modules\core\helpers;

use app;
use app\models\Property;
use app\models\PropertyStaticValues;
use app\modules\core\models\ContentBlock;
use app\modules\page\models\Page;
use app\modules\shop\models\Category;
use app\modules\shop\models\Product;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii;

/**
 * Class ContentBlockHelper
 * Main public static method compileContentString() uses submethods to extract chunk calls from model content field,
 * fetch chunks from data base table, then compile it and replace chunk calls with compiled chunks data
 * Example chunk call in model content field should be like: [[$chunk param='value'|'default value' param2=42]].
 * Chunk declaration should be like : <p>String: [[+param]]</p> <p>Float: [[+param2:format, param1, param2]]</p>
 * All supported formats you can find at Yii::$app->formatter
 *
 * @package app\modules\core\helpers
 */
class ContentBlockHelper
{
    private static $chunksByKey = [];

    /**
     * Compiles content string by injecting chunks into content
     * Preloads chunks which have preload = 1
     *
     * @param  string $content Original content with chunk calls
     * @param  string $content_key Key for caching compiled content version
     * @param  yii\caching\Dependency $dependency Cache dependency
     * @return string Compiled content with injected chunks
     */
    public static function compileContentString($content, $content_key, $dependency)
    {
        self::preloadChunks();
        $output = self::processData($content, $content_key, $dependency);
        $output = self::processData($output, $content_key, $dependency, '', false);
        return $output;
    }

    /**
     * Finding chunk calls with regexp
     * Iterate matches
     * While iterating:
     * Extracts single chunk data with sanitizeChunk() method
     * Fetches chunk by key using fetchChunkByKey(), who returns chunk value by key from static array if exists, otherwise from db
     * Compiles single chunk using compileChunk() method
     * Replaces single chunk call with compiled chunk data in the model content
     *
     * @param  string $content_key Key for caching compiled content version
     * @param  yii\caching\Dependency $dependency Cache dependency
     * @param  bool $preprocess flag to separate rendering non cacheable chunks such as Form
     * @param  string $content
     * @param  string $chunk_key ContentBlock key string to prevent endless recursion
     * @return string
     */
    private static function processData($content, $content_key, $dependency, $chunk_key = '', $preprocess = true)
    {
        $matches = [];
        $replacement = '';
        preg_match_all('%\[\[([^\]\[]+)\]\]%ui', $content, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $k => $rawChunk) {
                $chunkData = self::sanitizeChunk($rawChunk);
                if ($chunkData['key'] == $chunk_key) {
                    $content = str_replace($matches[0][$k], '', $content);
                    continue;
                }
                $cacheKey = $content_key . $chunkData['key'] . serialize($chunkData);
                switch ($chunkData['token']) {
                    case '$':
                        if ($preprocess === false) break;
                        $chunk = self::fetchChunkByKey($chunkData['key']);
                        $replacement = Yii::$app->cache->get($cacheKey);
                        if ($replacement === false) {
                            $replacement = static::compileChunk($chunk, $chunkData, $chunkData['key'], $content_key, $dependency);
                            Yii::$app->cache->set(
                                $cacheKey,
                                $replacement,
                                84600,
                                $dependency
                            );
                        }
                        break;
                    case '%':
                        if ($preprocess === true) {
                            $replacement = $rawChunk;
                            break;
                        }
                        $replacement = static::replaceForms($chunkData);
                        break;
                    case '~' :
                        if ($preprocess === false) break;
                        $replacement = Yii::$app->cache->get($cacheKey);
                        if ($replacement === false) {
                            $replacement = static::renderUrl($chunkData, $dependency);
                            Yii::$app->cache->set(
                                $cacheKey,
                                $replacement,
                                84600,
                                $dependency
                            );
                        }
                        break;
                    case '*':
                        if ($preprocess === false) {
                            break;
                        }
                        $replacement = Yii::$app->cache->get($cacheKey);
                        if ($replacement === false) {
                            $replacement = static::renderProducts($chunkData, $dependency);
                            Yii::$app->cache->set(
                                $cacheKey,
                                $replacement,
                                84600,
                                $dependency
                            );
                        }
                        break;
                }
                $content = str_replace($matches[0][$k], $replacement, $content);
            }
        }
        return $content;
    }

    /**
     * @param array $chunkData
     * @return mixed
     */
    private static function replaceForms($chunkData)
    {
        $regexp = '/^(?P<formId>\d+)(#(?P<id>[\w\d\-_]+))?(;(?P<isModal>isModal))?$/Usi';
        return preg_replace_callback(
            $regexp,
            function ($matches) {
                if (isset($matches['formId'])) {
                    $params = ['formId' => intval($matches['formId'])];
                    if (isset($matches['id'])) {
                        $params['id'] = $matches['id'];
                    }
                    if (isset($matches['isModal'])) {
                        $params['isModal'] = true;
                    }
                    return app\widgets\form\Form::widget($params);
                }
                return '';
            },
            $chunkData['key']
        );
    }

    /**
     * renders url according to given data
     * @param $chunkData
     * @param TagDependency $dependency
     * @return string
     */
    private static function renderUrl($chunkData, &$dependency)
    {
        $expression = '%(?P<objectName>[^#]+?)#(?P<objectId>[\d]+?)$%';
        $output = '';
        preg_match($expression, $chunkData['key'], $m);
        if (true === isset($m['objectName'], $m['objectId'])) {
            $id = (int)$m['objectId'];
            switch (strtolower($m['objectName'])) {
                case "page" :
                    if (null !== $model = Page::findById($id)) {
                        $dependency->tags [] = ActiveRecordHelper::getCommonTag(Page::className());
                        $dependency->tags [] = $model->objectTag();
                        $output = Url::to(['@article', 'id' => $id]);
                    }
                    break;
                case "category" :
                    if (null !== $model = Category::findById($id)) {
                        $dependency->tags [] = ActiveRecordHelper::getCommonTag(Category::className());
                        $dependency->tags [] = $model->objectTag();
                        $output = Url::to(
                            [
                                '@category',
                                'last_category_id' => $id,
                                'category_group_id' => $model->category_group_id
                            ]
                        );
                    }
                    break;
                case "product" :
                    if (null !== $model = app\modules\shop\models\Product::findById($id)) {
                        $dependency->tags [] = ActiveRecordHelper::getCommonTag(Product::className());
                        $dependency->tags [] = $model->objectTag();
                        $output = Url::to(
                            [
                                '@product',
                                'model' => $model,
                                'category_group_id' => $model->getMainCategory()->category_group_id
                            ]
                        );
                    }
                    break;
            }
        }
        return $output;
    }

    /**
     * Extracts chunk data from chunk call
     * uses regexp to extract param data from placeholder
     * [[$chunk <paramName>='<escapedValue>'|'<escapedDefault>' <paramName>=<unescapedValue>|<unescapedDefault>]]
     * iterate matches.
     * While iterating converts escapedValue and escapedDefault into string, unescapedValue and unescapedDefault - into float
     * Returns chunk data array like:
     *  [
     *      'key' => 'chunkKey',
     *      'firstParam'=> 'string value',
     *      'firstParam-default'=> 'default string value',
     *      'secondParam'=> float value,
     *      'secondParam-default'=> default float value,
     *  ]
     *
     * @param string $rawChunk
     * @return array
     */
    private static function sanitizeChunk($rawChunk)
    {
        $chunk = [];
        preg_match('%(?P<chunkToken>[^\w\[]?)([^\s\]\[]+)[\s\]]%', $rawChunk, $keyMatches);
        $chunk['token'] = $keyMatches['chunkToken'];
        $chunk['key'] = $keyMatches[2];
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
                $chunk[$paramName . '-default'] = strval($matches['escapedDefault'][$key]);
            }
            if (!empty($matches['unescapedDefault'][$key])) {
                $chunk[$paramName . '-default'] = floatval($matches['unescapedDefault'][$key]);
            }
        }
        return $chunk;
    }

    /**
     * Compiles single chunk
     * uses regexp to find placeholders and extract it's data from chunk value field
     * [[<token><paramName>:<format><params>]]
     * token switch is for future functionality increase
     * now method only recognizes + token and replaces following param with according $arguments array data
     * applies formatter according previously defined param values type if needed
     * if param name from placeholder was not found in arguments array, placeholder in the compiled chunk will be replaced with empty string
     * returns compiled chunk
     *
     * @param  string $content_key Key for caching compiled content version
     * @param  yii\caching\Dependency $dependency Cache dependency
     * @param  string $chunk ContentBlock instance
     * @param  array $arguments Arguments for this chunk from original content
     * @param  string $key ContentBlock key string to prevent endless recursion
     * @return string Result string ready for replacing
     */
    private static function compileChunk($chunk, $arguments, $key, $content_key, $dependency)
    {
        $matches = [];
        preg_match_all('%\[\[(?P<token>[\+\*])(?P<paramName>[^\s\:\]]+)\:?(?P<format>[^\,\]]+)?\,?(?P<params>[^\]]+)?\]\]%ui', $chunk, $matches);
        foreach ($matches[0] as $k => $rawParam) {
            $token = $matches['token'][$k];
            $paramName = trim($matches['paramName'][$k]);
            $format = trim($matches['format'][$k]);
            $params = preg_replace('%[\s]%', '', $matches['params'][$k]);
            $params = explode(',', $params);
            switch ($token) {
                case '+':
                    if (array_key_exists($paramName, $arguments)) {
                        $replacement = static::applyFormatter($arguments[$paramName], $format, $params);
                        $chunk = str_replace($matches[0][$k], $replacement, $chunk);
                    } else if (array_key_exists($paramName . '-default', $arguments)) {
                        $replacement = static::applyFormatter($arguments[$paramName . '-default'], $format, $params);
                        $chunk = str_replace($matches[0][$k], $replacement, $chunk);
                    } else {
                        $chunk = str_replace($matches[0][$k], '', $chunk);
                    }
                    break;
                default:
                    $chunk = str_replace($matches[0][$k], '', $chunk);
            }
        }
        return self::processData($chunk, $content_key, $dependency, $key);
    }

    /**
     * Find formatter declarations in chunk placeholders. if find trying to apply
     * yii\i18n\Formatter formats see yii\i18n\Formatter for details
     *
     * @param string $value single placeholder declaration from chunk
     * @param string $format
     * @param array $params
     * @return string|array
     */
    private static function applyFormatter($value, $format, $params)
    {
        if (false === method_exists(Yii::$app->formatter, $format) || empty($format)) {
            return $value;
        }
        array_unshift($params, $value);
        try {
            $formattedValue = call_user_func_array([Yii::$app->formatter, $format], $params);
        } catch (\Exception $e) {
            $formattedValue = $value;
        }
        return $formattedValue;
    }

    /**
     * Fetches single chunk by key from static var
     * if is no there - get it from db and push to static array
     *
     * @param $key string Chunk key field
     * @return string Chunk value field
     */
    private static function fetchChunkByKey($key)
    {
        if (!array_key_exists($key, static::$chunksByKey)) {
            $dependency = new TagDependency([
                'tags' => [
                    ActiveRecordHelper::getCommonTag(ContentBlock::className()),
                ]
            ]);
            static::$chunksByKey[$key] = ContentBlock::getDb()->cache(function ($db) use ($key) {
                $chunk = ContentBlock::find()
                    ->where(['key' => $key])
                    ->asArray()
                    ->one();
                return static::$chunksByKey[$key] = $chunk['value'];
            }, 86400, $dependency);
        }
        return static::$chunksByKey[$key];
    }

    /**
     * preloads chunks with option preload  = 1
     * and push it to static array
     *
     * @return array|void
     */
    private static function preloadChunks()
    {
        if (is_null(static::$chunksByKey)) {
            $dependency = new TagDependency([
                'tags' => [
                    ActiveRecordHelper::getCommonTag(ContentBlock::className()),
                ]
            ]);
            static::$chunksByKey = ContentBlock::getDb()->cache(function ($db) {
                $chunks = ContentBlock::find()
                    ->where(['preload' => 1])
                    ->asArray()
                    ->all();
                return ArrayHelper::map($chunks, 'key', 'value');
            }, 86400, $dependency);
        }
        return static::$chunksByKey;
    }

    /**
     * renders chunks in template files
     * @param string $key ContentBlock key
     * @param array $params . Array of params to be replaced while render
     * @param yii\base\Model $model . Caller model instance to use in caching
     * @return mixed
     */
    public static function getChunk($key, $params = [], yii\base\Model $model = null)
    {
        if (null === $rawChunk = self::fetchChunkByKey($key)) {
            return '';
        }
        $tags = [
            ActiveRecordHelper::getCommonTag(app\modules\core\models\ContentBlock::className()),
        ];
        $content_key = 'templateChunkRender' . $key;
        if (null !== $model) {
            $content_key .= $model->id;
            $tags[] = ActiveRecordHelper::getObjectTag(get_class($model), $model->id);
        }
        $dependency = new TagDependency(['tags' => $tags]);
        if (false === empty($params)) {
            $rawChunk = self::compileChunk($rawChunk, $params, $key, $content_key, $dependency);
        }
        return self::compileContentString($rawChunk, $content_key, $dependency);
    }

    /**
     * renders product item and list.
     * possible can render all objects, but need for few logic change
     * @param array $chunkData params for select and render
     * @param TagDependency $dependency
     * @return mixed
     */
    private static function renderProducts($chunkData, &$dependency)
    {
        $params = [
            'itemView' => Yii::$app->getModule('shop')->itemView,
            'type' => 'show',
            'object' => 'product',
            'where' => [],
            'limit' => 0,
            'listView' => Yii::$app->getModule('shop')->listView,
        ];
        switch ($chunkData['key']) {
            case 'product':
                if (ArrayHelper::keyExists('sku', $chunkData)) {
                    $params['where'] = ['sku' => $chunkData['sku']];
                }
                break;
            case 'productList':
                $params['type'] = 'list';
                break;
            default:
                $expression = '%(?P<objectName>[^#]+?)#(?P<objectId>[\d]+?)$%';
                if (preg_match($expression, $chunkData['key'], $matches)) {
                    $params['where']['id'] = $matches['objectId'];
                }
                break;
        }
        switch ($params['object']) {
            case 'product':
                $dependency->tags[] = ActiveRecordHelper::getCommonTag(Product::className());
                $query = Product::find();
                if (!empty($chunkData['categoryId'])) {
                    $query->leftJoin(
                        '{{%product_category}}',
                        Product::tableName() . '.id = {{%product_category}}.object_model_id'
                    )->andWhere(['{{%product_category}}.category_id' => $chunkData['categoryId']]);
                    $dependency->tags[] = ActiveRecordHelper::getCommonTag(Category::className());
                    $dependency->tags[] = ActiveRecordHelper::getObjectTag(
                        Category::className(),
                        $chunkData['categoryId']
                    );
                }
                if (!empty($chunkData['property'])) {
                    $expression = '%(?P<propertyKey>[^:]+?):(?P<propertyValue>.+?)$%';
                    if (preg_match($expression, $chunkData['property'], $matches)) {
                        $property = Property::findOne(['key' => $matches['propertyKey']]);
                        if (!is_null($property)) {
                            /** @var Property $property */
                            $dependency->tags[] = ActiveRecordHelper::getCommonTag(Property::className());
                            $dependency->tags[] = $property->objectTag();
                            if ($property->is_eav == 1) {
                                $query->leftJoin(
                                    '{{%product_eav}}',
                                    Product::tableName() . '.id = {{%product_eav}}.object_model_id'
                                )->andWhere(
                                    [
                                        '{{%product_eav}}.key' => $matches['propertyKey'],
                                        '{{%product_eav}}.value' => $matches['propertyValue']
                                    ]
                                );
                            } elseif ($property->has_static_values == 1) {
                                $psv = PropertyStaticValues::findOne(
                                    [
                                        'property_id' => $property->id,
                                        'value' => $matches['propertyValue']
                                    ]
                                );
                                if (!is_null($psv)) {
                                    $dependency->tags[] = ActiveRecordHelper::getCommonTag(
                                        PropertyStaticValues::className()
                                    );
                                    $dependency->tags[] = $psv->objectTag();
                                    $query->leftJoin(
                                        '{{%object_static_values}}',
                                        Product::tableName() . '.id = {{%object_static_values}}.object_model_id'
                                    )->andWhere(
                                        [
                                            'object_id' => 3,
                                            '{{%object_static_values}}.property_static_value_id' => $psv->id,
                                        ]
                                    );
                                } else {
                                    return '';
                                }
                            }
                            /** @todo add column_stored */
                        } else {
                            return '';
                        }
                    }
                }
                break;
            default:
                $query = Product::find();
                break;
        }
        $params = ArrayHelper::merge($params, array_intersect_key($chunkData, $params));
        if (!empty($params['where'])) {
            $query->andWhere($params['where']);
        }
        if (!empty($params['limit'])) {
            $query->limit($params['limit']);
        }

        if ($params['type'] === 'list') {
            $view = $params['listView'];
            $objects = $query->all();
            foreach ($objects as $object) {
                $dependency->tags[] = $object->objectTag();
            }
            switch ($params['object']) {
                case 'product':
                    $viewParams = ['products' => $objects];
                    break;
                default:
                    $viewParams = ['products' => $objects];
                    break;
            }
        } else {
            $view = $params['itemView'];
            $object = $query->one();
            if (is_null($object)) {
                return '';
            }
            $dependency->tags[] = $object->objectTag();
            switch ($params['object']) {
                case 'product':
                    $viewParams = [
                        'product' => $object,
                        'url' => Url::to(
                            [
                                '@product',
                                'model' => $object,
                                'category_group_id' => $object->getMainCategory()->category_group_id,
                            ]
                        )
                    ];
                    break;
                default:
                    $viewParams = [
                        'product' => $object,
                        'url' => Url::to(
                            [
                                '@product',
                                'model' => $object,
                                'category_group_id' => $object->getMainCategory()->category_group_id,
                            ]
                        )
                    ];
                    break;
            }
        }
        return Yii::$app->view->render($view, $viewParams);
    }
}
