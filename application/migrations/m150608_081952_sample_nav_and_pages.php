<?php

use yii\db\Schema;
use yii\db\Migration;

class m150608_081952_sample_nav_and_pages extends Migration
{
    public function up()
    {
        $aboutTitle = Yii::t('app', 'About us');
        $this->insert(
            '{{%page}}',
            [
                'parent_id' => 1,
                'slug' => 'about',
                'slug_compiled' => 'about',
                'content' => 'This is a sample about page',
                'name' => $aboutTitle,
                'title' => $aboutTitle,
                'h1' => $aboutTitle,
                'meta_description' => 'Example META for ' . $aboutTitle,
                'breadcrumbs_label' => $aboutTitle,
            ]
        );
        $aboutPageId = $this->db->lastInsertID;

        $deliveryTitle = Yii::t('app', 'Delivery');
        $this->insert(
            '{{%page}}',
            [
                'parent_id' => 1,
                'slug' => 'delivery',
                'slug_compiled' => 'delivery',
                'content' => 'Place your delivery information here',
                'name' => $deliveryTitle,
                'title' => $deliveryTitle,
                'h1' => $deliveryTitle,
                'meta_description' => 'Example META for ' . $deliveryTitle,
                'breadcrumbs_label' => $deliveryTitle,
            ]
        );
        $deliveryPageId = $this->db->lastInsertID;

        $paymentTitle = Yii::t('app', 'Payment');
        $this->insert(
            '{{%page}}',
            [
                'parent_id' => 1,
                'slug' => 'payment',
                'slug_compiled' => 'payment',
                'content' => 'Place your PAYMENT information here',
                'name' => $paymentTitle,
                'title' => $paymentTitle,
                'h1' => $paymentTitle,
                'meta_description' => 'Example META for ' . $paymentTitle,
                'breadcrumbs_label' => $paymentTitle,
            ]
        );
        $paymentPageId = $this->db->lastInsertID;
        $this->insert(
            '{{%navigation}}',
            [
                'parent_id' => 1,
                'name' => Yii::t('app', 'Catalog'),
                'url' => '/catalog',
            ]
        );
        $this->insert(
            '{{%navigation}}',
            [
                'parent_id' => 1,
                'name' => $aboutTitle,
                'route' => '/page/page/show',
                'route_params' => \yii\helpers\Json::encode(['id'=>$aboutPageId]),
            ]
        );
        $this->insert(
            '{{%navigation}}',
            [
                'parent_id' => 1,
                'name' => $deliveryTitle,
                'route' => '/page/page/show',
                'route_params' => \yii\helpers\Json::encode(['id'=>$deliveryPageId]),
            ]
        );
        $this->insert(
            '{{%navigation}}',
            [
                'parent_id' => 1,
                'name' => $paymentTitle,
                'route' => '/page/page/show',
                'route_params' => \yii\helpers\Json::encode(['id'=>$paymentPageId]),
            ]
        );
    }

    public function down()
    {
        echo "m150608_081952_sample_nav_and_pages cannot be reverted.\n";

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
