<?php

use yii\db\Schema;
use yii\db\Migration;

class m141216_084809_psv_dont_filter extends Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE {{%property_static_values}}
              ADD `dont_filter` TINYINT(1) NOT NULL DEFAULT 0;
        ");

    }

    public function down()
    {
        $this->execute("
            ALTER TABLE {{%property_static_values}}
              DROP `dont_filter`;
        ");

    }
}
