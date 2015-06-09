<?php

use yii\db\Schema;
use yii\db\Migration;

class m150428_082941_reviews_images extends Migration
{
    public function up()
    {

        $this->addColumn(
            \app\reviews\models\Review::tableName(),
            'image_path',
            'string'
        );


    }

    public function down()
    {
        $this->dropColumn(
            \app\reviews\models\Review::tableName(),
            'image_path'
        );
    }
    

}
