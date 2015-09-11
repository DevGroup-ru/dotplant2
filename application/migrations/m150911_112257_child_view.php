<?php

use app\models\ViewObject;
use yii\db\Migration;

class m150911_112257_child_view extends Migration
{
    public function up()
    {

        $this->addColumn(
            ViewObject::tableName(),
            'child_view_id',
            $this->integer()->notNull()->defaultValue(1)
        );

    }

    public function down()
    {
        $this->dropColumn(
            ViewObject::tableName(),
            'child_view_id'
        );
    }


}
