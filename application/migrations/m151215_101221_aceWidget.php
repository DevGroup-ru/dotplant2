<?php

use yii\db\Migration;
use yii\helpers\Json;

class m151215_101221_aceWidget extends Migration
{
    public function up()
    {

        $this->insert(
            '{{%wysiwyg%}}',
            [
                'name' => 'Ace',
                'class_name' => 'devgroup\ace\Ace',
                'params' => Json::encode([
                    'mode' => 'html',
                    'theme' => 'chrome',
                    'jsOptions' => [
                        'wrap' => true,
                    ],
                    'htmlOptions' => [
                        'width' => '100%',
                        'height' => '200px'
                    ]
                ]),
                'configuration_model' => null,
                'configuration_view' => null
            ]
        );
    }

    public function down()
    {
       $this->delete(
           '{{%wysiwyg%}}',
           [
               'name' => 'Ace',
           ]
       );
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
