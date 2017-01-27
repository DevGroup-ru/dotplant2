<?php

use app\modules\seo\models\Counter;
use yii\db\Migration;

class m170127_063746_add_position_to_counter extends Migration
{
    public function up()
    {
        $this->addColumn(
            Counter::tableName(),
            "position",
            $this->integer(1)->defaultValue(0)
        );
    }

    public function down()
    {
        $this->dropColumn(
            Counter::tableName(),
            "position"
        );
    }
}
