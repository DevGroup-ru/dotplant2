<?php

use yii\db\Schema;
use yii\db\Migration;

class m150727_093721_reviews_root extends Migration
{
    public function up()
    {
        foreach (\app\modules\review\models\Review::findAll(['root_id' => 0]) as $model) {
            $model->root_id = $model->id;
            $model->save();
        }
    }

    public function down()
    {
        echo "m150727_093721_reviews_root cannot be reverted.\n";

        return false;
    }
}
