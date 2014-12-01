<?php

namespace app\components\filters;

use yii\db\ActiveQuery;

interface FilterQueryInterface
{
    /**
     * Performs filtration on $query object
     * @param ActiveQuery $query
     * @param $cacheKeyAppend String to append to cache key
     * @return ActiveQuery
     */
    public function filter(ActiveQuery $query, &$cacheKeyAppend);
}
