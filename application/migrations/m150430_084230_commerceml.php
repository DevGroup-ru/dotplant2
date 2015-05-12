<?php

use yii\db\Schema;
use yii\db\Migration;

class m150430_084230_commerceml extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%commerceml_guid}}',
            [
                'id' => Schema::TYPE_PK,
                'guid' => Schema::TYPE_STRING,
                'name' => Schema::TYPE_TEXT,
                'model_id' => Schema::TYPE_BIGINT,
                'type' => 'ENUM(\'PRODUCT\', \'CATEGORY\', \'PROPERTY\') DEFAULT \'PRODUCT\'',
            ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%commerceml_guid}}');

        return true;
    }
}
?>