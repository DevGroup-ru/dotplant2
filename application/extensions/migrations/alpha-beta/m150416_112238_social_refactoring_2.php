<?php

use yii\db\Schema;
use yii\db\Migration;

class m150416_112238_social_refactoring_2 extends Migration
{
    public function up()
    {
        $this->execute(
            "UPDATE {{%object}} SET object_class=:class_name WHERE name=:name",
            [
                ':class_name' => 'app\modules\user\models\User',
                ':name' => 'User',
            ]
        );
    }

    public function down()
    {
        $this->execute(
            "UPDATE {{%object}} SET object_class=:class_name WHERE name=:name",
            [
                ':class_name' => 'app\models\User',
                ':name' => 'User',
            ]
        );
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
