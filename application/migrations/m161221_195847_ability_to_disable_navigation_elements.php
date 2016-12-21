<?php

use yii\db\Migration;

class m161221_195847_ability_to_disable_navigation_elements extends Migration
{
    public function up()
    {
        $this->addColumn(
            \app\widgets\navigation\models\Navigation::tableName(),
            "active",
            self::boolean()->notNull()->defaultValue(true)
        );
    }

    public function down()
    {
        $this->dropColumn(
            \app\widgets\navigation\models\Navigation::tableName(),
            "active"
        );
    }
}
