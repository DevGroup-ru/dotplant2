<?php

use app\modules\shop\models\FilterSets;
use yii\db\Schema;
use yii\db\Migration;

class m150622_090046_filterSetSlider extends Migration
{
    public function up()
    {
        $this->addColumn(
            FilterSets::tableName(),
            'is_range_slider',
            Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0'
        );
    }

    public function down()
    {
        $this->dropColumn(
            FilterSets::tableName(),
            'is_range_slider'
        );
    }

}
