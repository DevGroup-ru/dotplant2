<?php

namespace app\components\filters;

use app;
use app\modules\shop\models\Product;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;


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
        $get = array_merge(Yii::$app->request->get(), Yii::$app->request->post());

        $isChange = (isset($get[$this->changeAttribute]) && $get[$this->changeAttribute] == 1 ) ? true : false;

        $min = floatval(
            ArrayHelper::getValue($get, $this->minAttribute, $this->minValue)
        );
        $max = floatval(
            ArrayHelper::getValue($get, $this->maxAttribute, $this->maxValue)
        );
        if ($min !== floatval($this->minValue) && $isChange) {
            $cacheKeyAppend .= "[MinPrice:$min]";
            $query = $query->andWhere(
                Product::tableName() . '.price >= :min_price',
                [':min_price'=>$min]
            );
        } else {
            ArrayHelper::remove($get, $this->minAttribute);
            ArrayHelper::remove($_GET, $this->minAttribute);
            Yii::$app->request->setQueryParams(
                $get
            );
        }

        if ($max !== floatval($this->maxValue) && $isChange) {
            $cacheKeyAppend .= "[MaxPrice:$max]";
            $query = $query->andWhere(
                Product::tableName() . '.price <= :max_price',
                [':max_price'=>$max]
            );
        } else {
            ArrayHelper::remove($get, $this->maxAttribute);
            ArrayHelper::remove($_GET, $this->maxAttribute);
            Yii::$app->request->setQueryParams(
                $get
            );
        }
        return $query;
    }
}