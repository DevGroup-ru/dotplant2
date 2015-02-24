<?php

namespace app\models;

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
}
