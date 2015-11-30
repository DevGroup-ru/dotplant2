<?php

use yii\db\Expression;
use yii\db\Migration;

class m151127_084444_heartbeat extends Migration
{
    public function up()
    {
        mb_internal_encoding("UTF-8");
        $tableOptions = $this->db->driverName === 'mysql' ? 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB' : null;
        $this->createTable(
            '{{%user_activity}}',
            [
                'id' => $this->primaryKey(),
                'object_id' => $this->integer()->notNull(),
                'object_model_id' => $this->integer()->notNull(),
                'user_id' => $this->integer()->notNull(),
                'is_main' => $this->integer(3)->notNull(),
                'last_heartbeat' => $this->timestamp() . new Expression(' DEFAULT CURRENT_TIMESTAMP'),
                // https://github.com/yiisoft/yii2/issues/9337 fixed in 2.0.7
            ],
            $tableOptions
        );
    }

    public function down()
    {
        $this->dropTable('{{%user_activity}}');
    }

}
