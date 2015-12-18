<?php

use app\modules\shop\models\FilterSets;
use yii\db\Migration;

class m150716_122316_multiple_filters extends Migration
{
    public function up()
    {
        $this->addColumn(
            FilterSets::tableName(),
            'multiple',
            'TINYINT UNSIGNED NOT NULL DEFAULT 0'
        );
    }

    public function down()
    {
        $this->dropTable(FilterSets::tableName());
    }
}
