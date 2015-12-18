<?php

use app\models\PropertyStaticValues;
use yii\db\Migration;

class m151218_203419_property_title_prepend extends Migration
{
    public function up()
    {
        $this->addColumn(
            PropertyStaticValues::tableName(),
            "title_prepend",
            $this->smallInteger(1)->notNull()->defaultValue(0)
        );
    }

    public function down()
    {
        $this->dropColumn(
            PropertyStaticValues::tableName(),
            "title_prepend"
        );
    }
}
