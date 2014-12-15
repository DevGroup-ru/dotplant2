<?php

use yii\db\Schema;
use yii\db\Migration;

class m141215_091048_property_dont_filter extends Migration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE {{%property}}
              ADD `dont_filter` TINYINT(1) NOT NULL DEFAULT 0;
        ");
        
    }

    public function down()
    {
        $this->execute("
            ALTER TABLE {{%property}}
              DROP `dont_filter`;
        ");
        
    }
}
