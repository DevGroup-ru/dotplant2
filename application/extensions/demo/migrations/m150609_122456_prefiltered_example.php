<?php

use yii\db\Schema;
use yii\db\Migration;

class m150609_122456_prefiltered_example extends Migration
{
    public function up()
    {
        $this->insert('{{property_group}}', [
            'object_id' => 3,
            'name' => Yii::t('app', 'Special actions'),
            'is_internal' => 1,
            'hidden_group_title' => 1,
        ]);

        $propertyGroupId = $this->db->lastInsertID;

        $this->addColumn('{{%product_property}}', 'todays_deals', Schema::TYPE_INTEGER);
        $this->addColumn('{{%product_property}}', 'sale', Schema::TYPE_INTEGER);

        $this->insert('{{%property}}', [
            'property_group_id' => $propertyGroupId,
            'name' => Yii::t('app', 'Show in Today\'s deals'),
            'key' => 'todays_deals',
            'value_type' => 'NUMBER',
            'property_handler_id' => 3,
            'is_column_type_stored' => 1,
            'handler_additional_params' => '{}',
        ]);
        $todaysDealsPropertyId = $this->db->lastInsertID;

        $this->insert('{{%property}}', [
            'property_group_id' => $propertyGroupId,
            'name' => Yii::t('app', 'Show in Sale'),
            'key' => 'sale',
            'value_type' => 'NUMBER',
            'property_handler_id' => 3,
            'is_column_type_stored' => 1,
            'handler_additional_params' => '{}',
        ]);
        $salePropertyId = $this->db->lastInsertID;

        $q = new \yii\db\Query();
        $ids = $q
            ->select('id')
            ->from('{{%product}}')
            ->where(['like', 'name', 'Lenovo'])
            ->column();

        srand();

        foreach ($ids as $id) {
            $this->insert('{{%object_property_group}}', [
                'object_id' => 3,
                'object_model_id' => $id,
                'property_group_id' => $propertyGroupId,
            ]);
            $this->insert('{{%product_property}}',[
                'object_model_id' => $id,
                'todays_deals' => rand(0,1),
                'sale' => rand(0,1)
            ]);
        }

        $this->insert('{{%filter_sets}}',[
            'category_id' => 3,
            'sort_order' => 4,
            'property_id' => $salePropertyId,
        ]);

        $this->insert('{{%filter_sets}}',[
            'category_id' => 3,
            'sort_order' => 3,
            'property_id' => $todaysDealsPropertyId,
        ]);

        $this->insert('{{%prefiltered_pages}}', [
            'slug' => 'sale',
            'active' => '1',
            'last_category_id' => '3',
            'params' => '{"'.$salePropertyId.'":"1"}',
            'title' => 'Total sale in our awesome shop!',
            'announce' => NULL,
            'content' => '<p>This is an example content for our prefiltered page</p>',
            'h1' => 'Total sale - up to 20%',
            'meta_description' => 'Harry up! Total sale in our awesome shop - up to 20%!',
            'breadcrumbs_label' => 'Total sale',
            'view_id' => '0'
        ]);

        $this->insert('{{%prefiltered_pages}}', [
            'slug' => 'todays-deals',
            'active' => '1',
            'last_category_id' => '3',
            'params' => '{"'.$todaysDealsPropertyId.'":"1"}',
            'title' => 'Today\'s deals!',
            'announce' => NULL,
            'content' => '<p>Here\'s some hand-picked products for the Deal of the day!</p>',
            'h1' => 'Deal of the day - up to 30%!',
            'meta_description' => 'Deal of the day in our shop!',
            'breadcrumbs_label' => 'Deal of the day',
            'view_id' => '0'
        ]);

        $this->insert('{{navigation}}',[
            'parent_id' => 1,
            'name' => Yii::t('app', 'Today\'s deals'),
            'url' => '/todays-deals',
            'sort_order' => -1,
        ]);
        $this->insert('{{navigation}}',[
            'parent_id' => 1,
            'name' => Yii::t('app', 'Sale'),
            'url' => '/sale',
            'sort_order' => 3,
        ]);

        $this->update('{{%page}}', [
            'slug_compiled' => ':mainpage:',
            'content' => 'You can edit content of main page in backend/page section',
        ], ['id'=>1]);

        $this->insert('{{%view}}', [
            'name' => 'Main page',
            'view' => '@app/extensions/demo/views/main-page.php',
        ]);
        $this->insert('{{%view_object}}', [
            'view_id' => $this->db->lastInsertID,
            'object_id' => 1,
            'object_model_id' => 1,
        ]);
    }

    public function down()
    {
        echo "m150609_122456_prefiltered_example cannot be reverted.\n";

        return false;
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
