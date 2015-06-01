<?php

use yii\db\Schema;
use yii\db\Migration;

class m150228_135050_indexes extends Migration
{
    public function up()
    {

        $this->createIndex(
            'cat_omid',
            '{{%product_category}}',
            [
                'category_id',
                'object_model_id',
            ]
        );
        $this->createIndex(
            'parent_active',
            '{{%product}}',
            [
                'parent_id',
                'active',
            ]
        );
        $this->createIndex(
            'object_route',
            '{{%dynamic_content}}',
            [
                'object_id',
                'route(80)',
            ]
        );
    }

    public function down()
    {

        $this->dropIndex('cat_omid', '{{%product_category}}');
        $this->dropIndex('parent_active', '{{%product}}');
        $this->dropIndex('object_route', '{{%dynamic_content}}');
    }
}
