<?php

use yii\db\Schema;
use yii\db\Migration;

class m150202_084157_page_subdomains extends Migration
{
    public function up()
    {
        $this->addColumn('{{%page}}', 'subdomain', 'string');
    }

    public function down()
    {
        $this->dropColumn('{{%page}}', 'subdomain');
    }
}
