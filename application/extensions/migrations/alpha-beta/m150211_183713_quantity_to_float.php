<?php

use app\models\Config;
use yii\db\Schema;
use yii\db\Migration;

class m150211_183713_quantity_to_float extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%order}}', 'items_count', Schema::TYPE_FLOAT . ' UNSIGNED');
        $this->alterColumn('{{%order_item}}', 'quantity', Schema::TYPE_FLOAT . ' UNSIGNED');
        $Shop = Config::findOne(['path' => 'shop']);
        if (!is_null($Shop)) {
            $config = new Config;
            $config->attributes = [
                'parent_id' => $Shop->id,
                'name' => 'Cart counts unique products',
                'key' => 'cartCountsUniqueProducts',
                'value' => '0',
                'preload' => 1,
            ];

            return $config->save();


        }
        return false;
    }

    public function down()
    {
        $this->alterColumn('{{%order}}', 'items_count', Schema::TYPE_INTEGER . ' UNSIGNED');
        $this->alterColumn('{{%order_item}}', 'quantity', Schema::TYPE_INTEGER . ' UNSIGNED');
        Config::deleteAll(['path' => 'shop.cartCountsUniqueProducts']);
    }
}
