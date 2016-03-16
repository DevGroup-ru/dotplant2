<?php

namespace app\components\filters;

use app;
use app\modules\shop\models\Product;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use app\modules\shop\models\Currency;


class ProductPriceRangeFilter implements FilterQueryInterface
{
    public $minAttribute = 'price_min';
    public $maxAttribute = 'price_max';
    public $changeAttribute = 'price_change_flag';

    public $minValue = 0;
    public $maxValue = 9999999;
    /**
     * @param ActiveQuery $query
     * @return ActiveQuery
     */
    public function filter(ActiveQuery $query, &$cacheKeyAppend)
    {
        $get = Yii::$app->request->get();
        $params = array_merge($get, Yii::$app->request->post());
        $min = floatval(
            ArrayHelper::getValue($params, $this->minAttribute, $this->minValue)
        );
        $max = floatval(
            ArrayHelper::getValue($params, $this->maxAttribute, $this->maxValue)
        );
        $joinFlag = false;
        if ($min !== floatval($this->minValue)) {
            $cacheKeyAppend .= "[MinPrice:$min]";
            $query = $query->andWhere(
                Product::tableName() . '.price >=  FLOOR (:min_price * currency.convert_nominal / currency.convert_rate * POW(10, currency.max_fraction_digits)) / POW(10, currency.max_fraction_digits)',
                [':min_price' => $min]
            );
            $get[$this->minAttribute] = $min;
            $joinFlag = true;
        }
        if ($max !== floatval($this->maxValue) && (double) 0 !== floatval($max)) {
            $cacheKeyAppend .= "[MaxPrice:$max]";
            $query = $query->andWhere(
                Product::tableName() . '.price <= CEILING (:max_price * currency.convert_nominal / currency.convert_rate * POW(10, currency.max_fraction_digits)) / POW(10, currency.max_fraction_digits)',
                [':max_price' => $max]
            );
            $get[$this->maxAttribute] = $max;
            $joinFlag = true;
        }
        if ($joinFlag) {
            $query = $query->leftJoin(Currency::tableName() . ' ON currency.id = product.currency_id');
        }
        Yii::$app->request->setQueryParams($get);
        return $query;
    }
}
