<?php

use yii\db\Migration;

class m151215_114531_content_block_groups extends Migration
{
    public function up()
    {

        $this->createTable(
            '{{%content_block_group%}}',
            [
                'id' => $this->primaryKey(),
                'parent_id' => $this->integer(10)->notNull()->defaultValue(1),
                'name' => $this->string(250)->notNull(),
                'sort_order' => $this->integer()->notNull()->defaultValue(0)
            ]
        );

        $this->insert(
            '{{%content_block_group%}}',
            [
                'parent_id' => 0,
                'name' => 'root',
                'sort_order' => 0
            ]
        );

        $groupID = $this->db->lastInsertID;

        $this->addColumn(
            '{{%content_block}}',
            'group_id',
            $this->integer(2)->notNull()->defaultValue($groupID)
        );

    }

    public function down()
    {
        $this->dropColumn(
            '{{%content_block}}',
            'group_id'
        );

        $this->dropTable('{{%content_block_group%}}');
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
