<?php

use yii\db\Schema;
use yii\db\Migration;

class m150720_100330_wysiwyg_conf_view extends Migration
{
    public function up()
    {
        $this->addColumn('{{%wysiwyg}}', 'configuration_view', $this->string());
        $this->update('{{%wysiwyg}}', ['configuration_view' => '@app/modules/core/wysiwyg/imperavi-config.php'], ['name'=>'Imperavi']);
    }

    public function down()
    {
        $this->dropColumn('{{%wysiwyg}}', 'configuration_view');
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
