<?php

use yii\db\Schema;
use yii\db\Migration;

class m150514_130440_filter_sets extends Migration
{
    public function up()
    {
        $this->createTable('{{%filter_sets}}',[
            'id' => Schema::TYPE_PK,
            'category_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'sort_order' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'property_id' =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'is_filter_by_price' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 0',
            'delegate_to_children' => Schema::TYPE_BOOLEAN . ' NOT NULL DEFAULT 1',
        ]);

//        /** @var Elasticsearch\Client $client */
//        $client = Yii::$app->elasticSearch->client();
//        $client->indices()->create([
//            'index' => 'product_index',
//            'body' => [
//                'settings' => [
//                    'number_of_shards' => 10,
//                ],
//                'mappings' => [
//                    'product' => [
//                        '_source' => [
//                            'enabled' => true,
//                            'properties' => [
//                                'product_id' => [
//                                    'type' => 'integer',
//                                    'include_in_all' => true,
//                                ],
//                                'price' => [
//                                    // price is in main currency!
//                                    'type' => 'float',
//                                    'include_in_all' => true,
//                                    'null_value' => 0,
//                                ],
//                                'parent_id' => [
//                                    'type' => 'integer',
//                                ],
//                                'categories_ids' => [
//                                    'type' => 'integer',
//                                    'include_in_all' => true,
//                                ],
//                                'main_category_name' => [
//                                    'type' => 'string',
//                                    'boost' => 2,
//                                ],
//                                'name' => [
//                                    'type' => 'string',
//                                    'similarity' => 'BM25',
//                                    'boost' => 2,
//                                ],
//                                'announce' => [
//                                    'type' => 'string',
//                                    'similarity' => 'BM25',
//                                    'boost' => 1.2,
//                                ],
//                                'content' => [
//                                    'type' => 'string',
//                                    'similarity' => 'BM25',
//                                    'term_vector' => 'yes',
//                                ],
//                                'meta_description' => [
//                                    'type' => 'string',
//                                    'similarity' => 'BM25',
//                                    'boost' => 1.5,
//                                ],
//                                'title' => [
//                                    'type' => 'string',
//                                    'similarity' => 'BM25',
//                                    'boost' => 2,
//                                ],
//                                'h1' => [
//                                    'type' => 'string',
//                                    'similarity' => 'BM25',
//                                    'boost' => 2,
//                                ],
//                                'breadcrumbs_label' => [
//                                    'type' => 'string',
//                                    'similarity' => 'BM25',
//                                ],
//                                'sku' => [
//                                    'type' => 'string',
//                                ],
//                            ],
//                        ],
//                    ],
//                ],
//            ],
//        ]);
    }

    public function down()
    {
        $this->dropTable('{{%filter_sets}}');

//        /** @var Elasticsearch\Client $client */
//        $client = Yii::$app->elasticSearch->client();
//        $client->indices()->delete(['index'=>'product_index']);
    }
    
    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }
    
    public function safeDown()
    {
    }
    */
}
