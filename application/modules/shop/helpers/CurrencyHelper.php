<?php
namespace app\modules\shop\helpers;

use app\modules\shop\models\Currency;

class CurrencyHelper
{
    /** @var Currency $currency */
    static protected $userCurrency = null;

    /**
     * @return Currency
     */
    static public function getUserCurrency()
    {
        return null === static::$userCurrency ? static::getMainCurrency() : static::$userCurrency;
    }

    /**
     * @param Currency $userCurrency
     */
    static public function setUserCurrency($userCurrency)
    {
        static::$userCurrency = $userCurrency;
    }

    /**
     * @return Currency
     */
    static public function getMainCurrency()
    {
        return Currency::getMainCurrency();
    }

    /**
     * @param string $code
     * @param bool|true $useMainCurrency
     * @return Currency
     */
    static public function findCurrencyByIso($code)
    {
        $currency = Currency::find()->where(['iso_code' => $code])->one();
        $currency = null === $currency ? static::getMainCurrency() : $currency;
        return $currency;
    }

    /**
     * @param float|int $input
     * @param Currency $from
     * @param Currency $to
     * @return float|int
     */
    static public function convertCurrencies($input = 0, Currency $from, Currency $to)
    {
        if (0 === $input) {
            return $input;
        }

        if ($from->id !== $to->id) {
            $main = static::getMainCurrency();
            if ($main->id === $from->id && $main->id !== $to->id) {
                $input = round($input / $to->convert_rate * $to->convert_nominal, 2);
            } elseif ($main->id !== $from->id && $main->id === $to->id) {
                $input = round($input / $from->convert_nominal * $from->convert_rate, 2);
            } else {
                $input = round($input / $from->convert_nominal * $from->convert_rate, 2);
                $input = round($input / $to->convert_rate * $to->convert_nominal, 2);
            }
        }

        return $input;
    }

    /**
     * @param float|int $input
     * @param Currency $from
     * @return float|int
     */
    static public function convertToUserCurrency($input = 0, Currency $from)
    {
        return static::convertCurrencies($input, $from, static::getUserCurrency());
    }

    /**
     * @param float|int $input
     * @param Currency $from
     * @return float|int
     */
    static public function convertToMainCurrency($input = 0, Currency $from)
    {
        return static::convertCurrencies($input, $from, static::getMainCurrency());
    }
}
