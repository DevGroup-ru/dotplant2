<?php

use yii\db\Schema;
use yii\db\Migration;

class m141125_124907_background_tasks_status_fix extends Migration
{
    public function up()
    {
        $this->execute("ALTER TABLE {{%backgroundtasks_task}} CHANGE `status` `status` ENUM('ACTIVE','STOPPED','RUNNING','FAILED','COMPLETED','PROCESS') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'ACTIVE';");
        $this->execute("ALTER TABLE {{%backgroundtasks_task}} CHANGE `fall_counter` `fail_counter` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0';");
    }

    public function down()
    {
        $this->execute("ALTER TABLE {{%backgroundtasks_task}} CHANGE `status` `status` ENUM('ACTIVE','STOPPED','RUNNING','FAILED','COMPLETED') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'ACTIVE';");
        $this->execute("ALTER TABLE {{%backgroundtasks_task}} CHANGE `fail_counter` `fall_counter` TINYINT(3) UNSIGNED NOT NULL DEFAULT '0';");
    }
}
