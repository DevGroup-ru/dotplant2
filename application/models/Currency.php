<?php

namespace app\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;

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
            'iso_code' => Yii::t('app', 'Iso Code'),
            'is_main' => Yii::t('app', 'Is Main'),
            'convert_nominal' => Yii::t('app', 'Convert Nominal'),
            'convert_rate' => Yii::t('app', 'Convert Rate'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'intl_formatting' => Yii::t('app', 'Intl Formatting'),
            'min_fraction_digits' => Yii::t('app', 'Min Fraction Digits'),
            'max_fraction_digits' => Yii::t('app', 'Max Fraction Digits'),
            'dec_point' => Yii::t('app', 'Dec Point'),
            'thousands_sep' => Yii::t('app', 'Thousands Sep'),
            'format_string' => Yii::t('app', 'Format String'),
            'additional_rate' => Yii::t('app', 'Additional Rate'),
            'additional_nominal' => Yii::t('app', 'Additional Nominal'),
            'currency_rate_provider_id' => Yii::t('app', 'Currency Rate Provider ID'),
        ];
    }

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
}
