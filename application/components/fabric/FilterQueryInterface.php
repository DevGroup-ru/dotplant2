<?php

namespace app\components\fabric;

use yii\db\ActiveQuery;

interface FilterQueryInterface
{
    public function filter(ActiveQuery $query);
}
