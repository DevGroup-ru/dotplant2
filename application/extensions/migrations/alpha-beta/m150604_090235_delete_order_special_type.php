<?php

use yii\db\Schema;
use yii\db\Migration;

class m150604_090235_delete_order_special_type extends Migration
{
    public function up()
    {

        $this->dropColumn(
            '{{%special_price_list}}',
            'type_id'
        );


        $this->addColumn(
            '{{%special_price_list}}',
            'type',
            "ENUM('core', 'discount', 'delivery', 'tax' ,'project') DEFAULT 'project'"
        );

        $this->update(
            '{{%special_price_list}}',
            [
                'type' => 'core'
            ],
            [
                'handler' => ['getCurrencyPriceProduct']
            ]
        );

        $this->update(
            '{{%special_price_list}}',
            [
                'type' => 'discount'
            ],
            [
                'handler' => ['getDiscountPriceProduct', 'getDiscountPriceOrder']
            ]
        );

        $this->update(
            '{{%special_price_list}}',
            [
                'type' => 'delivery'
            ],
            [
                'handler' => ['getDeliveryPriceOrder']
            ]
        );

        $this->dropTable('{{%special_price_list_type}}');


    }

    public function down()
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
    }
    

}
