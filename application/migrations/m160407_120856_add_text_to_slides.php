<?php

use yii\db\Migration;
use app\models\Slide;

class m160407_120856_add_text_to_slides extends Migration
{
    public function up()
    {
        $this->addColumn(
            Slide::tableName(),
            "text",
            $this->text()
        );
    }

    public function down()
    {
        $this->dropColumn(
            Slide::tableName(),
            "text"
        );
    }
}
