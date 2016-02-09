<?php

namespace app\modules\shop\models;

use app\modules\shop\models\CurrencyRateProvider;
use app\modules\shop\components\SpecialPriceProductInterface;
use devgroup\TagDependencyHelper\ActiveRecordHelper;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\data\ActiveDataProvider;

/**
 * This is the model class Multi-currency
 *
 * @property integer $id
 * @property string $name
 * @property string $iso_code
 * @property integer $is_main
 * @property double $convert_nominal
 * @property double $convert_rate
 * @property integer $sort_order
 * @property integer $intl_formatting
 * @property integer $min_fraction_digits
 * @property integer $max_fraction_digits
 * @property string $dec_point
 * @property string $thousands_sep
 * @property string $format_string
 * @property double $additional_rate
 * @property double $additional_nominal
 * @property integer $currency_rate_provider_id
 */
class Currency extends \yii\db\ActiveRecord
{
    private static $mainCurrency = null;
    private static $selection = null;
    private static $identity_map = [];
    private $formatter = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%currency}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_main', 'sort_order', 'intl_formatting', 'min_fraction_digits', 'max_fraction_digits', 'currency_rate_provider_id'], 'integer'],
            [['convert_nominal', 'convert_rate', 'additional_rate', 'additional_nominal'], 'number'],
            [['name', 'iso_code', 'dec_point', 'thousands_sep', 'format_string'], 'string', 'max' => 255]
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
            'iso_code' => Yii::t('app', 'ISO-4217 code'),
            'is_main' => Yii::t('app', 'Is main currency'),
            'convert_nominal' => Yii::t('app', 'Convert nominal'),
            'convert_rate' => Yii::t('app', 'Convert rate'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'intl_formatting' => Yii::t('app', 'Intl formatting with ICU'),
            'min_fraction_digits' => Yii::t('app', 'Min fraction digits'),
            'max_fraction_digits' => Yii::t('app', 'Max fraction digits'),
            'dec_point' => Yii::t('app', 'Decimal point'),
            'thousands_sep' => Yii::t('app', 'Thousands separator'),
            'format_string' => Yii::t('app', 'Format string'),
            'additional_rate' => Yii::t('app', 'Additional rate'),
            'additional_nominal' => Yii::t('app', 'Additional nominal'),
            'currency_rate_provider_id' => Yii::t('app', 'Currency rate provider'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \devgroup\TagDependencyHelper\ActiveRecordHelper::className(),
            ],
        ];
    }

    /**
     * Returns model instance by ID using IdentityMap
     * @param integer $id
     * @return Currency
     */
    public static function findById($id)
    {
        if (!isset(static::$identity_map[$id])) {
            static::$identity_map[$id] = Yii::$app->cache->get('Currency: ' . $id);
            if (static::$identity_map[$id] === false) {
                static::$identity_map[$id] = Currency::findOne($id);
                if (is_object(static::$identity_map[$id])) {
                    Yii::$app->cache->set(
                        'Currency: ' . $id,
                        static::$identity_map[$id],
                        86400,
                        new TagDependency(
                            [
                                'tags' => [
                                    \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(Currency::className(), $id),
                                ],
                            ]
                        )
                    );
                }
            }
        }
        return static::$identity_map[$id];
    }


    /**
     * Returns main currency object for this shop with static-cache
     *
     * @return Currency Main currency object
     */
    public static function getMainCurrency()
    {
        if (static::$mainCurrency === null) {
            static::$mainCurrency = Yii::$app->cache->get("MainCurrency");
            if (static::$mainCurrency === false) {
                static::$mainCurrency = Currency::find()
                    ->where(['is_main' => 1])
                    ->one();
                if (static::$mainCurrency !== null) {
                    static::$identity_map[static::$mainCurrency->id] = static::$mainCurrency;
                }
                Yii::$app->cache->set(
                    "MainCurrency",
                    static::$mainCurrency,
                    604800,
                    new TagDependency(
                        [
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getObjectTag(static::className(), static::$mainCurrency->id),
                            ],
                        ]
                    )
                );
            }
        }

        return static::$mainCurrency;
    }

    /**
     * Relation to CurrencyRateProvider model
     * @return \yii\db\ActiveQuery
     */
    public function getRateProvider()
    {
        return $this->hasOne(CurrencyRateProvider::className(), ['id' => 'currency_rate_provider_id']);
    }

    /**
     * Returns array(id=>name) of currencies for dropdown list
     * @return array
     * @throws InvalidConfigException
     */
    public static function getSelection()
    {
        if (static::$selection === null) {
            static::$selection = Yii::$app->cache->get('AllCurrenciesSelection');
            if (static::$selection === false) {

                $rows = Currency::find()
                    ->select(['id', 'name'])
                    ->orderBy(['is_main'=>SORT_DESC, 'sort_order' => SORT_ASC])
                    ->asArray()
                    ->all();
                static::$selection = ArrayHelper::map($rows, 'id', 'name');

                Yii::$app->cache->set(
                    "AllCurrenciesSelection",
                    static::$selection,
                    604800,
                    new TagDependency(
                        [
                            'tags' => [
                                \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className()),
                            ],
                        ]
                    )
                );
            }
        }
        return static::$selection;
    }

    /**
     * Search tasks
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
                    'pageSize' => 10,
                ],
            ]
        );
        if (!($this->load($params))) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'iso_code', $this->iso_code]);
        $query->andFilterWhere(['is_main' => $this->is_main]);
        $query->andFilterWhere(['currency_rate_provider_id' => $this->currency_rate_provider_id]);


        return $dataProvider;
    }

    /**
     * Returns \yii\i18n\Formatter instance for current Currency instance
     * @return \yii\i18n\Formatter
     * @throws InvalidConfigException
     */
    private function getFormatter()
    {
        if ($this->formatter === null) {
            $this->formatter = Yii::createObject([
                'class' => '\yii\i18n\Formatter',
                'currencyCode' => $this->iso_code,
                'decimalSeparator' => $this->dec_point,
                'thousandSeparator' => $this->thousands_sep,
                'numberFormatterOptions' => [
                    7 => $this->min_fraction_digits, // min
                    6 => $this->max_fraction_digits, // max
                ]
            ]);
        }
        return $this->formatter;
    }

    /**
     * Formats price with current currency settings
     * @param $price
     * @return string
     */
    public function format($price)
    {
        if ($this->intl_formatting === 1) {
            return $this->getFormatter()->asCurrency($price);
        } else {
            $number_value = $this->getFormatter()->asDecimal($price);
            return strtr($this->format_string, ['#'=>$number_value]);
        }
    }

    /**
     * Formats price with current currency settings but without format string
     * @param $price
     * @return float
     */
    public function formatWithoutFormatString($price)
    {
        $formatter = Yii::createObject([
            'class' => '\yii\i18n\Formatter',
            'currencyCode' => $this->iso_code,
            'decimalSeparator' => '.',
            'thousandSeparator' => '',
            'numberFormatterOptions' => [
                7 => $this->min_fraction_digits, // min
                6 => $this->max_fraction_digits, // max
            ]
        ]);
        return $formatter->asDecimal($price);
    }

    /**
     * Returns Currency instance by name
     * @param $name
     * @return array|mixed|null|\yii\db\ActiveRecord
     */
    public static function getByName($name)
    {
        // first search in identity map
        foreach (static::$identity_map as $id => $currency) {
            if ($currency->name === $name) {
                return $currency;
            }
        }
        // if not - find in db
        $currency = Yii::$app->cache->get('Currency:name:'.$name);
        if ($currency === false) {
            $currency = Currency::find()
                ->where(['name' => $name])
                ->one();
            Yii::$app->cache->set(
                'Currency:name:'.$name,
                $currency,
                86400,
                new TagDependency(
                    [
                        'tags' => [
                            \devgroup\TagDependencyHelper\ActiveRecordHelper::getCommonTag(static::className()),
                        ],
                    ]
                )
            );
        }
        if ($currency !== null) {
            // put to identity map
            static::$identity_map[$currency->id] = $currency;
        }
        return $currency;
    }

    /**
     * @return array all ISO codes from DB
     */
    public static function getIsoCodes()
    {
        $column = Yii::$app->cache->get('ISO:column');
        if ($column === false) {
            $column = array_column(static::find()->select(['iso_code'])->asArray()->all(), 'iso_code');
            Yii::$app->cache->set(
                'ISO:column',
                $column,
                86400,
                new TagDependency(['tags' => [ActiveRecordHelper::getCommonTag(static::className())]])
            );
        }
        return $column;
    }
}
