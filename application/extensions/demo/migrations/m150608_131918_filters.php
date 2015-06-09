<?php

use yii\db\Schema;
use yii\db\Migration;

class m150608_131918_filters extends Migration
{
    public function up()
    {
        $this->insert(
            '{{%theme_active_widgets}}',
            [
                'part_id' => 5,
                'widget_id' => 5,
                'variation_id' => 2,
            ]
        );
        $propertyId1 = \app\models\Property::find()
            ->where(['key'=>'tip_pamyati'])
            ->select('id')
            ->scalar();
        $propertyId2 = \app\models\Property::find()
            ->where(['key'=>'tip_ekrana'])
            ->select('id')
            ->scalar();
        $this->insert(
            '{{%filter_sets}}',
            [
                'category_id'=>3,
                'sort_order' => 2,
                'property_id' => $propertyId2,
            ]
        );

        $this->insert(
            '{{%filter_sets}}',
            [
                'category_id'=>3,
                'sort_order' => 1,
                'property_id' => $propertyId1,
            ]
        );
    }

    public function down()
    {
        echo "m150608_131918_filters cannot be reverted.\n";

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
