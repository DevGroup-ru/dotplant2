<?php

use yii\db\Schema;
use yii\db\Migration;
use \app\models\Object;
use \app\modules\shop\models\Order;
use \app\modules\shop\models\Product;

class m150519_120719_spetial_price_order extends Migration
{
    public function up()
    {
        $this->createTable(
            '{{%special_price_list_type}}',
            [
                'id' => Schema::TYPE_PK,
                'key' => Schema::TYPE_STRING . ' NOT NULL',
                'description' => Schema::TYPE_STRING . ' DEFAULT NULL'
            ]
        );

        $this->batchInsert(
            '{{%special_price_list_type}}',
            [
                'key',
                'description'
            ],
            [
                ['core', 'Core'],
                ['discount', 'Discount'],
                ['delivery', 'Delivery'],
                ['tax', 'Tax'],
                ['project', 'Project'],
            ]
        );


        $special_price_list = (new \yii\db\Query())
            ->from('{{%special_price_list}}')
            ->all();

        $this->dropColumn(
            '{{%special_price_list}}',
            'type'
        );

        $this->addColumn(
            '{{%special_price_list}}',
            'type_id',
            Schema::TYPE_SMALLINT
        );

        foreach ($special_price_list as $list) {
            $this->update(
                '{{%special_price_list}}',
                [
                    'type_id' => (new \yii\db\Query())
                        ->select('id')
                        ->from('{{%special_price_list_type}}')
                        ->where(['key' => $list['type']])
                        ->scalar()
                ],
                [
                    'id' => $list['id']
                ]
            );
        }


        $this->insert(
            '{{%special_price_list}}',
            [
                'object_id' => Object::getForClass(Order::className())->id,
                'class' => 'app\modules\shop\models\Discount',
                'active' => 1,
                'sort_order' => 15,
                'type_id' => (new \yii\db\Query())
                    ->select('id')
                    ->from('{{%special_price_list_type}}')
                    ->where(['key' => 'discount'])
                    ->scalar()
            ]
        );

        $this->createTable(
            '{{%special_price_object}}',
            [
                'id' => Schema::TYPE_PK,
                'special_price_list_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'object_model_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'price' => Schema::TYPE_FLOAT . ' NOT NULL DEFAULT 0'
            ]
        );




    }

    public function down()
    {

        $this->dropTable('{{%special_price_list_type}}');
        $this->dropTable('{{%special_price_object}}');

        $this->dropColumn(
            '{{%special_price_list}}',
            'type_id'
        );

        $this->addColumn(
            '{{%special_price_list}}',
            'type',
            "ENUM('core', 'discount', 'delivery', 'project') DEFAULT 'project'"
        );

        $this->update(
            '{{%special_price_list}}',
            [
                'type' => 'core',
            ],
            [
                'class' => 'app\modules\shop\models\Currency',
                'object_id' => Object::getForClass(Product::className())->id
            ]
        );

        $this->update(
            '{{%special_price_list}}',
            [
                'type' => 'discount'
            ],
            [
                'class' => 'app\modules\shop\models\Discount',
                'object_id' => Object::getForClass(Product::className())->id
            ]
        );


        $this->delete(
            '{{%special_price_list}}',
            [
                'class' => 'app\modules\shop\models\Discount',
                'object_id' => Object::getForClass(Order::className())->id
            ]
        );
    }

}
