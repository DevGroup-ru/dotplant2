<?php

use yii\db\Migration;

class m151217_065513_formSubjectTemplate extends Migration
{
    public function up()
    {
        $this->addColumn(
            '{{%form%}}',
            'subject_template',
            $this->string()->notNull()->defaultValue('{form_name} #{id}')
        );

    }

    public function down()
    {
        $this->dropColumn(
            '{{%form%}}',
            'subject_template'
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
