<?php

use yii\db\Schema;
use yii\db\Migration;

class m150513_122524_special_price extends Migration
{
    public function up()
    {
        $this->createTable(
            '{{%special_price_list%}}',
            [
                'id' => Schema::TYPE_PK,
                'object_id' => Schema::TYPE_SMALLINT .' NOT NULL',
                'class' => Schema::TYPE_STRING . ' NOT NULL',
                'active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'type' =>  "ENUM('core', 'discount', 'delivery', 'project') NOT NULL",
                'sort_order' => Schema::TYPE_INTEGER .' NOT NULL DEFAULT 0',
                'params' => Schema::TYPE_TEXT
            ]
        );

        $this->batchInsert(
            '{{%special_price_list%}}',
            [
                'object_id',
                'class',
                'type',
                'sort_order'
            ],
            [
                [
                    \app\models\Object::getForClass(\app\modules\shop\models\Product::className())->id,
                    \app\modules\shop\models\Currency::className(),
                    'core',
                    5
                ],
                [
                    \app\models\Object::getForClass(\app\modules\shop\models\Product::className())->id,
                    \app\modules\shop\models\Discount::className(),
                    'discount',
                    10
                ]
            ]
        );

    }

    public function down()
    {

        $this->dropTable('{{%special_price_list%}}');

    }
}
