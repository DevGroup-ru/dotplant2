<?php

use yii\db\Schema;
use yii\db\Migration;

class m150216_121527_deleting_append_fields_in_DC extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%dynamic_content}}', 'append_content');
        $this->dropColumn('{{%dynamic_content}}', 'append_title');
        $this->dropColumn('{{%dynamic_content}}', 'append_h1');
        $this->dropColumn('{{%dynamic_content}}', 'append_meta_description');
    }

    public function down()
    {
        $this->addColumn('{{%dynamic_content}}', 'append_content', 'boolean');
        $this->addColumn('{{%dynamic_content}}', 'append_title', 'boolean');
        $this->addColumn('{{%dynamic_content}}', 'append_h1', 'boolean');
        $this->addColumn('{{%dynamic_content}}', 'append_meta_description', 'boolean');

        return true;
    }
}
