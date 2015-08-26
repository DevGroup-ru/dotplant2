<?php

use yii\db\Schema;
use yii\db\Migration;

class m150722_072209_alt_for_images extends Migration
{
    public function up()
    {
        $this->addColumn('{{image}}', 'image_alt', $this->text());
        $this->renameColumn('{{image}}', 'image_description', 'image_title');
    }

    public function down()
    {
        $this->dropColumn('{{image}}', 'image_alt');
        $this->renameColumn('{{image}}', 'image_title', 'image_description');
    }
}
