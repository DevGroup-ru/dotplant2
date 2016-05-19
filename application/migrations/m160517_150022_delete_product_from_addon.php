<?php

use yii\db\Migration;
use app\modules\shop\models\Addon;

class m160517_150022_delete_product_from_addon extends Migration
{
    public function up()
    {
        $this->dropColumn(Addon::tableName(), 'is_product_id');
    }

    public function down()
    {
        echo "m160517_150022_delete_product_from_addon cannot be reverted.\n";

        return false;
    }
}
