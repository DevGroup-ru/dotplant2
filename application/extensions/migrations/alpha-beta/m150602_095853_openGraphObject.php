<?php

use yii\db\Schema;
use yii\db\Migration;

class m150602_095853_openGraphObject extends Migration
{
    public function up()
    {
        $this->createTable(
            '{{%open_graph_object}}',
            [
                'id' => Schema::TYPE_PK,
                'object_id' => Schema::TYPE_INTEGER . ' NOT NULL',
                'active' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
                'relation_data' => Schema::TYPE_TEXT . ' NOT NULL'
            ]
        );


        $data = [
            'title' =>
                [
                    'type' => 'field',
                    'key' => 'title',
                ],
            'image' =>
                [
                    'type' => 'relation',
                    'relationName' => 'getImages',
                    'key' => 'file',
                    'class' => 'app\\modules\\image\\models\\Image',
                ],
            'description' =>
                [

                    'type' => 'field',
                    'key' => 'announce',
                ],
        ];


        $this->batchInsert(
            '{{%open_graph_object}}',
            [
                'object_id',
                'active',
                'relation_data',
            ],
            [
                [
                    \app\models\Object::getForClass(\app\modules\page\models\Page::className())->id,
                    1,
                    json_encode($data)
                ],
                [
                    \app\models\Object::getForClass(\app\modules\shop\models\Category::className())->id,
                    1,
                    json_encode($data)
                ],
                [
                    \app\models\Object::getForClass(\app\modules\shop\models\Product::className())->id,
                    1,
                    json_encode($data)
                ],

            ]
        );

    }

    public function down()
    {
        $this->dropTable('{{%open_graph_object}}');
    }

}
