<?php

namespace app\extensions\DefaultTheme\models;

use app\extensions\DefaultTheme\components\VariationMatcher;
use app\traits\IdentityMap;
use Yii;
use \devgroup\TagDependencyHelper\ActiveRecordHelper;
use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;


/**
 * This is the model class for table "{{%theme_variation}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $by_url
 * @property string $by_route
 * @property string $matcher_class_name
 * @property integer $exclusive
 */
class ThemeVariation extends \yii\db\ActiveRecord
{
    use IdentityMap;
    public static $matchedVariations = null;

    /**
     * @var array|null Array representation of all ThemeVariation records in db
     */
    public static $allVariations = null;

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
        return '{{%theme_variation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['exclusive', 'omit_get_params'], 'integer'],
            [['name', 'by_url', 'by_route', 'matcher_class_name'], 'string', 'max' => 255]
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
            'by_url' => Yii::t('app', 'By Url'),
            'by_route' => Yii::t('app', 'By Route'),
            'matcher_class_name' => Yii::t('app', 'Matcher Class Name'),
            'exclusive' => Yii::t('app', 'Exclusive'),
            'omit_get_params' => Yii::t('app', 'Omit GET parameters'),
        ];
    }

    /**
     * Returns all db-stored variations in array representation
     *
     * @param bool $force True if you want to refresh static-variable cache
     * @return array
     */
    public static function getAllVariations($force = false)
    {
        if (static::$allVariations === null || $force === true) {
            $cacheKey = 'AllThemeVariations';

            static::$allVariations = Yii::$app->cache->get($cacheKey);
            if (static::$allVariations === false) {
                static::$allVariations = ThemeVariation::find()
                    ->orderBy(['exclusive' => SORT_DESC])
                    ->asArray()
                    ->all();
                Yii::$app->cache->set(
                    $cacheKey,
                    static::$allVariations,
                    86400,
                    new TagDependency([
                        'tags' => [
                            ActiveRecordHelper::getCommonTag(ThemeVariation::className()),
                        ]
                    ])
                );
            }
        }
        return static::$allVariations;
    }

    /**
     * Finds matching variations for current request
     * @todo Think about caching here!
     * @return array Array of matched variations
     */
    public static function getMatchedVariations()
    {
        if (static::$matchedVariations === null) {
            Yii::beginProfile('Get matched variations');
            $variations = static::getAllVariations();
            $route = Yii::$app->requestedRoute;
            $oldUri = $uri = Yii::$app->request->url;
            static::$matchedVariations = [];


            foreach ($variations as $variation) {
                $match = true;
                $uri = $oldUri;
                if ($variation['omit_get_params']) {
                    $uri = preg_replace('/\?.*$/Us', '', $oldUri);
                }
                // check by url
                if (!empty($variation['by_url'])) {
                    $asteriskPosition = mb_strpos($variation['by_url'], '*');
                    if ($asteriskPosition === false) {
                        // no *

                        // direct compare!
                        $match = $variation['by_url'] === $uri;


                    } elseif ($asteriskPosition === 0) {
                        // all pages
                        $match = true;
                    } elseif ($asteriskPosition === 1) {
                        // all non-main pages
                        $match = $uri !== '/';
                    } else {
                        $startsWith = mb_substr($variation['by_url'], 0, $asteriskPosition);
                        $match = mb_strpos($uri, $startsWith) === 0;
                    }
                }

                // check by route
                if (!empty($variation['by_route'])) {
                    $match = $match && ($route === $variation['by_route']);
                }

                // check by matcher
                if (!empty($variation['matcher_class_name'])) {
                    $className = $variation['matcher_class_name'];
                    /** @var VariationMatcher $matcher */
                    $matcher = new $className($variation);
                    $match = $match && $matcher->run();
                }


                if ($match === true) {
                    if ($variation['exclusive'] === '1') {
                        Yii::endProfile('Get matched variations');
                        return [$variation];
                    } else {
                        static::$matchedVariations[] = $variation;
                    }
                }
            }
            Yii::endProfile('Get matched variations');
        }
        return static::$matchedVariations;
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
                    'pageParam' => 'variation-page',
                    'pageSizeParam' => 'variation-per-page',
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['exclusive' => $this->exclusive]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'by_url', $this->by_url]);
        $query->andFilterWhere(['like', 'by_route', $this->by_route]);
        $query->andFilterWhere(['like', 'matcher_class_name', $this->matcher_class_name]);

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

        $activeWidgets = ThemeActiveWidgets::find()->where(['part_id' => $this->id])->all();
        foreach ($activeWidgets as $widget) {
            $widget->delete();
        }
        return true;

    }
}