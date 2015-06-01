<?php

use yii\db\Schema;
use yii\db\Migration;

class m150408_083957_rateItem_add_guest_column extends Migration
{
    public function up()
    {
        $this->addColumn('{{%rating_item}}', 'allow_guest', Schema::TYPE_BOOLEAN . ' DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('{{%rating_item}}', 'allow_guest');
    }

}
