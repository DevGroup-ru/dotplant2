<?php

use yii\db\Schema;
use yii\db\Migration;

class m150722_122030_reviews_tree extends Migration
{
    public function up()
    {
        $this->addColumn(
            \app\modules\review\models\Review::tableName(),
            'parent_id',
            $this->integer()->defaultValue(0)
        );

        $this->addColumn(
            \app\modules\review\models\Review::tableName(),
            'root_id',
            $this->integer()->defaultValue(0)
        );

    }

    public function down()
    {
        echo "m150722_122030_reviews_tree cannot be reverted.\n";

        return false;
    }
}
