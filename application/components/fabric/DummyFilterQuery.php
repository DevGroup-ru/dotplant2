<?php

namespace app\components\fabric;

use yii\db\ActiveQuery;

class DummyFilterQuery implements FilterQueryInterface
{
    public function filter(ActiveQuery $query)
    {
        return $query;
    }
}
