<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\Order;
use app\models\Config;

class m150415_095657_delete_order_field extends Migration
{
    public function up()
    {
        $this->addColumn(Order::tableName(), 'is_deleted', 'TINYINT UNSIGNED DEFAULT \'0\'');


        $parent = Config::find()
            ->where(
                [
                    'key' => 'shop'
                ]
            )
            ->one();

        $this->insert(
            '{{%config}}',
            [
               'parent_id' => $parent->id,
                'name' => Yii::t('app', 'Ability to delete orders'),
                'key' => 'AbilityDeleteOrders',
                'value' => 0,
                'path' => 'shop.AbilityDeleteOrders'
            ]
        );

    }

    public function down()
    {
        $this->dropColumn(Order::tableName(), 'is_deleted');
        $this->delete(
            '{{%config}}',
            [
                'key' => 'AbilityDeleteOrders',
            ]
        );

    }
}
