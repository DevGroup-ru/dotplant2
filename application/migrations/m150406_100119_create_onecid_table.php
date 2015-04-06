<?php

use app\data\models\OnecId;
use yii\db\Schema;
use yii\db\Migration;

class m150406_100119_create_onecid_table extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        // Schemes
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        $this->createTable(
                OnecId::tableName(),
                [
                        'id' => 'INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT',
                        'onec' => 'CHAR(36) NOT NULL',
                        'inner_id' => 'INT UNSIGNED DEFAULT \'0\'',
                        'entity_id' => 'VARCHAR(20) NOT NULL DEFAULT \'new\'',
                        'UNIQUE KEY `onec` (`onec`)',
                ],
                $tableOptions
        );
        

    }

    public function down()
    {
        $this->dropTable('onec_id');
        return false;
    }
    
    
}
