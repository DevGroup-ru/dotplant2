<?php

use yii\db\Schema;
use yii\db\Migration;
use app\models\Config;
use app\backend\models\BackendMenu;

class m150202_142336_yml_config extends Migration
{
    public function up()
    {
        $this->insert(Config::tableName(),
            [
                'parent_id' => 0,
                'name' => 'YML',
                'key' => 'yml',
                'path' => 'yml'
            ]
        );

        $parentId = $this->db->lastInsertID;

        $this->batchInsert(Config::tableName(),
            [
                'parent_id',
                'name',
                'key',
                'value',
                'path'
            ],
            [
                [
                    $parentId,
                    'Главная валюта',
                    'main_currency',
                    'RUR',
                    'yml.main_currency'
                ],
                [
                    $parentId,
                    'Показывать все свойства в YML',
                    'show_all_properties',
                    1,
                    'yml.show_all_properties'
                ],
                [
                    $parentId,
                    'Тип описания по умолчанию',
                    'default_offer_type',
                    'simplified',
                    'yml.default_offer_type'
                ],
                [
                    $parentId,
                    'Общая стоимость доставки для региона',
                    'local_delivery_cost',
                    '',
                    'yml.local_delivery_cost'
                ]
            ]
        );
    }

    public function down()
    {
        $this->delete(Config::tableName(), [ 'path' => "yml" ]);
        $this->delete(Config::tableName(), [ 'path' => "yml.main_currency" ]);
        $this->delete(Config::tableName(), [ 'path' => "yml.show_all_properties" ]);
        $this->delete(Config::tableName(), [ 'path' => "yml.default_offer_type" ]);
        $this->delete(Config::tableName(), [ 'path' => "yml.local_delivery_cost" ]);

        return true;
    }
}
