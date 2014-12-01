<?php

namespace app\components\filters;

use app\components\filters\FilterQueryInterface;
use yii\db\ActiveQuery;

class DummyFilterQuery implements FilterQueryInterface
{
    public function filter(ActiveQuery $query)
    {
        return $query;
    }
}
