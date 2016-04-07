<?php

use yii\db\Migration;
use app\models\Slide;

class m160407_120244_add_text_to_slides extends Migration
{
    public function up()
    {
        $this->addColumn(
            \app\models\Slide::tableName(),
            "text",
            $this->string()
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
