<?php

use yii\db\Schema;
use yii\db\Migration;

class m150319_143410_ratings extends Migration
{
    public function up()
    {
        $this->createTable(
            '{{%rating_item}}',
            [
                'id' => Schema::TYPE_PK,
                'name' => Schema::TYPE_STRING . ' NOT NULL',
                'rating_group' => Schema::TYPE_STRING . ' NOT NULL',
                'min_value' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'max_value' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 5',
                'step_value' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 1',
                'require_review' => Schema::TYPE_BOOLEAN . ' DEFAULT 0',
            ]
        );

        $this->createTable(
            '{{%rating_values}}',
            [
                'id' => Schema::TYPE_PK,
                'rating_id' => Schema::TYPE_STRING . ' NOT NULL',
                'object_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'object_model_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'rating_item_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'value' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'user_id' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
                'date' => Schema::TYPE_DATETIME . ' NOT NULL',
            ]
        );

        $this->addColumn('{{%review}}', 'rating_id', Schema::TYPE_STRING);
    }

    public function down()
    {
        $this->dropTable('{{%rating_item}}');
        $this->dropTable('{{%rating_values}}');

        $this->dropColumn('{{%review}}', 'rating_id');

        return true;
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
